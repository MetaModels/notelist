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
use MetaModels\IFactory;
use MetaModels\NoteListBundle\NoteListFactory;

/**
 * This class provides the list of registered note lists for the backend.
 */
class NoteListListListener
{
    use FilterIdToMetaModelTrait;

    /**
     * The MetaModels factory.
     *
     * @var IFactory
     */
    private IFactory $factory;

    /**
     * The note list factory.
     *
     * @var NoteListFactory
     */
    private NoteListFactory $listFactory;

    /**
     * The database.
     *
     * @var Connection
     */
    private Connection $database;

    /**
     * Create a new instance.
     *
     * @param IFactory        $factory         The MetaModels factory.
     * @param NoteListFactory $noteListFactory The note list factory.
     * @param Connection      $database        The database connection.
     */
    public function __construct(IFactory $factory, NoteListFactory $noteListFactory, Connection $database)
    {
        $this->listFactory = $noteListFactory;
        $this->factory     = $factory;
        $this->database    = $database;
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

        if (
            ('notelist' !== $event->getPropertyName())
            || !(($dataDefinition = $event->getEnvironment()->getDataDefinition()) instanceof ContainerInterface)
            || ('tl_metamodel_filtersetting' !== $dataDefinition->getName())
        ) {
            return;
        }

        $metaModel = $this->getMetaModel(
            (string) $event->getModel()->getProperty('fid'),
            $this->factory,
            $this->database
        );

        if (null === $metaModel) {
            return;
        }

        $event->setOptions($this->listFactory->getConfiguredListsFor($metaModel));
    }
}
