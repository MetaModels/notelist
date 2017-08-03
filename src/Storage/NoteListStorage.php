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

namespace MetaModels\NoteList\Storage;

use MetaModels\Filter\Rules\StaticIdList;
use MetaModels\IItem;
use MetaModels\IItems;
use MetaModels\IMetaModel;

/**
 * This class abstracts the storage of items.
 */
class NoteListStorage
{
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
     * Create a new instance.
     *
     * @param IMetaModel       $metaModel      The metamodel this storage tracks.
     * @param AdapterInterface $storageAdapter The storage adapter to use.
     * @param string           $storageKey     The key to use in the session adapter.
     * @param array            $names          The human readable names as array locale => value.
     */
    public function __construct(
        IMetaModel $metaModel,
        AdapterInterface $storageAdapter,
        string $storageKey,
        array $names
    ) {
        $this->metaModel      = $metaModel;
        $this->storageAdapter = $storageAdapter;
        $this->storageKey     = $storageKey;
        $this->names          = $names;
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
     * Add an item.
     *
     * @param IItem $item The item to add.
     *
     * @return void
     */
    public function add(IItem $item)
    {
        $this->storageAdapter->setKey(
            $this->storageKey,
            array_unique(array_merge($this->getItemIds(), [$item->get('id')]))
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
        $idList = $this->getItemIds();
        foreach ($idList as $key => $candidate) {
            if ($search === $candidate) {
                unset($idList[$key]);
                break;
            }
        }
        $this->storageAdapter->setKey($this->storageKey, $idList);
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
    }

    /**
     * Retrieve the list of contained ids.
     *
     * @return string[]
     */
    public function getItemIds()
    {
        return $this->storageAdapter->getKey($this->storageKey);
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
}
