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

namespace MetaModels\NoteListBundle\Test\Storage;

use MetaModels\Filter\IFilter;
use MetaModels\IItem;
use MetaModels\IItems;
use MetaModels\IMetaModel;
use MetaModels\NoteListBundle\Event\ManipulateNoteListEvent;
use MetaModels\NoteListBundle\Event\NoteListEvents;
use MetaModels\NoteListBundle\Storage\AdapterInterface;
use MetaModels\NoteListBundle\Storage\NoteListStorage;
use MetaModels\NoteListBundle\Storage\ValueBag;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This tests the NoteListStorage class.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class NoteListStorageTest extends TestCase
{
    /**
     * Test that the class can be instantiated.
     */
    public function testInstantiation(): void
    {
        $dispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);
        $metaModel  = $this->getMockForAbstractClass(IMetaModel::class);
        $adapter    = $this->getMockForAbstractClass(AdapterInterface::class);

        $list = new NoteListStorage($dispatcher, $metaModel, $adapter, 'storage-key', []);

        $this->assertInstanceOf('MetaModels\NoteList\Storage\NoteListStorage', $list);
        $this->assertInstanceOf('MetaModels\NoteList\Storage\ValueBag', $list->getMeta());
    }

    /**
     * Test that adding of items is correctly mapped to the adapter.
     */
    public function testAddingOfItemAdds(): void
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
                new ManipulateNoteListEvent($metaModel, $list, ManipulateNoteListEvent::OPERATION_ADD, $item),
                NoteListEvents::MANIPULATE_NOTE_LIST
            );

        $list->add($item);
    }

    /**
     * Test that removal of items is correctly mapped to the adapter.
     */
    public function testRemovalOfItemRemoves(): void
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
                new ManipulateNoteListEvent($metaModel, $list, ManipulateNoteListEvent::OPERATION_REMOVE, $item),
                NoteListEvents::MANIPULATE_NOTE_LIST
            );

        $list->remove($item);
    }

    /**
     * Test that has() returns true when an item is contained.
     */
    public function testHasReturnsTrueWhenItemIsContained(): void
    {
        $dispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);
        $metaModel  = $this->getMockForAbstractClass(IMetaModel::class);
        $adapter    = $this->getMockForAbstractClass(AdapterInterface::class);
        $item       = $this->getMockForAbstractClass(IItem::class);
        $adapter->expects($this->once())->method('getKey')->with('storage-key')->willReturn([
            NoteListStorage::ITEMS_KEY => ['23', '42'],
            NoteListStorage::META_KEY  => ['23' => [], '42' => []]
        ]);
        $item->expects($this->once())->method('get')->with('id')->willReturn('42');

        $list = new NoteListStorage($dispatcher, $metaModel, $adapter, 'storage-key', []);

        $this->assertTrue($list->has($item));
    }

    /**
     * Test that has() returns false when an item is not contained.
     */
    public function testHasReturnsFalseWhenItemIsNotContained(): void
    {
        $dispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);
        $metaModel  = $this->getMockForAbstractClass(IMetaModel::class);
        $adapter    = $this->getMockForAbstractClass(AdapterInterface::class);
        $item       = $this->getMockForAbstractClass(IItem::class);
        $adapter->expects($this->once())->method('getKey')->with('storage-key')->willReturn([
            NoteListStorage::ITEMS_KEY => ['23'],
            NoteListStorage::META_KEY  => ['23' => []]
        ]);
        $item->expects($this->once())->method('get')->with('id')->willReturn('42');

        $list = new NoteListStorage($dispatcher, $metaModel, $adapter, 'storage-key', []);

        $this->assertFalse($list->has($item));
    }

    /**
     * Test that clearing of the list is correctly mapped to the adapter.
     */
    public function testClearingWorks(): void
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
                new ManipulateNoteListEvent($metaModel, $list, ManipulateNoteListEvent::OPERATION_CLEAR),
                NoteListEvents::MANIPULATE_NOTE_LIST
            );
        $list->clear();
    }

    /**
     * Test that retrieval of count returns the correct amount.
     */
    public function testGetCountReturnsAmountOfStoredIds(): void
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

    /**
     * Test that the correct storage key is returned.
     */
    public function testStorageKeyIsReturned(): void
    {
        $dispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);
        $metaModel  = $this->getMockForAbstractClass(IMetaModel::class);
        $adapter    = $this->getMockForAbstractClass(AdapterInterface::class);

        $list = new NoteListStorage($dispatcher, $metaModel, $adapter, 'storage-key', []);

        $this->assertSame('storage-key', $list->getStorageKey());
    }

    /**
     * Test that the correct value bag is returned.
     */
    public function testValueBagIsReturned(): void
    {
        $dispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);
        $metaModel  = $this->getMockForAbstractClass(IMetaModel::class);
        $adapter    = $this->getMockForAbstractClass(AdapterInterface::class);
        $valueBag   = new ValueBag([]);

        $list = new NoteListStorage($dispatcher, $metaModel, $adapter, 'storage-key', [], null, $valueBag);

        $this->assertSame($valueBag, $list->getMeta());
    }

    /**
     * Test that getItems() returns the result from the filter
     */
    public function testGetItemsReturnsItemsFromFilter(): void
    {
        $dispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);
        $metaModel  = $this->getMockForAbstractClass(IMetaModel::class);
        $adapter    = $this->getMockForAbstractClass(AdapterInterface::class);

        $filter = $this->getMockForAbstractClass(IFilter::class);
        $items  = $this->getMockForAbstractClass(IItems::class);

        $metaModel->expects($this->once())->method('getEmptyFilter')->willReturn($filter);
        $metaModel->expects($this->once())->method('findByFilter')->with($filter)->willReturn($items);

        $list = new NoteListStorage($dispatcher, $metaModel, $adapter, 'storage-key', []);

        $this->assertSame($items, $list->getItems());
    }

    /**
     * Test the meta data handling.
     */
    public function testMetaDataHandling(): void
    {
        $dispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);
        $metaModel  = $this->getMockForAbstractClass(IMetaModel::class);
        $adapter    = $this->getMockForAbstractClass(AdapterInterface::class);

        $data = [
            NoteListStorage::ITEMS_KEY => ['42'],
            NoteListStorage::META_KEY  => [
                '42' => ['some-key' => 'some-value']
            ]
        ];
        $adapter->method('getKey')->with('storage-key')->willReturnCallback(function () use ($data) {
            return $data;
        });
        $adapter->method('setKey')->with('storage-key')->willReturnCallback(function () use (&$data) {
            $data = func_get_arg(1);
        });

        $list = new NoteListStorage($dispatcher, $metaModel, $adapter, 'storage-key', []);

        $item = $this->getMockForAbstractClass(IItem::class);
        $item->method('get')->with('id')->willReturn('42');

        $this->assertSame(['some-key' => 'some-value'], $list->getMetaDataFor($item));

        $list->updateMetaDataFor($item, ['another-key' => 'another-value']);
        $this->assertSame(['another-key' => 'another-value'], $data[NoteListStorage::META_KEY]['42']);
    }
}
