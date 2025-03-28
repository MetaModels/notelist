<?php

/**
 * This file is part of MetaModels/notelist.
 *
 * (c) 2017-2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2018 The MetaModels team.
 * @license    https://github.com/MetaModels/notelist/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

use MetaModels\NoteListBundle\Bridge\FormFieldBridge;
use MetaModels\NoteListBundle\Bridge\InsertTagBridge;
use MetaModels\NoteListBundle\Bridge\ProcessFormDataBridge;

$GLOBALS['BE_MOD']['metamodels']['metamodels']['tables'][] = 'tl_metamodel_notelist';

$GLOBALS['TL_FFL']['metamodel_notelist'] = FormFieldBridge::class;

$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = array(InsertTagBridge::class, 'replaceInsertTags');

$GLOBALS['TL_HOOKS']['processFormData'][] = array(ProcessFormDataBridge::class, 'clearNotelistFormData');
