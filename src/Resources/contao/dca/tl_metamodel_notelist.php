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
            'parent'  => [
                'source' => 'tl_metamodel'
            ],
            'default' => [
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
        'sorting'           => [
            'mode'         => 4,
            'fields'       => [],
            'flag'         => 1,
            'panelLayout'  => 'sort,limit',
            'headerFields' => ['name']
        ],
        'label'             => [
            'fields' => ['name'],
            'format' => '%s',
        ],
        'global_operations' => [
            'all' => [
                'label'       => 'all.label',
                'description' => 'all.description',
                'href'        => 'act=select',
                'class'       => 'header_edit_all',
                'attributes'  => 'onclick="Backend.getScrollOffset();"'
            ],
        ],
        'operations'        => [
            'edit'   => [
                'label'       => 'editheader.label',
                'description' => 'editheader.description',
                'href'        => 'act=edit',
                'icon'        => 'edit.gif',
            ],
            'cut'    => [
                'label'       => 'cut.label',
                'description' => 'cut.description',
                'href'        => 'act=paste&amp;mode=cut',
                'icon'        => 'cut.gif'
            ],
            'delete' => [
                'label'       => 'delete.label',
                'description' => 'delete.description',
                'href'        => 'act=delete',
                'icon'        => 'delete.gif',
                'attributes'  => 'onclick="if (!confirm(this.dataset.msgConfirm)) return false; Backend.getScrollOffset();"',
            ],
            'show'   => [
                'label'       => 'show.label',
                'description' => 'show.description',
                'href'        => 'act=show',
                'icon'        => 'show.gif'
            ],
        ]
    ],
    'metapalettes' => [
        'default' => [
            'config' => [
                'name',
                'storageAdapter',
                'filter',
                'form'
            ],
        ]
    ],
    'fields'       => [
        'id'             => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment'
        ],
        'pid'            => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'tstamp'         => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'name'           => [
            'label'       => 'name.label',
            'description' => 'name.description',
            'sorting'     => true,
            'flag'        => 3,
            'length'      => 1,
            'exclude'     => true,
            'inputType'   => 'text',
            'eval'        => [
                'mandatory' => true,
                'tl_class'  => 'w50'
            ],
            'sql'         => "varchar(255) NOT NULL default ''"
        ],
        'storageAdapter' => [
            'label'       => 'storageAdapter.label',
            'description' => 'storageAdapter.description',
            'exclude'     => true,
            'inputType'   => 'select',
            'eval'        => [
                'fetchOptions'       => true,
                'includeBlankOption' => true,
                'mandatory'          => true,
                'maxlength'          => 64,
                'doNotCopy'          => true,
                'tl_class'           => 'w50'
            ],
            'sql'         => "varchar(64) NOT NULL default ''"
        ],
        'filter'         => [
            'label'       => 'filter.label',
            'description' => 'filter.description',
            'exclude'     => true,
            'inputType'   => 'select',
            'eval'        => [
                'fetchOptions'       => true,
                'includeBlankOption' => true,
                'maxlength'          => 64,
                'doNotCopy'          => true,
                'tl_class'           => 'clr w50'
            ],
            'sql'         => "int(10) unsigned NOT NULL default '0'"
        ],
        'form'           => [
            'label'       => 'form.label',
            'description' => 'form.description',
            'exclude'     => true,
            'inputType'   => 'select',
            'eval'        => [
                'fetchOptions'       => true,
                'includeBlankOption' => true,
                'maxlength'          => 64,
                'doNotCopy'          => true,
                'tl_class'           => 'w50'
            ],
            'sql'         => "int(10) unsigned NOT NULL default '0'"
        ],
    ],
];
