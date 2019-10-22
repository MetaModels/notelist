<?php

/**
 * This file is part of MetaModels/notelist.
 *
 * (c) 2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2017 The MetaModels team.
 * @license    https://github.com/MetaModels/notelist/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

declare(strict_types = 1);

namespace MetaModels\NoteListBundle\EventListener\DcGeneral;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use Doctrine\DBAL\Connection;

/**
 * This class provides the list of filter settings for the backend.
 */
class FilterSettingsListListener
{
    /**
     * The database.
     *
     * @var Connection
     */
    private $connection;

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
    public function getOptions(GetPropertyOptionsEvent $event)
    {
        if (null !== $event->getOptions()) {
            return;
        }

        if (('filter' !== $event->getPropertyName())
        || ('tl_metamodel_notelist' !== $event->getEnvironment()->getDataDefinition()->getName())) {
            return;
        }

        $adapters = $this->connection
            ->createQueryBuilder()
            ->select('id', 'name')
            ->from('tl_metamodel_filter')
            ->where('pid=:pid')
            ->setParameter('pid', $event->getModel()->getProperty('pid'))
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);
        $result   = [];
        foreach ($adapters as $adapter) {
            $result[$adapter['id']] = $adapter['name'];
        }

        $event->setOptions($result);
    }
}
