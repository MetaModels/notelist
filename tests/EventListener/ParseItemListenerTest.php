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

use ContaoCommunityAlliance\UrlBuilder\UrlBuilder;
use MetaModels\Events\RenderItemListEvent;
use MetaModels\FrontendIntegration\HybridList;
use MetaModels\IItem;
use MetaModels\IMetaModel;
use MetaModels\ItemList;
use MetaModels\NoteList\Event\ParseNoteListFormEvent;
use MetaModels\NoteList\EventListener\ParseItemListener;
use MetaModels\NoteList\NoteListFactory;
use MetaModels\NoteList\Storage\NoteListStorage;
use MetaModels\NoteList\Test\TestCase;
use MetaModels\Render\Setting\ICollection;
use MetaModels\Render\Template;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This tests the ParseItemListener class.
 */
class ParseItemListenerTest extends TestCase
{
    /**
     * Test the parsing.
     *
     * @return void
     */
    public function testHandleListRendering()
    {
        $this->preloadContaoClasses(['System', 'Controller', 'Frontend', ]);

        $factory = $this
            ->getMockBuilder(NoteListFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfiguredListsFor', 'getList', 'getName'])
            ->getMock();

        $metaModel     = $this->getMockForAbstractClass(IMetaModel::class);
        $renderSetting = $this->getMockForAbstractClass(ICollection::class);

        $renderSetting
            ->expects($this->once())
            ->method('set')
            ->with(ParseItemListener::NOTELIST_LIST, ['23', '42']);

        $list = $this
            ->getMockBuilder(ItemList::class)
            ->setMethods(['getMetaModel', 'getView'])
            ->getMock();
        $list
            ->expects($this->once())
            ->method('getMetaModel')
            ->willReturn($metaModel);
        $list
            ->expects($this->once())
            ->method('getView')
            ->willReturn($renderSetting);

        $template = new Template();
        $caller   = $this
            ->getMockBuilder(HybridList::class)
            ->disableOriginalConstructor()
            ->setMethods(['__get'])
            ->getMock();

        $caller
            ->expects($this->exactly(2))
            ->method('__get')
            ->withConsecutive(
                ['metamodel_add_notelist'],
                ['metamodel_notelist']
            )
            ->willReturnOnConsecutiveCalls(
                1,
                serialize(['23', '42'])
            );

        $dispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);
        $listener   = $this
            ->getMockBuilder(ParseItemListener::class)
            ->setConstructorArgs([$factory, $dispatcher])
            ->setMethods(['getCurrentUrl'])
            ->getMock();

        $listener
            ->expects($this->once())
            ->method('getCurrentUrl')
            ->willReturn(new UrlBuilder('http://example.com/'));


        /** @var ParseItemListener $listener */
        $listener->handleListRendering(new RenderItemListEvent($list, $template, $caller));
    }

    /**
     * Test the parsing.
     *
     * @return void
     */
    public function testHandleListRenderingAddAction()
    {
        $this->preloadContaoClasses(['System', 'Controller', 'Frontend', ]);

        $factory = $this
            ->getMockBuilder(NoteListFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfiguredListsFor', 'getList', 'getName'])
            ->getMock();

        $metaModel = $this->getMockForAbstractClass(IMetaModel::class);
        $item      = $this->getMockForAbstractClass(IItem::class);

        $list = $this
            ->getMockBuilder(NoteListStorage::class)
            ->disableOriginalConstructor()
            ->setMethods(['add'])
            ->getMock();
        $list->expects($this->once())->method('add')->with($item);

        $factory
            ->expects($this->once())
            ->method('getList')
            ->with($metaModel, '23')
            ->willReturn($list);

        $renderSetting = $this->getMockForAbstractClass(ICollection::class);

        $metaModel
            ->expects($this->once())
            ->method('findById')
            ->with(15)
            ->willReturn($item = $this->getMockForAbstractClass(IItem::class));

        $renderSetting
            ->expects($this->once())
            ->method('set')
            ->with(ParseItemListener::NOTELIST_LIST, ['23']);

        $list = $this
            ->getMockBuilder(ItemList::class)
            ->setMethods(['getMetaModel', 'getView'])
            ->getMock();
        $list
            ->expects($this->once())
            ->method('getMetaModel')
            ->willReturn($metaModel);
        $list
            ->expects($this->once())
            ->method('getView')
            ->willReturn($renderSetting);

        $template = new Template();
        $caller   = $this
            ->getMockBuilder(HybridList::class)
            ->disableOriginalConstructor()
            ->setMethods(['__get'])
            ->getMock();

        $caller
            ->expects($this->exactly(2))
            ->method('__get')
            ->withConsecutive(
                ['metamodel_add_notelist'],
                ['metamodel_notelist']
            )
            ->willReturnOnConsecutiveCalls(
                1,
                serialize(['23'])
            );

        $dispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);
        $listener   = $this
            ->getMockBuilder(ParseItemListener::class)
            ->setConstructorArgs([$factory, $dispatcher])
            ->setMethods(['getCurrentUrl'])
            ->getMock();

        $listener
            ->expects($this->exactly(2))
            ->method('getCurrentUrl')
            ->willReturn(new UrlBuilder('http://example.com/?notelist_23_action=add&notelist_23_item=15'));

        /** @var ParseItemListener $listener */
        $listener->handleListRendering(new RenderItemListEvent($list, $template, $caller));
    }

