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

declare(strict_types=1);

namespace MetaModels\NoteListBundle\Filter;

use MetaModels\Filter\IFilterRule;
use MetaModels\NoteListBundle\Storage\NoteListStorage;

/**
 * This filter rule returns all items currently contained within the note list.
 */
class NoteListFilterRule implements IFilterRule
{
    /**
     * The note list.
     *
     * @var NoteListStorage
     */
    private NoteListStorage $noteList;

    /**
     * Create a new instance.
     *
     * @param NoteListStorage $noteList The note list to use.
     */
    public function __construct(NoteListStorage $noteList)
    {
        $this->noteList = $noteList;
    }

    /**
     * {@inheritDoc}
     */
    public function getMatchingIds(): ?array
    {
        return $this->noteList->getItemIds();
    }
}
