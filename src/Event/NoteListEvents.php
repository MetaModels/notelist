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

namespace MetaModels\NoteList\Event;

/**
 * This class holds the constant names of all note list events.
 */
class NoteListEvents
{
    /**
     * This event gets triggered when a note list gets rendered in a frontend form.
     */
    const PARSE_NOTE_LIST_FORM = 'metamodels.note-list.parse-note-list-form';

    /**
     * This event gets triggered when a note list gets manipulated.
     */
    const MANIPULATE_NOTE_LIST = 'metamodels.note-list.manipulate';
}
