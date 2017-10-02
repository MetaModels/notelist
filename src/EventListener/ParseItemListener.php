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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2017 The MetaModels team.
 * @license    https://github.com/MetaModels/notelist/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

declare(strict_types = 1);

namespace MetaModels\NoteList\EventListener;

use Contao\Environment;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilder;
use MetaModels\Events\ParseItemEvent;
use MetaModels\Events\RenderItemListEvent;
use MetaModels\FrontendIntegration\HybridList;
use MetaModels\IItem;
use MetaModels\IMetaModel;
use MetaModels\NoteList\Event\NoteListEvents;
use MetaModels\NoteList\Event\ParseNoteListFormEvent;
use MetaModels\NoteList\Event\ProcessActionEvent;
use MetaModels\NoteList\Form\FormRenderer;
use MetaModels\NoteList\NoteListFactory;
use MetaModels\NoteList\Storage\NoteListStorage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This class adds notelist actions in MetaModel frontend lists.
 */
class ParseItemListener
{
    /**
     * Key to use in the render setting.
     */
    const NOTELIST_LIST = '$note-lists';

    /**
     * The note list factory.
     *
     * @var NoteListFactory
     */
    private $factory;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Create a new instance.
     *
     * @param NoteListFactory          $factory    The factory.
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     */
    public function __construct(NoteListFactory $factory, EventDispatcherInterface $dispatcher)
    {
        $this->factory    = $factory;
        $this->dispatcher = $dispatcher;
    }

    /**
     * This updates the render setting with the selected note list instances.
     *
     * @param RenderItemListEvent $event The event to process.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function handleListRendering(RenderItemListEvent $event)
    {
        $caller = $event->getCaller();
        if (!($caller instanceof HybridList)) {
            return;
        }

        if (!(bool) $caller->metamodel_add_notelist) {
            return;
        }

        $lists = !empty($tmp = $caller->metamodel_notelist) ? unserialize($tmp) : [];

        if (!$this->processActions($event->getList()->getMetaModel(), $lists)) {
            $event->getList()->getView()->set(self::NOTELIST_LIST, $lists);
        }
    }

    /**
     * This updates the render setting with the selected note list instances.
     *
     * @param ParseNoteListFormEvent $event The event to process.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function handleFormRendering(ParseNoteListFormEvent $event)
    {
        $renderSetting = $event->getRenderSetting();

        $lists = [$event->getNoteListId()];

        if (!$this->processActions($event->getMetaModel(), $lists)) {
            $renderSetting->set(self::NOTELIST_LIST, $lists);
        }
    }

    /**
     * Add the notelist action buttons.
     *
     * @param ParseItemEvent $event Parse the passed item.
     *
     * @return void
     */
    public function addNoteListActions(ParseItemEvent $event)
    {
        $settings = $event->getRenderSettings();
        if (!($lists = $settings->get(self::NOTELIST_LIST))) {
            return;
        }

        $parsed = $event->getResult();
        $item   = $event->getItem();
        $model  = $item->getMetaModel();

        foreach ($lists as $list) {
            $storage = $this->factory->getList($model, $list);
            if (!$storage->accepts($item)) {
                continue;
            }
            $parsed['actions']['notelist_' . $list] = $this->generateButton($item, $storage);
        }

        $event->setResult($parsed);
    }

    /**
     * Test for actions and if present, process them.
     *
     * @param IMetaModel $metaModel The MetaModel instance.
     * @param string[]   $lists     The identifier list.
     *
     * @return bool
     *
     * @throws \InvalidArgumentException When the item could not be found or the action is unknown.
     */
    private function processActions(IMetaModel $metaModel, array $lists)
    {
        $url = $this->getCurrentUrl();
        foreach ($lists as $list) {
            // FIXME: need to add form POST handling here.
            // FIXME: we need a way to assemble payload array.
            if ($url->hasQueryParameter('notelist_' . $list . '_action')) {
                $action   = $url->getQueryParameter('notelist_' . $list . '_action');
                $noteList = $this->factory->getList($metaModel, $list);
                $payload  = [
                    'item' => $url->getQueryParameter('notelist_' . $list . '_item')
                ];

                $event = new ProcessActionEvent($action, $payload, $noteList, $metaModel);
                $this->dispatcher->dispatch(NoteListEvents::PROCESS_NOTE_LIST_ACTION, $event);
                if ($event->isSuccess()) {
                    $this->redirect($list);
                    return true;
                }

                throw new \InvalidArgumentException('Failed to process action ' . $action . ' for list ' . $list);
            }
        }

        return false;
    }

    /**
     * Build the action array.
     *
     * @param IItem           $item    The item to generate the button for.
     * @param NoteListStorage $storage The storage to use.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function generateButton(IItem $item, NoteListStorage $storage)
    {
        $action = !$storage->has($item) ? 'add' : 'remove';
        $url    = $this
            ->getCurrentUrl()
            ->setQueryParameter('notelist_' . $storage->getStorageKey() . '_action', $action)
            ->setQueryParameter('notelist_' . $storage->getStorageKey() . '_item', $item->get('id'));

        // Obtain list and generate button for it.
        return [
            'label' => sprintf($GLOBALS['TL_LANG']['MSC']['metamodel_notelist_' . $action], $storage->getName()),
            'href'  => $url->getUrl(),
            'class' => $action,
        ];
    }

    /**
     * Strip our parameters and redirect.
     *
     * @param string $identifier The identifier in the parameters.
     *
     * @return void
     */
    private function redirect(string $identifier)
    {
        $url = $this
            ->getCurrentUrl()
            ->unsetQueryParameter('notelist_' . $identifier . '_action')
            ->unsetQueryParameter('notelist_' . $identifier . '_item')
            ->getUrl();

        $this->dispatcher->dispatch(ContaoEvents::CONTROLLER_REDIRECT, new RedirectEvent($url));
    }

    /**
     * Retrieve an URL builder containing the current URL.
     *
     * @return UrlBuilder
     *
     * @internal
     */
    protected function getCurrentUrl()
    {
        return new UrlBuilder(Environment::getInstance()->get('requestUri'));
    }
}
