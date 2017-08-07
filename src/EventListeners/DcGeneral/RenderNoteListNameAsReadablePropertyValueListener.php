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

namespace MetaModels\NoteList\EventListeners\DcGeneral;

use ContaoCommunityAlliance\DcGeneral\View\Event\RenderReadablePropertyValueEvent;

/**
 * This class renders the note list name as readable property value for dc-general.
 */
class RenderNoteListNameAsReadablePropertyValueListener
{
    /**
     * Render the value.
     *
     * @param RenderReadablePropertyValueEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function render(RenderReadablePropertyValueEvent $event)
    {
        if (null !== $event->getRendered()) {
            return;
        }

        $value = $event->getValue();
        if (!is_array($value)) {
            $event->setRendered($value);
            return;
        }

        if (isset($value[$GLOBALS['TL_LANGUAGE']])) {
            $event->setRendered($value[$GLOBALS['TL_LANGUAGE']]);
        }

        $event->setRendered(current($value));
    }
}
