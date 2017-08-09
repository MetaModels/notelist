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

$GLOBALS['TL_DCA']['tl_metamodel_notelist'] = [
    'config'       => [
        'dataContainer'    => 'General',
        'switchToEdit'     => true,
        'enableVersioning' => false,
        'sql'              => [
            'keys' => [
                'id'  => 'primary',
                'pid' => 'index',
            ],
        ],
    ],
    'dca_config'   => [
        'data_provider'  => [
            'parent' => [
                'source' => 'tl_metamodel'
            ],
            'default'      => [
                'source' => 'tl_metamodel_notelist'
            ],
        ],
        'childCondition' => [
            [
                'from'    => 'tl_metamodel',
                'to'      => 'tl_metamodel_notelist',
                'setOn'   => [
                    [
                        'to_field'   => 'pid',
                        'from_field' => 'id',
                    ],
                ],
                'filter'  => [
                    [
                        'local'     => 'pid',
                        'remote'    => 'id',
                        'operation' => '=',
                    ],
                ],
                'inverse' => [
                    [
                        'local'     => 'pid',
                        'remote'    => 'id',
                        'operation' => '=',
                    ],
                ]
            ],

        ],
    ],
    'list'         => [
        'sorting'         => [
            'mode'        => 4,
            'fields'      => [],
            'flag'        => 1,
            'panelLayout' => 'sort,limit',
            'headerFields'    => ['name']
        ],
        'label'             => [
            'fields' => ['name'],
            'format' => '%s',
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            ],
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_notelist']['editheader'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ],
            'cut'    => [
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_notelist']['cut'],
                'href'  => 'act=paste&amp;mode=cut',
                'icon'  => 'cut.gif'
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_metamodel_notelist']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => sprintf(
                    'onclick="if (!confirm(\'%s\')) return false; Backend.getScrollOffset();"',
                    $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                )
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_notelist']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif'
            ],
        ]
    ],
    'metapalettes' => [
        'default' => [
            'config' => [
                'name',
                'storageAdapter',
                'filter'
            ],
        ]
    ],
    'fields'       => [
        'id'     => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment'
        ],
        'pid'         => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'name'           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_notelist']['name'],
            'sorting'   => true,
            'flag'      => 3,
            'length'    => 1,
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => [
                'mandatory' => true,
            ],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'storageAdapter' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_notelist']['storageAdapter'],
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => [
                'fetchOptions'       => true,
                'includeBlankOption' => true,
                'mandatory'          => true,
                'maxlength'          => 64,
                'doNotCopy'          => true,
                'tl_class'           => 'w50'
            ],
            'sql'       => "varchar(64) NOT NULL default ''"
        ],
        'filter' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_notelist']['filter'],
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => [
                'fetchOptions'       => true,
                'includeBlankOption' => true,
                'mandatory'          => true,
                'maxlength'          => 64,
                'doNotCopy'          => true,
                'tl_class'           => 'w50'
            ],
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ]
    ],
];
