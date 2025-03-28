<?php

/**
 * This file is part of MetaModels/notelist.
 *
 * (c) 2017-2023 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2017-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/notelist/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\NoteListBundle\Event;

use MetaModels\IItem;
use MetaModels\IMetaModel;
use MetaModels\NoteListBundle\Storage\NoteListStorage;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event gets triggered when a note list gets manipulated.
 */
class ManipulateNoteListEvent extends Event
{
    /**
     * An item has been added.
     */
    public const OPERATION_ADD = 'add';

    /**
     * An item has been removed.
     */
    public const OPERATION_REMOVE = 'remove';

    /**
     * The list has been cleared.
     */
    public const OPERATION_CLEAR = 'clear';

    /**
     * The MetaModel.
     *
     * @var IMetaModel
     */
    private IMetaModel $metaModel;

    /**
     * The note list being manipulated.
     *
     * @var NoteListStorage
     */
    private NoteListStorage $noteList;

    /**
     * The operation.
     *
     * @var string
     */
    private string $operation;

    /**
     * The item being added/removed (if any).
     *
     * @var IItem|null
     */
    private ?IItem $item;

    /**
     * Create a new instance.
     *
     * @param IMetaModel      $metaModel The MetaModel.
     * @param NoteListStorage $noteList  The note list.
     * @param string          $operation The operation.
     * @param IItem|null      $item      The item being added/removed (if any).
     */
    public function __construct(IMetaModel $metaModel, NoteListStorage $noteList, string $operation, IItem $item = null)
    {
        $this->metaModel = $metaModel;
        $this->noteList  = $noteList;
        $this->operation = $operation;
        $this->item      = $item;
    }

    /**
     * Retrieve metaModel.
     *
     * @return IMetaModel
     */
    public function getMetaModel()
    {
        return $this->metaModel;
    }

    /**
     * Retrieve noteList.
     *
     * @return NoteListStorage
     */
    public function getNoteList()
    {
        return $this->noteList;
    }

    /**
     * Retrieve operation.
     *
     * @return string
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * Retrieve item.
     *
     * @return IItem|null
     */
    public function getItem()
    {
        return $this->item;
    }
}
