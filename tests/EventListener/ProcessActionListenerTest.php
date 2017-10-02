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

namespace MetaModels\NoteList\Test\EventListener;

use MetaModels\IItem;
use MetaModels\IMetaModel;
use MetaModels\NoteList\Event\ProcessActionEvent;
use MetaModels\NoteList\EventListener\ProcessActionListener;
use MetaModels\NoteList\Storage\NoteListStorage;
use MetaModels\NoteList\Test\TestCase;

/**
 * This tests the ProcessActionListener class.
 *
 * @covers \MetaModels\NoteList\EventListener\ProcessActionListener
 */
class ProcessActionListenerTest extends TestCase
{
    /**
     * Test handling of add action.
     *
     * @return void
     */
    public function testHandlesAddAction()
    {
        $metaModel = $this->getMockForAbstractClass(IMetaModel::class);
        $item      = $this->getMockForAbstractClass(IItem::class);

        $list = $this
            ->getMockBuilder(NoteListStorage::class)
            ->disableOriginalConstructor()
            ->setMethods(['add'])
            ->getMock();
        $list->expects($this->once())->method('add')->with($item);

        $metaModel
            ->expects($this->once())
            ->method('findById')
            ->with(15)
            ->willReturn($item = $this->getMockForAbstractClass(IItem::class));

        $listener = new ProcessActionListener();
        $event    = new ProcessActionEvent('add', ['item' => 15], $list, $metaModel);

        $listener->handleEvent($event);
        $this->assertTrue($event->isSuccess());
    }

    /**
     * Test handling of remove action.
     *
     * @return void
     */
    public function testHandlesRemoveAction()
    {
        $metaModel = $this->getMockForAbstractClass(IMetaModel::class);
        $item      = $this->getMockForAbstractClass(IItem::class);

        $list = $this
            ->getMockBuilder(NoteListStorage::class)
            ->disableOriginalConstructor()
            ->setMethods(['remove'])
            ->getMock();
        $list->expects($this->once())->method('remove')->with($item);

        $metaModel
            ->expects($this->once())
            ->method('findById')
            ->with(15)
            ->willReturn($item = $this->getMockForAbstractClass(IItem::class));

        $listener = new ProcessActionListener();
        $event    = new ProcessActionEvent('remove', ['item' => 15], $list, $metaModel);

        $listener->handleEvent($event);
        $this->assertTrue($event->isSuccess());
    }

    /**
     * Test handling of clear action.
     *
     * @return void
     */
    public function testHandlesClearAction()
    {
        $metaModel = $this->getMockForAbstractClass(IMetaModel::class);

        $list = $this
            ->getMockBuilder(NoteListStorage::class)
            ->disableOriginalConstructor()
            ->setMethods(['clear'])
            ->getMock();
        $list->expects($this->once())->method('clear');

        $listener = new ProcessActionListener();
        $event    = new ProcessActionEvent('clear', [], $list, $metaModel);

        $listener->handleEvent($event);
        $this->assertTrue($event->isSuccess());
    }

    /**
     * Test skipping of unknown actions.
     *
     * @return void
     */
    public function testDoesNotHandleUnknownAction()
    {
        $metaModel = $this->getMockForAbstractClass(IMetaModel::class);
        $list      = $this->getMockBuilder(NoteListStorage::class)->disableOriginalConstructor()->getMock();
        $listener  = new ProcessActionListener();
        $event     = $this
            ->getMockBuilder(ProcessActionEvent::class)
            ->setConstructorArgs(['unknown-action', [], $list, $metaModel])
            ->setMethods(['getNoteList'])
            ->getMock();
        $event->expects($this->never())->method('getNoteList');
        /** @var ProcessActionListener $listener */
        $listener->handleEvent($event);
        $this->assertFalse($event->isSuccess());
    }

    /**
     * Test invalid item handling.
     *
     * @return void
     */
    public function testThrowsExceptionForNonExistentItems()
    {
        $metaModel = $this->getMockForAbstractClass(IMetaModel::class);

        $list = $this
            ->getMockBuilder(NoteListStorage::class)
            ->disableOriginalConstructor()
            ->setMethods(['remove'])
            ->getMock();
        $list->expects($this->never())->method('remove');

        $metaModel
            ->expects($this->once())
            ->method('findById')
            ->with(15)
            ->willReturn(null);

        $listener = new ProcessActionListener();
        $event    = new ProcessActionEvent('remove', ['item' => 15], $list, $metaModel);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Item 15 could not be found.');

        $listener->handleEvent($event);
    }
}
