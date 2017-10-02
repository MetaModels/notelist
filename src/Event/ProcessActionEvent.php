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

use MetaModels\IMetaModel;
use MetaModels\NoteList\Storage\NoteListStorage;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event gets triggered when an action needs to get processed.
 */
class ProcessActionEvent extends Event
{
    /**
     * The action.
     *
     * @var string
     */
    private $action;

    /**
     * The payload added to the request.
     *
     * @var string[]
     */
    private $payload;

    /**
     * The note list the action applies to.
     *
     * @var NoteListStorage
     */
    private $noteList;

    /**
     * The MetaModel this storage tracks.
     *
     * @var IMetaModel
     */
    private $metaModel;

    /**
     * The processing result.
     *
     * @var bool
     */
    private $success = false;

    /**
     * Create a new instance.
     *
     * @param string          $action    The action to be processed.
     * @param string[]        $payload   The accompanying payload.
     * @param NoteListStorage $noteList  The note list this applies to.
     * @param IMetaModel      $metaModel The MetaModel instance.
     */
    public function __construct(string $action, array $payload, NoteListStorage $noteList, IMetaModel $metaModel)
    {
        $this->action    = $action;
        $this->payload   = $payload;
        $this->noteList  = $noteList;
        $this->metaModel = $metaModel;
    }

    /**
     * Retrieve action.
     *
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Retrieve payload.
     *
     * @return string[]
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * Retrieve payload keys.
     *
     * @return string[]
     */
    public function getPayloadKeys(): array
    {
        return array_keys($this->payload);
    }

    /**
     * Retrieve a payload value.
     *
     * @param string $key The key to retrieve.
     *
     * @return null|mixed
     */
    public function getPayloadValue(string $key)
    {
        return ($this->payload[$key] ?? null);
    }

    /**
     * Check if a key exists in the payload.
     *
     * @param string $key The key to retrieve.
     *
     * @return bool
     */
    public function hasPayloadValue(string $key): bool
    {
        return array_key_exists($key, $this->payload);
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
     * Retrieve metaModel.
     *
     * @return IMetaModel
     */
    public function getMetaModel()
    {
        return $this->metaModel;
    }

    /**
     * Retrieve success.
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Set success.
     *
     * @param bool $success The new value.
     *
     * @return self
     */
    public function setSuccess(bool $success = true): ProcessActionEvent
    {
        $this->success = $success;

        return $this;
    }
}
