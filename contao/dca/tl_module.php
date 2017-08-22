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

declare(strict_types = 1);

$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'metamodel_add_notelist';
$GLOBALS['TL_DCA']['tl_module']['palettes']['metamodel_list'] =
    str_replace(
        ';{template_legend:hide}',
        ';{metamodel_notelist_legend:hide},metamodel_add_notelist;{template_legend:hide}',
        $GLOBALS['TL_DCA']['tl_module']['palettes']['metamodel_list']
    );

$GLOBALS['TL_DCA']['tl_module']['subpalettes']['metamodel_add_notelist'] = '';

$GLOBALS['TL_DCA']['tl_module']['fields']['add_notelist'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['fields']['metamodel_add_notelist'],
    'inputType' => 'checkbox',
    'eval'      => [
        'submitOnChange' => true
    ],
    'sql'       => 'char(1) NOT NULL default \'\''
];

$GLOBALS['TL_DCA']['tl_module']['fields']['metamodel_notelist'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_module']['fields']['metamodel_notelist'],
    'inputType'        => 'checkboxWizard',
    'options_callback' => [MetaModels\NoteList\Bridge\DcaCallbackBridge::class, 'getNoteListOptions'],
    'eval' => [
        'multiple' => true
    ],
    'sql'              => 'blob NULL'
];
