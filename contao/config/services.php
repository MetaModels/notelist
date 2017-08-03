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

use MetaModels\NoteList\EventListeners\DcGeneral\BreadCrumbNoteList;
use MetaModels\NoteList\EventListeners\DcGeneral\BuildNoteListNameWidgetListener;
use MetaModels\NoteList\EventListeners\DcGeneral\FilterSettingTypeRenderer;
use MetaModels\NoteList\EventListeners\DcGeneral\NoteListListListener;
use MetaModels\NoteList\EventListeners\DcGeneral\RenderNoteListNameAsReadablePropertyValueListener;
use MetaModels\NoteList\Filter\NoteListFilterSettingTypeFactory;
use MetaModels\NoteList\EventListeners\DcGeneral\AdapterListListener;
use MetaModels\NoteList\EventListeners\ParseItemListener;
use MetaModels\NoteList\NoteListFactory;
use MetaModels\NoteList\Storage\StorageAdapterFactory;

/** @var Pimple $container */

$container['metamodels-notelist.storage-factory'] = $container->share(
    function () {
        return new StorageAdapterFactory();
    }
);

$container['metamodels-notelist.factory'] = $container->share(
    function ($container) {
        return new NoteListFactory(
            $container['database.connection'],
            $container['metamodels-notelist.storage-factory']
        );
    }
);

$container['metamodels-notelist.filter-setting-factory'] = $container->share(
    function ($container) {
        return new NoteListFilterSettingTypeFactory($container['metamodels-notelist.factory']);
    }
);

$container['metamodels-notelist.parse-item-listener'] = $container->share(
    function ($container) {
        return new ParseItemListener($container['metamodels-notelist.factory'], $container['event-dispatcher']);
    }
);

/*
 * Backend from here on.
 */

$container['metamodels-notelist.backend.adapter-option-listener'] = $container->share(
    function ($container) {
        return new AdapterListListener(
            $container['metamodels-notelist.storage-factory'],
            $container['translator']
        );
    }
);

$container['metamodels-notelist.backend.notelist-list-option-listener'] = $container->share(
    function ($container) {
        return new NoteListListListener(
            $container['metamodels-factory.factory'],
            $container['metamodels-notelist.factory'],
            $container['database.connection']
        );
    }
);

$container['metamodels-notelist.backend.breadcrumb-renderer'] = $container->share(
    function ($container) {
        $serviceContainer = $container['metamodels-service-container'];

        return new BreadCrumbNoteList($serviceContainer);
    }
);

$container['metamodels-notelist.backend.prepare-name-widget'] = $container->share(
    function ($container) {
        return new BuildNoteListNameWidgetListener(
            $container['metamodels-factory.factory'],
            $container['translator']
        );
    }
);

$container['metamodels-notelist.backend.render-name-widget'] = $container->share(
    function ($container) {
        return new RenderNoteListNameAsReadablePropertyValueListener(
            $container['metamodels-factory.factory'],
            $container['translator']
        );
    }
);

$container['metamodels-notelist.backend.filter-setting-type-renderer'] = $container->share(
    function ($container) {
        return new FilterSettingTypeRenderer(
            $container['translator'],
            $container['event-dispatcher'],
            $container['metamodels-notelist.factory'],
            $container['metamodels-factory.factory'],
            $container['database.connection']
        );
    }
);