    /**
     * Test the parsing.
     *
     * @return void
     */
    public function testHandleListRenderingRemoveAction()
    {
        $this->preloadContaoClasses(['System', 'Controller', 'Frontend', ]);

        $factory = $this
            ->getMockBuilder(NoteListFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfiguredListsFor', 'getList', 'getName'])
            ->getMock();

        $metaModel = $this->getMockForAbstractClass(IMetaModel::class);
        $item      = $this->getMockForAbstractClass(IItem::class);

        $list = $this
            ->getMockBuilder(NoteListStorage::class)
            ->disableOriginalConstructor()
            ->setMethods(['remove'])
            ->getMock();
        $list->expects($this->once())->method('remove')->with($item);

        $factory
            ->expects($this->once())
            ->method('getList')
            ->with($metaModel, '23')
            ->willReturn($list);

        $renderSetting = $this->getMockForAbstractClass(ICollection::class);

        $metaModel
            ->expects($this->once())
            ->method('findById')
            ->with(15)
            ->willReturn($item = $this->getMockForAbstractClass(IItem::class));

        $renderSetting
            ->expects($this->once())
            ->method('set')
            ->with(ParseItemListener::NOTELIST_LIST, ['23']);

        $list = $this
            ->getMockBuilder(ItemList::class)
            ->setMethods(['getMetaModel', 'getView'])
            ->getMock();
        $list
            ->expects($this->once())
            ->method('getMetaModel')
            ->willReturn($metaModel);
        $list
            ->expects($this->once())
            ->method('getView')
            ->willReturn($renderSetting);

        $template = new Template();
        $caller   = $this
            ->getMockBuilder(HybridList::class)
            ->disableOriginalConstructor()
            ->setMethods(['__get'])
            ->getMock();

        $caller
            ->expects($this->exactly(2))
            ->method('__get')
            ->withConsecutive(
                ['metamodel_add_notelist'],
                ['metamodel_notelist']
            )
            ->willReturnOnConsecutiveCalls(
                1,
                serialize(['23'])
            );

        $dispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);
        $listener   = $this
            ->getMockBuilder(ParseItemListener::class)
            ->setConstructorArgs([$factory, $dispatcher])
            ->setMethods(['getCurrentUrl'])
            ->getMock();

        $listener
            ->expects($this->exactly(2))
            ->method('getCurrentUrl')
            ->willReturn(new UrlBuilder('http://example.com/?notelist_23_action=remove&notelist_23_item=15'));

        /** @var ParseItemListener $listener */
        $listener->handleListRendering(new RenderItemListEvent($list, $template, $caller));
    }

    /**
     * Test the parsing.
     *
     * @return void
     */
    public function testHandleListRenderingClearAction()
    {
        $this->preloadContaoClasses(['System', 'Controller', 'Frontend', ]);

        $factory = $this
            ->getMockBuilder(NoteListFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfiguredListsFor', 'getList', 'getName'])
            ->getMock();

        $metaModel = $this->getMockForAbstractClass(IMetaModel::class);

        $list = $this
            ->getMockBuilder(NoteListStorage::class)
            ->disableOriginalConstructor()
            ->setMethods(['clear'])
            ->getMock();
        $list->expects($this->once())->method('clear');

        $factory
            ->expects($this->once())
            ->method('getList')
            ->with($metaModel, '23')
            ->willReturn($list);

        $renderSetting = $this->getMockForAbstractClass(ICollection::class);

        $renderSetting
            ->expects($this->once())
            ->method('set')
            ->with(ParseItemListener::NOTELIST_LIST, ['23']);

        $list = $this
            ->getMockBuilder(ItemList::class)
            ->setMethods(['getMetaModel', 'getView'])
            ->getMock();
        $list
            ->expects($this->once())
            ->method('getMetaModel')
            ->willReturn($metaModel);
        $list
            ->expects($this->once())
            ->method('getView')
            ->willReturn($renderSetting);

        $template = new Template();
        $caller   = $this
            ->getMockBuilder(HybridList::class)
            ->disableOriginalConstructor()
            ->setMethods(['__get'])
            ->getMock();

        $caller
            ->expects($this->exactly(2))
            ->method('__get')
            ->withConsecutive(
                ['metamodel_add_notelist'],
                ['metamodel_notelist']
            )
            ->willReturnOnConsecutiveCalls(
                1,
                serialize(['23'])
            );

        $dispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);
        $listener   = $this
            ->getMockBuilder(ParseItemListener::class)
            ->setConstructorArgs([$factory, $dispatcher])
            ->setMethods(['getCurrentUrl'])
            ->getMock();

        $listener
            ->expects($this->exactly(2))
            ->method('getCurrentUrl')
            ->willReturn(new UrlBuilder('http://example.com/?notelist_23_action=clear'));

        /** @var ParseItemListener $listener */
        $listener->handleListRendering(new RenderItemListEvent($list, $template, $caller));
    }

    /**
     * Test the parsing.
     *
     * @return void
     */
    public function testHandleFormRendering()
    {
        $this->markTestIncomplete();

        $factory = $this
            ->getMockBuilder(NoteListFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfiguredListsFor', 'getList', 'getName'])
            ->getMock();

        $dispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);

        $listener = new ParseItemListener($factory, $dispatcher);

        $metaModel = $this->getMockForAbstractClass(IMetaModel::class);
        $renderSetting = $this->getMockForAbstractClass(ICollection::class);
        $notelistId = '23';

        $listener->handleFormRendering(new ParseNoteListFormEvent($metaModel, $renderSetting, $notelistId));
    }
}
