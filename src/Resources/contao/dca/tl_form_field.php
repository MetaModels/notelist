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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2017 The MetaModels team.
 * @license    https://github.com/MetaModels/notelist/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_form_field']['palettes']['__selector__'][]     = 'metamodel';
$GLOBALS['TL_DCA']['tl_form_field']['palettes']['metamodel_notelist'] =
    '{type_legend},type,name,label;' .
    '{fconfig_legend},metamodel,metamodel_notelist;' .
    '{expert_legend:hide},class;' .
    '{template_legend:hide},customTpl,metamodel_customTplEmail;' .
    '{submit_legend},addSubmit';

$GLOBALS['TL_DCA']['tl_form_field']['subpalettes']['metamodel'] = 'metamodel_notelist';

$GLOBALS['TL_DCA']['tl_form_field']['fields']['metamodel'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_form_field']['metamodel'],
    'exclude'          => true,
    'inputType'        => 'select',
    'options_callback' => [
        MetaModels\NoteListBundle\Bridge\DcaCallbackBridge::class,
        'getMetaModelOptions'
    ],
    'sql'              => "char(1) NOT NULL default ''",
    'eval'             => [
        'submitOnChange'     => true,
        'includeBlankOption' => true,
    ]
];

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
                    MetaModels\NoteListBundle\Bridge\DcaCallbackBridge::class,
                    'getNoteListOptionsMcw'
                ],
                'eval'      => [
                    'includeBlankOption' => true,
                    'style'              => 'width:200px',
                    'chosen'             => 'true'
                ],
            ],
            'frontend' => [
                'label'     => &$GLOBALS['TL_LANG']['tl_form_field']['metamodel_notelist_rendersetting_frontend'],
                'exclude'   => true,
                'inputType' => 'select',
                'options_callback' => [
                    MetaModels\NoteListBundle\Bridge\DcaCallbackBridge::class,
                    'getRenderSettingsMcw'
                ],
                'eval'      => [
                    'includeBlankOption' => true,
                    'style'              => 'width:200px',
                    'chosen'             => 'true'
                ],
            ],
            'email'   => [
                'label'     => &$GLOBALS['TL_LANG']['tl_form_field']['metamodel_notelist_rendersetting_email'],
                'exclude'   => true,
                'inputType' => 'select',
                'options_callback' => [
                    MetaModels\NoteListBundle\Bridge\DcaCallbackBridge::class,
                    'getRenderSettingsMcw'
                ],
                'eval'      => [
                    'includeBlankOption' => true,
                    'style'  => 'width:200px',
                    'chosen' => 'true'
                ],
            ],
            'clearlist'   => [
                'label'     => &$GLOBALS['TL_LANG']['tl_form_field']['metamodel_notelist_rendersetting_clearlist'],
                'exclude'   => true,
                'default'   => 'true',
                'inputType' => 'checkbox',
                'eval'      => [
                    'style'  => 'width:50px'
                ],
            ],
        ],
    ],
    'options_callback' => [
        MetaModels\NoteListBundle\Bridge\DcaCallbackBridge::class,
        'getNoteListOptions'
    ],
    'sql'              => 'text NULL',
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['metamodel_customTplEmail'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_form_field']['metamodel_customTplEmail'],
    'exclude'          => true,
    'inputType'        => 'select',
    'options_callback' => [
        MetaModels\NoteListBundle\Bridge\DcaCallbackBridge::class,
        'getEmailTemplates'
    ],
    'sql'              => 'varchar(255) NULL',
    'eval'             => [
        'includeBlankOption' => true
    ]
];
