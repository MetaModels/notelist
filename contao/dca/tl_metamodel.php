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

$GLOBALS['TL_DCA']['tl_metamodel']['list']['operations']['notelist'] = [
    'label'   => &$GLOBALS['TL_LANG']['tl_metamodel']['notelist'],
    'href'    => 'table=tl_metamodel_notelist',
    'icon'    => 'system/modules/metamodels_notelist/public/images/icons/notelist.png',
    'idparam' => 'pid'
];
