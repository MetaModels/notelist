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
use MetaModels\NoteList\Storage\AdapterInterface;
use MetaModels\NoteList\Storage\NoteListStorage;
use PHPUnit\Framework\TestCase;

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
        $metaModel = $this->getMockForAbstractClass(IMetaModel::class);
        $adapter   = $this->getMockForAbstractClass(AdapterInterface::class);

        $list = new NoteListStorage($metaModel, $adapter, 'storage-key', []);

        $this->assertInstanceOf('MetaModels\NoteList\Storage\NoteListStorage', $list);
    }

    /**
     * Test that adding of items is correctly mapped to the adapter.
     *
     * @return void
     */
    public function testAddingOfItemAdds()
    {
        $metaModel = $this->getMockForAbstractClass(IMetaModel::class);
        $adapter   = $this->getMockForAbstractClass(AdapterInterface::class);
        $item      = $this->getMockForAbstractClass(IItem::class);

        $adapter->expects($this->once())->method('getKey')->with('storage-key')->willReturn(['23']);
        $adapter->expects($this->once())->method('setKey')->with('storage-key', ['23', '42']);
        $item->expects($this->once())->method('get')->with('id')->willReturn('42');

        $list = new NoteListStorage($metaModel, $adapter, 'storage-key', []);
        $list->add($item);
    }

    /**
     * Test that removal of items is correctly mapped to the adapter.
     *
     * @return void
     */
    public function testRemovalOfItemRemoves()
    {
        $metaModel = $this->getMockForAbstractClass(IMetaModel::class);
        $adapter   = $this->getMockForAbstractClass(AdapterInterface::class);
        $item      = $this->getMockForAbstractClass(IItem::class);

        $adapter->expects($this->once())->method('getKey')->with('storage-key')->willReturn(['23', '42']);
        $adapter->expects($this->once())->method('setKey')->with('storage-key', ['23']);
        $item->expects($this->once())->method('get')->with('id')->willReturn('42');

        $list = new NoteListStorage($metaModel, $adapter, 'storage-key', []);
        $list->remove($item);
    }

    /**
     * Test that clearing of the list is correctly mapped to the adapter.
     *
     * @return void
     */
    public function testClearingWorks()
    {
        $metaModel = $this->getMockForAbstractClass(IMetaModel::class);
        $adapter   = $this->getMockForAbstractClass(AdapterInterface::class);

        $adapter->expects($this->once())->method('setKey')->with('storage-key', []);

        $list = new NoteListStorage($metaModel, $adapter, 'storage-key', []);
        $list->clear();
    }
}
