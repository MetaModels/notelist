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

declare(strict_types=1);

$GLOBALS['TL_DCA']['tl_form_field']['palettes']['__selector__'][]     = 'metamodel';
$GLOBALS['TL_DCA']['tl_form_field']['palettes']['metamodel_notelist'] =
    '{type_legend},type,name,label;' .
    '{fconfig_legend},metamodel,metamodel_notelist;' .
    '{expert_legend:hide},class;' .
    '{template_legend:hide},customTpl;' .
    '{submit_legend},addSubmit';

$GLOBALS['TL_DCA']['tl_form_field']['subpalettes']['metamodel'] = 'metamodel_notelist';

$GLOBALS['TL_DCA']['tl_form_field']['fields']['metamodel'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_form_field']['metamodel'],
    'exclude'          => true,
    'inputType'        => 'select',
    'options_callback' => [
        MetaModels\NoteList\Bridge\DcaCallbackBridge::class,
        'getMetaModelOptions'
    ],
    'sql'              => "char(1) NOT NULL default ''",
    'eval'             => [
        'submitOnChange'     => true,
        'includeBlankOption' => true,
    ]
];
/*
$GLOBALS['TL_DCA']['tl_form_field']['fields']['metamodel_notelist'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_form_field']['metamodel_notelist'],
    'exclude'          => true,
    'inputType'        => 'checkbox',
    'options_callback' => [
        MetaModels\NoteList\Bridge\DcaCallbackBridge::class,
        'getNoteListOptions'
    ],
    'sql'              => 'text NULL',
    'eval'             => [
        'multiple' => true
    ]
];
*/

$GLOBALS['TL_DCA']['tl_form_field']['fields']['metamodel_notelist'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_form_field']['metamodel_notelist'],
    'exclude'          => true,
    'inputType' => 'multiColumnWizard',
    'eval'      => [
        'tl_class'     => 'notelist_combine',
        'columnFields' => [
            'notelist' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_form_field']['metamodel_notelist_notelist'],
                'exclude'   => true,
                'inputType' => 'select',
                'options_callback' => [
                    MetaModels\NoteList\Bridge\DcaCallbackBridge::class,
                    'getNoteListOptionsMcw'
                ],
                'eval'      => [
                    'includeBlankOption' => true,
                    'style'              => 'width:115px',
                    'chosen'             => 'true'
                ],
            ],
            'frontend' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_form_field']['metamodel_notelist_rendersetting_frontend'],
                'exclude'   => true,
                'inputType' => 'select',
                'options_callback' => [MetaModels\NoteList\Bridge\DcaCallbackBridge::class, 'getRenderSettingsMcw'],
                'eval'      => [
                    'includeBlankOption' => true,
                    'style'              => 'width:115px',
                    'chosen'             => 'true'
                ],
            ],
            'email'   => [
                'label'     => &$GLOBALS['TL_LANG']['tl_form_field']['metamodel_notelist_rendersetting_email'],
                'exclude'   => true,
                'inputType' => 'select',
                'options_callback' => [MetaModels\NoteList\Bridge\DcaCallbackBridge::class, 'getRenderSettingsMcw'],
                'eval'      => [
                    'includeBlankOption' => true,
                    'style'  => 'width:180px',
                    'chosen' => 'true'
                ],
            ],
        ],
    ],
    'options_callback' => [
        MetaModels\NoteList\Bridge\DcaCallbackBridge::class,
        'getNoteListOptions'
    ],
    'sql'              => 'text NULL',
];
