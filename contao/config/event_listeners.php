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

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use ContaoCommunityAlliance\DcGeneral\View\Event\RenderReadablePropertyValueEvent;
use MetaModels\Events\ParseItemEvent;
use MetaModels\Events\RenderItemListEvent;
use MetaModels\Filter\Setting\Events\CreateFilterSettingFactoryEvent;
use MetaModels\MetaModelsEvents;
use MetaModels\NoteList\Event\NoteListEvents;
use MetaModels\NoteList\Event\ParseNoteListFormEvent;
use MetaModels\NoteList\EventListener\DcGeneral\BreadCrumbNoteList;
use MetaModels\NoteList\EventListener\DcGeneral\FilterSettingTypeRenderer;
use MetaModels\NoteList\EventListener\ParseItemListener;

return [
    GetPropertyOptionsEvent::NAME => [
        function (GetPropertyOptionsEvent $event) {
            // Forcing lazy initialization here as otherwise we will end up in endless recursion. Change for Contao 4!
            /** @var MetaModels\NoteList\EventListener\DcGeneral\AdapterListListener $handler */
            $handler = $GLOBALS['container']['metamodels-notelist.backend.adapter-option-listener'];
            $handler->getAdapterListOptions($event);
        },
        function (GetPropertyOptionsEvent $event) {
            // Forcing lazy initialization here as otherwise we will end up in endless recursion. Change for Contao 4!
            /** @var MetaModels\NoteList\EventListener\DcGeneral\NoteListListListener $handler */
            $handler = $GLOBALS['container']['metamodels-notelist.backend.notelist-list-option-listener'];
            $handler->getOptions($event);
        },
        function (GetPropertyOptionsEvent $event) {
            // Forcing lazy initialization here as otherwise we will end up in endless recursion. Change for Contao 4!
            /** @var MetaModels\NoteList\EventListener\DcGeneral\FilterSettingsListListener $handler */
            $handler = $GLOBALS['container']['metamodels-notelist.backend.filter-settings-option-listener'];
            $handler->getOptions($event);
        }
    ],
    GetBreadcrumbEvent::NAME => [
        function (GetBreadcrumbEvent $event) {
            if ('tl_metamodel_notelist' !== $event->getEnvironment()->getDataDefinition()->getName()) {
                return;
            }
            /** @var BreadCrumbNoteList $subscriber */
            $subscriber = $GLOBALS['container']['metamodels-notelist.backend.breadcrumb-renderer'];
            $subscriber->getBreadcrumb($event);
        }
    ],
    ModelToLabelEvent::NAME => [
        [
            function (ModelToLabelEvent $event) {
                /** @var FilterSettingTypeRenderer $handler */
                $handler = $GLOBALS['container']['metamodels-notelist.backend.filter-setting-type-renderer'];
                $handler->modelToLabel($event);
            },
            // Priority must be lower than the renderer by MetaModels core as that one always override unknown values.
            -1
        ]
    ],
    BuildWidgetEvent::NAME => [
        [
            function (BuildWidgetEvent $event) {
                if (('tl_metamodel_notelist' !== $event->getEnvironment()->getDataDefinition()->getName())
                || ($event->getProperty()->getName() !== 'name')) {
                    return;
                }

                $GLOBALS['container']['metamodels-notelist.backend.prepare-name-widget']
                    ->buildWidget($event);
            },
            // Priority must be higher than dc-general to be able to manipulate the container property.
            200
        ]
    ],
    DecodePropertyValueForWidgetEvent::NAME => [
        function (DecodePropertyValueForWidgetEvent $event) {
            if (('tl_metamodel_notelist' !== $event->getModel()->getProviderName())
                || ($event->getProperty() !== 'name')) {
                return;
            }

            $GLOBALS['container']['metamodels-notelist.backend.prepare-name-widget']
                ->decodeNameValue($event);
        }
    ],
    EncodePropertyValueFromWidgetEvent::NAME => [
        function (EncodePropertyValueFromWidgetEvent $event) {
            if (('tl_metamodel_notelist' !== $event->getModel()->getProviderName())
                || ($event->getProperty() !== 'name')) {
                return;
            }

            $GLOBALS['container']['metamodels-notelist.backend.prepare-name-widget']
                ->encodeNameValue($event);
        }
    ],
    RenderReadablePropertyValueEvent::NAME => [
        function (RenderReadablePropertyValueEvent $event) {
            if (('tl_metamodel_notelist' !== $event->getModel()->getProviderName())
                || ($event->getProperty()->getName() !== 'name')) {
                return;
            }

            $GLOBALS['container']['metamodels-notelist.backend.render-name-widget']
                ->render($event);
        }
    ],
    MetaModelsEvents::FILTER_SETTING_FACTORY_CREATE => [
        function (CreateFilterSettingFactoryEvent $event) {
            $event->getFactory()->addTypeFactory($GLOBALS['container']['metamodels-notelist.filter-setting-factory']);
        }
    ],
    MetaModelsEvents::PARSE_ITEM => [
        function (ParseItemEvent $event) {
            /** @var ParseItemListener $handler */
            $handler = $GLOBALS['container']['metamodels-notelist.parse-item-listener'];

            $handler->addNoteListActions($event);
        }
    ],
    MetaModelsEvents::RENDER_ITEM_LIST => [
        function (RenderItemListEvent $event) {
            /** @var ParseItemListener $handler */
            $handler = $GLOBALS['container']['metamodels-notelist.parse-item-listener'];

            $handler->handleListRendering($event);
        }
    ],
    NoteListEvents::PARSE_NOTE_LIST_FORM => [
        function (ParseNoteListFormEvent $event) {
            /** @var ParseItemListener $handler */
            $handler = $GLOBALS['container']['metamodels-notelist.parse-item-listener'];

            $handler->handleFormRendering($event);
        }
    ]
];
