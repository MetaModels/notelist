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

/**
 * This class provides the list of filter settings for the backend.
 */
class FilterSettingsListListener
{
    /**
     * The database.
     *
     * @var Database
     */
    private $database;

    /**
     * Create a new instance.
     *
     * @param Database $database The database connection.
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
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

        $settings = $this->database
            ->prepare('SELECT id,name FROM tl_metamodel_filter WHERE pid=?')
            ->execute($event->getModel()->getProperty('pid'));

        $adapters = $settings->fetchAllAssoc();
        $result   = [];
        foreach ($adapters as $adapter) {
            $result[$adapter['id']] = $adapter['name'];
        }

        $event->setOptions($result);
    }
}
