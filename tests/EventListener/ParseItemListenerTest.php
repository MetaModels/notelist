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
 * @copyright  2017-2025 The MetaModels team.
 * @license    https://github.com/MetaModels/notelist/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\NoteListBundle\Test\EventListener;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Framework\Adapter;
use Contao\TemplateLoader;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilder;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilderFactoryInterface;
use MetaModels\Events\RenderItemListEvent;
use MetaModels\Filter\FilterUrlBuilder;
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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * This tests the ParseItemListener class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ParseItemListenerTest extends TestCase
{
    /**
     * Test the parsing.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testHandlesListRendering()
    {
        $filterUrlBuilder = $this->getMockBuilder(FilterUrlBuilder::class)->disableOriginalConstructor()->getMock();

        $factory = $this
            ->getMockBuilder(NoteListFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConfiguredListsFor', 'getList', 'getName'])
            ->getMock();

        $metaModel     = $this->getMockForAbstractClass(IMetaModel::class);
        $renderSetting = $this->getMockForAbstractClass(ICollection::class);

        $renderSetting
            ->expects($this->once())
            ->method('set')
            ->with(ParseItemListener::NOTELIST_LIST, ['23', '42']);

        $list = $this
            ->getMockBuilder(ItemList::class)
            ->onlyMethods(['getMetaModel', 'getView'])
            ->setConstructorArgs([null, null, null, null, $filterUrlBuilder])
            ->getMock();
        $list
            ->expects($this->once())
            ->method('getMetaModel')
            ->willReturn($metaModel);
        $list
            ->expects($this->once())
            ->method('getView')
            ->willReturn($renderSetting);

        $noteList1 = $this
            ->getMockBuilder(NoteListStorage::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getMeta'])
            ->getMock();
        $noteList1->expects($this->once())->method('getMeta')->willReturn(new ValueBag([]));
        $noteList2 = $this
            ->getMockBuilder(NoteListStorage::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getMeta'])
            ->getMock();
        $noteList2->expects($this->once())->method('getMeta')->willReturn(new ValueBag([]));

        $factory
            ->expects($this->exactly(2))
            ->method('getList')
            ->willReturnCallback(
                function (
                    IMetaModel $aMetaModel,
                    string $noteListId
                ) use (
                    $metaModel,
                    $noteList1,
                    $noteList2
                ): NoteListStorage {
                    self::assertSame($metaModel, $aMetaModel);
                    switch ($noteListId) {
                        case '23':
                            return $noteList1;
                        case '42':
                            return $noteList2;
                        default:
                            self::fail('Unknown notelist id ' . $noteListId);
                    }
                }
            );

        $templateLoader    = $this->getMockBuilder(Adapter::class)->disableOriginalConstructor()->getMock();
        $scopeDeterminator =
            $this->getMockBuilder(RequestScopeDeterminator::class)->disableOriginalConstructor()->getMock();

        $template = new Template('', $templateLoader, $scopeDeterminator);
        $caller   = $this
            ->getMockBuilder(HybridList::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__get'])
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

        $urlBuilderFactory = $this->getMockForAbstractClass(UrlBuilderFactoryInterface::class);
        $requestStack      = $this
            ->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()
            ->getMock();

        $listener = $this
            ->getMockBuilder(ParseItemListener::class)
            ->setConstructorArgs([$factory, $dispatcher, $formBuilder, $urlBuilderFactory, $requestStack])
            ->onlyMethods(['getCurrentUrl'])
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
     */
    public function testSkipsHandlingOfListRenderingForUnknownCaller(): void
    {
        $listener = $this
            ->getMockBuilder(ParseItemListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $event = $this
            ->getMockBuilder(RenderItemListEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects(self::once())->method('getCaller')->willReturn(null);
        $event->expects(self::never())->method('getList');

        $listener->handleListRendering($event);
    }

    /**
     * Test the parsing.
     */
    public function testSkipsHandlingOfListRenderingForCallerWithoutNoteLists(): void
    {
        $listener = $this
            ->getMockBuilder(ParseItemListener::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $renderer = $this->getMockBuilder(HybridList::class)->disableOriginalConstructor()->getMock();

        $event = $this
            ->getMockBuilder(RenderItemListEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects(self::once())->method('getCaller')->willReturn($renderer);
        $event->expects(self::never())->method('getList');

        $listener->handleListRendering($event);
    }

    /**
     * Test the parsing.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testListRenderingProcessesActions()
    {
        $factory = $this
            ->getMockBuilder(NoteListFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConfiguredListsFor', 'getList', 'getName'])
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

        $templateLoader    = $this->getMockBuilder(Adapter::class)->disableOriginalConstructor()->getMock();
        $scopeDeterminator =
            $this->getMockBuilder(RequestScopeDeterminator::class)->disableOriginalConstructor()->getMock();

        $template = new Template('', $templateLoader, $scopeDeterminator);
        $caller   = $this
            ->getMockBuilder(HybridList::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__get'])
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
            ->expects($this->once())->method('dispatch')
            ->willReturnCallback(
                function ($event) {
                    $this->assertInstanceOf(ProcessActionEvent::class, $event);
                    /** @var ProcessActionEvent $event */
                    $this->assertSame('action-name', $event->getAction());
                    $event->setSuccess();
                    return $event;
                }
            );

        $formBuilder = $this->getMockBuilder(FormBuilder::class)->disableOriginalConstructor()->getMock();

        $urlBuilderFactory = $this->getMockForAbstractClass(UrlBuilderFactoryInterface::class);
        $requestStack      = $this
            ->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()
            ->getMock();

        $listener   = $this
            ->getMockBuilder(ParseItemListener::class)
            ->setConstructorArgs([$factory, $dispatcher, $formBuilder, $urlBuilderFactory, $requestStack])
            ->onlyMethods(['getCurrentUrl'])
            ->getMock();

        $filterUrlBuilder = $this->getMockBuilder(FilterUrlBuilder::class)->disableOriginalConstructor()->getMock();

        $itemList = $this
            ->getMockBuilder(ItemList::class)
            ->onlyMethods(['getMetaModel', 'getView'])
            ->setConstructorArgs([null, null, null, null, $filterUrlBuilder])
            ->getMock();
        $itemList
            ->expects($this->once())
            ->method('getMetaModel')
            ->willReturn($metaModel);

        $listener
            ->expects($this->exactly(2))
            ->method('getCurrentUrl')
            ->willReturn(new UrlBuilder('http://example.com/?notelist_23_action=action-name&notelist_23_item=15'));

        try {
            /** @var ParseItemListener $listener */
            $listener->handleListRendering(new RenderItemListEvent($itemList, $template, $caller));
        } catch (RedirectResponseException $exception) {
            $this->assertInstanceOf(RedirectResponse::class, $exception->getResponse());
            $this->assertSame('http://example.com/', $exception->getResponse()->getTargetUrl());
            return;
        }
        $this->fail('Exception not thrown.');
    }

    /**
     * Test the parsing.
     *
     * @return void
     */
    public function testHandleFormRendering()
    {
        $factory = $this
            ->getMockBuilder(NoteListFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConfiguredListsFor', 'getList', 'getName'])
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

        $urlBuilderFactory = $this->getMockForAbstractClass(UrlBuilderFactoryInterface::class);
        $requestStack      = $this
            ->getMockBuilder(RequestStack::class)
            ->disableOriginalConstructor()
            ->getMock();

        $listener   = $this
            ->getMockBuilder(ParseItemListener::class)
            ->setConstructorArgs([$factory, $dispatcher, $formBuilder, $urlBuilderFactory, $requestStack])
            ->onlyMethods(['getCurrentUrl'])
            ->getMock();

        $listener
            ->expects($this->once())
            ->method('getCurrentUrl')
            ->willReturn(new UrlBuilder('http://example.com/'));

        /** @var ParseItemListener $listener */
        $listener->handleFormRendering(new ParseNoteListFormEvent($metaModel, $renderSetting, '23'));
    }
}
