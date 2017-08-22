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

namespace MetaModels\NoteList\Test\Storage;

use MetaModels\IItem;
use MetaModels\IMetaModel;
use MetaModels\NoteList\Event\ManipulateNoteListEvent;
use MetaModels\NoteList\Event\NoteListEvents;
use MetaModels\NoteList\Storage\AdapterInterface;
use MetaModels\NoteList\Storage\NoteListStorage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This tests the NoteListStorage class.
 */
class NoteListStorageTest extends TestCase
{
    /**
     * Test that the class can be instantiated.
     *
     * @return void
     */
    public function testInstantiation()
    {
        $dispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);
        $metaModel  = $this->getMockForAbstractClass(IMetaModel::class);
        $adapter    = $this->getMockForAbstractClass(AdapterInterface::class);

        $list = new NoteListStorage($dispatcher, $metaModel, $adapter, 'storage-key', []);

        $this->assertInstanceOf('MetaModels\NoteList\Storage\NoteListStorage', $list);
    }

    /**
     * Test that adding of items is correctly mapped to the adapter.
     *
     * @return void
     */
    public function testAddingOfItemAdds()
    {
        $dispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);
        $metaModel  = $this->getMockForAbstractClass(IMetaModel::class);
        $adapter    = $this->getMockForAbstractClass(AdapterInterface::class);
        $item       = $this->getMockForAbstractClass(IItem::class);

        $adapter->expects($this->once())->method('getKey')->with('storage-key')->willReturn([
            NoteListStorage::ITEMS_KEY => ['23'],
            NoteListStorage::META_KEY  => ['23' => []]
        ]);
        $adapter->expects($this->once())->method('setKey')->with(
            'storage-key',
            [
                NoteListStorage::ITEMS_KEY => ['23', '42'],
                NoteListStorage::META_KEY  => [
                    '23' => [],
                    '42' => []
                ]
            ]
        );
        $item->expects($this->once())->method('get')->with('id')->willReturn('42');

        $list = new NoteListStorage($dispatcher, $metaModel, $adapter, 'storage-key', []);
        $dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                NoteListEvents::MANIPULATE_NOTE_LIST,
                new ManipulateNoteListEvent($metaModel, $list, ManipulateNoteListEvent::OPERATION_ADD, $item)
            );

        $list->add($item);
    }

    /**
     * Test that removal of items is correctly mapped to the adapter.
     *
     * @return void
     */
    public function testRemovalOfItemRemoves()
    {
        $dispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);
        $metaModel  = $this->getMockForAbstractClass(IMetaModel::class);
        $adapter    = $this->getMockForAbstractClass(AdapterInterface::class);
        $item       = $this->getMockForAbstractClass(IItem::class);

        $adapter->expects($this->once())->method('getKey')->with('storage-key')->willReturn([
            NoteListStorage::ITEMS_KEY => ['23', '42'],
            NoteListStorage::META_KEY  => [
                '23' => [],
                '42' => []
            ]
        ]);
        $adapter->expects($this->once())->method('setKey')->with(
            'storage-key',
            [
                NoteListStorage::ITEMS_KEY => ['23'],
                NoteListStorage::META_KEY  => ['23' => []]
            ]
        );
        $item->expects($this->once())->method('get')->with('id')->willReturn('42');

        $list = new NoteListStorage($dispatcher, $metaModel, $adapter, 'storage-key', []);
        $dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                NoteListEvents::MANIPULATE_NOTE_LIST,
                new ManipulateNoteListEvent($metaModel, $list, ManipulateNoteListEvent::OPERATION_REMOVE, $item)
            );

        $list->remove($item);
    }

    /**
     * Test that clearing of the list is correctly mapped to the adapter.
     *
     * @return void
     */
    public function testClearingWorks()
    {
        $dispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);
        $metaModel  = $this->getMockForAbstractClass(IMetaModel::class);
        $adapter    = $this->getMockForAbstractClass(AdapterInterface::class);

        $adapter->expects($this->once())->method('setKey')->with('storage-key', []);

        $list = new NoteListStorage($dispatcher, $metaModel, $adapter, 'storage-key', []);
        $dispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                NoteListEvents::MANIPULATE_NOTE_LIST,
                new ManipulateNoteListEvent($metaModel, $list, ManipulateNoteListEvent::OPERATION_CLEAR)
            );
        $list->clear();
    }

    /**
     * Test that retrieval of count returns the correct amount.
     *
     * @return void
     */
    public function testGetCountReturnsAmountOfStoredIds()
    {
        $dispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);
        $metaModel  = $this->getMockForAbstractClass(IMetaModel::class);
        $adapter    = $this->getMockForAbstractClass(AdapterInterface::class);

        $adapter->expects($this->once())->method('getKey')->with('storage-key')->willReturn([
            NoteListStorage::ITEMS_KEY => ['23', '42'],
            NoteListStorage::META_KEY  => [
                '23' => [],
                '42' => []
            ]
        ]);

        $list = new NoteListStorage($dispatcher, $metaModel, $adapter, 'storage-key', []);

        $this->assertSame(2, $list->getCount());
    }
}
