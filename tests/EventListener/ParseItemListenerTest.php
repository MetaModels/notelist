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

namespace MetaModels\NoteListBundle\Test\EventListener;

use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilder;
use MetaModels\Events\RenderItemListEvent;
use MetaModels\FrontendIntegration\HybridList;
use MetaModels\IMetaModel;
use MetaModels\ItemList;
use MetaModels\NoteListBundle\Event\ParseNoteListFormEvent;
use MetaModels\NoteListBundle\Event\ProcessActionEvent;
use MetaModels\NoteListBundle\EventListener\ParseItemListener;
use MetaModels\NoteListBundle\Form\FormBuilder;
use MetaModels\NoteListBundle\NoteListFactory;
use MetaModels\NoteListBundle\Storage\NoteListStorage;
use MetaModels\NoteListBundle\Storage\ValueBag;
use MetaModels\NoteListBundle\Test\TestCase;
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
    public function testHandlesListRendering()
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

        $noteList = $this
            ->getMockBuilder(NoteListStorage::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMeta'])
            ->getMock();
        $noteList->expects($this->once())->method('getMeta')->willReturn(new ValueBag([]));

        $factory
            ->expects($this->exactly(2))
            ->method('getList')
            ->withConsecutive([$metaModel, '23'], [$metaModel, '42'])
            ->willReturnOnConsecutiveCalls($noteList, null);

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

        $formBuilder = $this->getMockBuilder(FormBuilder::class)->disableOriginalConstructor()->getMock();

        $dispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);
        $listener   = $this
            ->getMockBuilder(ParseItemListener::class)
            ->setConstructorArgs([$factory, $dispatcher, $formBuilder])
            ->setMethods(['getCurrentUrl'])
            ->getMock();

        $listener
            ->expects($this->any())
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
    public function testSkipsHandlingOfListRenderingForUnknownCaller()
    {
        $listener = $this
            ->getMockBuilder(ParseItemListener::class)
            ->disableOriginalConstructor()
            ->setMethods(['processActions'])
            ->getMock();
        $listener->expects($this->never())->method('processActions');

        $event = new RenderItemListEvent(new ItemList(), new Template(), null);

        $listener->handleListRendering($event);
    }

    /**
     * Test the parsing.
     *
     * @return void
     */
    public function testSkipsHandlingOfListRenderingForCallerWithoutNoteLists()
    {
        $listener = $this
            ->getMockBuilder(ParseItemListener::class)
            ->disableOriginalConstructor()
            ->setMethods(['processActions'])
            ->getMock();
        $listener->expects($this->never())->method('processActions');

        $renderer = $this->getMockBuilder(HybridList::class)->disableOriginalConstructor()->getMock();

        $event = new RenderItemListEvent(new ItemList(), new Template(), $renderer);

        $listener->handleListRendering($event);
    }

    /**
     * Test the parsing.
     *
     * @return void
     */
    public function testListRenderingProcessesActions()
    {
        $factory = $this
            ->getMockBuilder(NoteListFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfiguredListsFor', 'getList', 'getName'])
            ->getMock();

        $metaModel = $this->getMockForAbstractClass(IMetaModel::class);

        $list = $this
            ->getMockBuilder(NoteListStorage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factory
            ->expects($this->once())
            ->method('getList')
            ->with($metaModel, '23')
            ->willReturn($list);

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

        ;

        $dispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);

        $dispatcher
            ->expects($this->exactly(2))->method('dispatch')
            ->willReturnOnConsecutiveCalls(
                $this->returnCallback(function ($name, $event) {
                    $this->assertInstanceOf(ProcessActionEvent::class, $event);
                    /** @var ProcessActionEvent $event */
                    $this->assertSame('action-name', $event->getAction());
                    $event->setSuccess();
                }),
                $this->returnCallback(function ($name, $event) {
                    $this->assertInstanceOf(RedirectEvent::class, $event);
                    /** @var RedirectEvent $event */
                    $this->assertSame('http://example.com/', $event->getNewLocation());
                })
            );

        $formBuilder = $this->getMockBuilder(FormBuilder::class)->disableOriginalConstructor()->getMock();

        $listener = $this
            ->getMockBuilder(ParseItemListener::class)
            ->setConstructorArgs([$factory, $dispatcher, $formBuilder])
            ->setMethods(['getCurrentUrl'])
            ->getMock();

        $itemList = $this
            ->getMockBuilder(ItemList::class)
            ->setMethods(['getMetaModel', 'getView'])
            ->getMock();
        $itemList
            ->expects($this->once())
            ->method('getMetaModel')
            ->willReturn($metaModel);

        $listener
            ->expects($this->exactly(2))
            ->method('getCurrentUrl')
            ->willReturn(new UrlBuilder('http://example.com/?notelist_23_action=action-name&notelist_23_item=15'));

        /** @var ParseItemListener $listener */
        $listener->handleListRendering(new RenderItemListEvent($itemList, $template, $caller));
    }

    /**
     * Test the parsing.
     *
     * @return void
     */
    public function testHandleFormRendering()
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
            ->expects($this->exactly(2))
            ->method('set')
            ->withConsecutive(
                [ParseItemListener::NOTELIST_LIST, ['23']],
                [ParseItemListener::NOTELIST_LIST_DISABLE_FORM, true]
            );

        $formBuilder = $this->getMockBuilder(FormBuilder::class)->disableOriginalConstructor()->getMock();

        $dispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);
        $listener   = $this
            ->getMockBuilder(ParseItemListener::class)
            ->setConstructorArgs([$factory, $dispatcher, $formBuilder])
            ->setMethods(['getCurrentUrl'])
            ->getMock();

        $listener
            ->expects($this->once())
            ->method('getCurrentUrl')
            ->willReturn(new UrlBuilder('http://example.com/'));

        /** @var ParseItemListener $listener */
        $listener->handleFormRendering(new ParseNoteListFormEvent($metaModel, $renderSetting, '23'));
    }
}
