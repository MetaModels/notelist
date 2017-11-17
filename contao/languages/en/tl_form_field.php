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

$GLOBALS['TL_LANG']['FFL']['metamodel_notelist'][0] = 'MetaModels note list';
$GLOBALS['TL_LANG']['FFL']['metamodel_notelist'][1] = 'MetaModels note list';

$GLOBALS['TL_LANG']['tl_form_field']['metamodel'][0]          = 'MetaModel';
$GLOBALS['TL_LANG']['tl_form_field']['metamodel'][1]          = 'Select the MetaModel to fetch the note lists from.';
$GLOBALS['TL_LANG']['tl_form_field']['metamodel_notelist'][0] = 'Note lists';
$GLOBALS['TL_LANG']['tl_form_field']['metamodel_notelist'][1] = 'Select all note lists that shall get added.';

$GLOBALS['TL_LANG']['tl_form_field']['metamodel_notelist_notelist'][0]               = 'Notelist';
$GLOBALS['TL_LANG']['tl_form_field']['metamodel_notelist_notelist'][1]               = 'Select the note list for the different outputs.';
$GLOBALS['TL_LANG']['tl_form_field']['metamodel_notelist_rendersetting_frontend'][0] = 'Frontend';
$GLOBALS['TL_LANG']['tl_form_field']['metamodel_notelist_rendersetting_frontend'][1] = 'Select the rendersetting for the frontend output.';
$GLOBALS['TL_LANG']['tl_form_field']['metamodel_notelist_rendersetting_email'][0]    = 'Email';
$GLOBALS['TL_LANG']['tl_form_field']['metamodel_notelist_rendersetting_email'][1]    = 'Select the rendersetting for the email output.';

$GLOBALS['TL_LANG']['tl_form_field']['metamodel_customTplEmail'][0] = 'Custom email template';
$GLOBALS['TL_LANG']['tl_form_field']['metamodel_customTplEmail'][1] = 
    'Select the custom email template - this template encloses the output of the rendersetting for the email.';
