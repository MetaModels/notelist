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

namespace MetaModels\NoteList\EventListener\DcGeneral;

use Contao\Database;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use MetaModels\IFactory;
use MetaModels\NoteList\NoteListFactory;

/**
 * This class provides the list of registered note lists for the backend.
 */
class NoteListListListener
{
    use FilterIdToMetaModelTrait;

    /**
     * The MetaModels factory.
     *
     * @var IFactory|NoteListFactory
     */
    private $factory;

    /**
     * The note list factory.
     *
     * @var NoteListFactory
     */
    private $listFactory;

    /**
     * The database.
     *
     * @var Database
     */
    private $database;

    /**
     * Create a new instance.
     *
     * @param IFactory        $factory         The MetaModels factory.
     * @param NoteListFactory $noteListFactory The note list factory.
     * @param Database        $database        The database connection.
     */
    public function __construct(IFactory $factory, NoteListFactory $noteListFactory, Database $database)
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

        if (('notelist' !== $event->getPropertyName())
        || ('tl_metamodel_filtersetting' !== $event->getEnvironment()->getDataDefinition()->getName())) {
            return;
        }
        $metaModel = $this->getMetaModel(
            $event->getModel()->getProperty('fid'),
            $this->factory,
            $this->database
        );
        if (null === $metaModel) {
            return;
        }

        $event->setOptions($this->listFactory->getConfiguredListsFor($metaModel));
    }
}
