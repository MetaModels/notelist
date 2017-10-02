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

$GLOBALS['TL_DCA']['tl_metamodel_filtersetting']['metapalettes']['notelist extends default'] = [
    '+config' => [
        'notelist'
    ],
];

$GLOBALS['TL_DCA']['tl_metamodel_filtersetting']['fields']['notelist'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['notelist'],
    'exclude'                 => true,
    'default'                 => true,
    'inputType'               => 'select',
    'sql'                     => 'int(10) unsigned NOT NULL default \'0\'',
    'eval'                    => [
        'tl_class'            => 'w50',
        'includeBlankOption' => true,
        'mandatory'          => true,
    ],
];
