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

declare(strict_types = 1);

namespace MetaModels\NoteListBundle\Storage;

use MetaModels\Filter\Rules\StaticIdList;
use MetaModels\Filter\Setting\ICollection;
use MetaModels\IItem;
use MetaModels\IItems;
use MetaModels\IMetaModel;
use MetaModels\NoteListBundle\Event\ManipulateNoteListEvent;
use MetaModels\NoteListBundle\Event\NoteListEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This class abstracts the storage of items.
 *
 * The stored data in the adapter is built as follows:
 * [
 *   'items' => ['item1', 'item2', ... ],
 *   'meta-data' => [
 *     'item1' => [
 *       'foo' => 'bar'
 *     ]
 *   ]
 * ]
 */
class NoteListStorage
{
    /**
     * The key to use in the storage array for meta data.
     */
    const META_KEY = 'meta-data';

    /**
     * The key to use in the storage array for item ids.
     */
    const ITEMS_KEY = 'items';

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * The MetaModel this storage tracks.
     *
     * @var IMetaModel
     */
    private $metaModel;

    /**
     * The key to use in the storage adapter.
     *
     * @var string
     */
    private $storageKey;

    /**
     * The storage adapter.
     *
     * @var AdapterInterface
     */
    private $storageAdapter;

    /**
     * The human readable names as array locale => value.
     *
     * @var array
     */
    private $names;

    /**
     * The filter setting the items must match agains.
     *
     * @var ICollection|null
     */
    private $filter;

    /**
     * The cached item count.
     *
     * @var bool|string[]|null
     */
    private $filterCache = false;

    /**
     * The meta data for the note list.
     *
     * @var ValueBag
     */
    private $meta;

    /**
     * Create a new instance.
     *
     * @param EventDispatcherInterface $dispatcher     The event dispatcher.
     * @param IMetaModel               $metaModel      The metamodel this storage tracks.
     * @param AdapterInterface         $storageAdapter The storage adapter to use.
     * @param string                   $storageKey     The key to use in the session adapter.
     * @param array                    $names          The human readable names as array locale => value.
     * @param ICollection|null         $filter         The filter setting.
     * @param ValueBag                 $meta           The meta data for the note list.
     */
    public function __construct(
        EventDispatcherInterface $dispatcher,
        IMetaModel $metaModel,
        AdapterInterface $storageAdapter,
        string $storageKey,
        array $names,
        ICollection $filter = null,
        ValueBag $meta = null
    ) {
        $this->dispatcher     = $dispatcher;
        $this->metaModel      = $metaModel;
        $this->storageAdapter = $storageAdapter;
        $this->storageKey     = $storageKey;
        $this->names          = $names;
        $this->filter         = $filter;
        $this->meta           = $meta ?: new ValueBag([]);
    }

    /**
     * Retrieve the storage key.
     *
     * @return string
     */
    public function getStorageKey()
    {
        return $this->storageKey;
    }

    /**
     * Obtain the meta data value bag.
     *
     * @return ValueBag
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Check if the note list accepts the passed item.
     *
     * @param IItem $item The item to test.
     *
     * @return bool
     */
    public function accepts(IItem $item)
    {
        if (!$this->filter) {
            return true;
        }
        if (false === $this->filterCache) {
            $filter = $this->metaModel->getEmptyFilter();
            // Check if we accept the item.
            $this->filter->addRules($filter, []);
            $this->filterCache = $filter->getMatchingIds();
        }
        return ($this->filterCache === null) || in_array($item->get('id'), $this->filterCache);
    }

    /**
     * Add an item.
     *
     * @param IItem $item The item to add.
     *
     * @param array $meta The meta data to set.
     *
     * @return void
     */
    public function add(IItem $item, array $meta = [])
    {
        if (!$this->accepts($item)) {
            return;
        }

        $itemId = $item->get('id');
        $data   = $this->getData();

        $data[self::META_KEY][$itemId] = $meta;
        $this->setData([
            self::ITEMS_KEY => array_unique(array_merge($data[self::ITEMS_KEY], [$itemId])),
            self::META_KEY  => $data[self::META_KEY]
        ]);
        $this->dispatcher->dispatch(
            new ManipulateNoteListEvent(
                $this->metaModel,
                $this,
                ManipulateNoteListEvent::OPERATION_ADD,
                $item
            ),
            NoteListEvents::MANIPULATE_NOTE_LIST
        );
    }

