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

namespace MetaModels\NoteList\Bridge;

use MetaModels\NoteList\InsertTags;

/**
 * This class handles insert tag relaying.
 */
class InsertTagBridge
{
    const TAG_BASE = 'metamodels_notelist';

    /**
     * Delegate the insert tag processing.
     *
     * @param string $tagName The insert tag name.
     *
     * @return bool|string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function replaceInsertTags($tagName)
    {
        $arguments = explode('::', $tagName);
        if (self::TAG_BASE !== $arguments[0]) {
            return false;
        }

        // pop off the base.
        array_shift($arguments);

        /** @var InsertTags $processor */
        $processor = $GLOBALS['container']['metamodels-notelist.insert-tags'];
        // Process the tag.
        switch (array_shift($arguments)) {
            case 'sum':
                return $processor->processNoteListSum($arguments);
            default:
        }

        return false;
    }
}
