<?php

/**
 * This file is part of MetaModels/notelist.
 *
 * (c) 2017-2025 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2017-2025 The MetaModels team.
 * @license    https://github.com/MetaModels/notelist/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\NoteListBundle\EventListener\DcGeneral;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use Doctrine\DBAL\Connection;

/**
 * This class provides the list of forms for the backend.
 */
class FormListListener
{
    /**
     * The database.
     *
     * @var Connection
     */
    private Connection $connection;

    /**
     * Create a new instance.
     *
     * @param Connection $connection The database connection.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Retrieve the list of adapters.
     *
     * @param GetPropertyOptionsEvent $event The event to process.
     *
     * @return void
     */
    public function getOptions(GetPropertyOptionsEvent $event): void
    {
        if (null !== $event->getOptions()) {
            return;
        }

        if (
            ('form' !== $event->getPropertyName())
            || !(($dataDefinition = $event->getEnvironment()->getDataDefinition()) instanceof ContainerInterface)
            || ('tl_metamodel_notelist' !== $dataDefinition->getName())
        ) {
            return;
        }

        // All forms without widget 'metamodel_notelist'.
        $adapters = $this->connection
            ->createQueryBuilder()
            ->select('id', 'title')
            ->from('tl_form')
            ->where('id NOT IN (SELECT pid FROM tl_form_field WHERE type = \'metamodel_notelist\' GROUP BY pid)')
            ->executeQuery()
            ->fetchAllAssociative();

        $result = [];
        foreach ($adapters as $adapter) {
            $result[$adapter['id']] = $adapter['title'];
        }

        $event->setOptions($result);
    }
}