    /**
     * Remove an item.
     *
     * @param IItem $item The item to remove.
     *
     * @return void
     */
    public function remove(IItem $item)
    {
        $search = $item->get('id');
        $data   = $this->getData();
        foreach ($data[self::ITEMS_KEY] as $key => $candidate) {
            if ($search === $candidate) {
                unset($data[self::ITEMS_KEY][$key]);
                unset($data[self::META_KEY][$search]);
                $this->dispatcher->dispatch(
                    new ManipulateNoteListEvent(
                        $this->metaModel,
                        $this,
                        ManipulateNoteListEvent::OPERATION_REMOVE,
                        $item
                    ),
                    NoteListEvents::MANIPULATE_NOTE_LIST
                );
                break;
            }
        }
        $this->setData($data);
    }

    /**
     * Retrieve the meta data information for an item.
     *
     * @param IItem $item The item to retrieve the meta data for.
     *
     * @return array
     */
    public function getMetaDataFor(IItem $item)
    {
        $data = $this->getData();

        return ($data[self::META_KEY][$item->get('id')] ?? []);
    }

    /**
     * Update the meta data information for an item.
     *
     * @param IItem $item The item to retrieve the meta data for.
     *
     * @param array $meta The meta information to store.
     *
     * @return void
     */
    public function updateMetaDataFor(IItem $item, array $meta)
    {
        $data = $this->getData();

        $data[self::META_KEY][$item->get('id')] = $meta;

        $this->setData($data);
    }

    /**
     * Check if an item is contained.
     *
     * @param IItem $item The item to search.
     *
     * @return bool
     */
    public function has(IItem $item)
    {
        $search = $item->get('id');
        $idList = $this->getItemIds();
        foreach ($idList as $candidate) {
            if ($search === $candidate) {
                return true;
            }
        }
        return false;
    }

    /**
     * Clear the list.
     *
     * @return void
     */
    public function clear()
    {
        $this->storageAdapter->setKey($this->storageKey, []);
        $this->dispatcher->dispatch(
            new ManipulateNoteListEvent($this->metaModel, $this, ManipulateNoteListEvent::OPERATION_CLEAR),
            NoteListEvents::MANIPULATE_NOTE_LIST
        );
    }

    /**
     * Retrieve the list of contained ids.
     *
     * @return string[]
     */
    public function getItemIds()
    {
        return $this->getData()[self::ITEMS_KEY];
    }

    /**
     * Retrieve the list of items.
     *
     * @return IItems
     */
    public function getItems()
    {
        $filter = $this->metaModel->getEmptyFilter();

        $filter->addFilterRule(new StaticIdList($this->getItemIds()));

        return $this->metaModel->findByFilter($filter);
    }

    /**
     * Retrieve the amount of items stored in the list.
     *
     * @return int
     */
    public function getCount()
    {
        return count($this->getItemIds());
    }

    /**
     * Retrieve the name of this storage.
     *
     * @return string
     */
    public function getName()
    {
        if ($this->metaModel->isTranslated()) {
            if (isset($this->names[$this->metaModel->getActiveLanguage()])) {
                return $this->names[$this->metaModel->getActiveLanguage()];
            }
            if (isset($this->names[$this->metaModel->getFallbackLanguage()])) {
                return $this->names[$this->metaModel->getFallbackLanguage()];
            }
        }

        return current($this->names);
    }

    /**
     * Retrieve the data from the storage.
     *
     * @return array
     */
    private function getData()
    {
        return $this->storageAdapter->getKey($this->storageKey) ?: [
            self::ITEMS_KEY => [],
            self::META_KEY  => [],
        ];
    }

    /**
     * Set the key in the storage.
     *
     * @param array $data The data to set.
     *
     * @return void
     */
    private function setData(array $data)
    {
        $this->storageAdapter->setKey($this->storageKey, $data);
    }
}
