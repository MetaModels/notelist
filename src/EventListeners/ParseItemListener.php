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

namespace MetaModels\NoteList\EventListeners;

use Contao\Environment;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilder;
use MetaModels\Events\ParseItemEvent;
use MetaModels\Events\RenderItemListEvent;
use MetaModels\FrontendIntegration\HybridList;
use MetaModels\IItem;
use MetaModels\IMetaModel;
use MetaModels\NoteList\Event\ParseNoteListFormEvent;
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
    public function handleFrontendEditingInListRendering(RenderItemListEvent $event)
    {
        $caller = $event->getCaller();
        if (!(($caller instanceof HybridList) || ($caller instanceof FormRenderer))) {
            return;
        }

        if (!(bool) $caller->metamodel_add_notelist) {
            return;
        }

        $lists = !empty($caller->metamodel_notelist) ? unserialize($caller->metamodel_notelist) : [];

        $this->processActions($event->getList()->getMetaModel(), $lists);

        $event->getList()->getView()->set(self::NOTELIST_LIST, $lists);
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
    public function handleFrontendEditingInFormRendering(ParseNoteListFormEvent $event)
    {
        $renderSetting = $event->getRenderSetting();

        $lists = [$event->getNoteListId()];

        $this->processActions($event->getMetaModel(), $lists);

        $renderSetting->set(self::NOTELIST_LIST, $lists);
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
     * @return void
     *
     * @throws \InvalidArgumentException When the item could not be found or the action is unknown.
     */
    private function processActions(IMetaModel $metaModel, $lists)
    {
        $url = $this->getCurrentUrl();
        foreach ($lists as $list) {
            if ($url->hasQueryParameter('notelist_' . $list . '_action')) {
                $action   = $url->getQueryParameter('notelist_' . $list . '_action');
                $item     = $this->getItemFromMetaModel(
                    $metaModel,
                    $url->getQueryParameter('notelist_' . $list . '_item')
                );
                $noteList = $this->factory->getList($metaModel, $list);
                switch ($action) {
                    case 'add':
                        $noteList->add($item);
                        $this->redirect($list);
                        break;
                    case 'remove':
                        $noteList->remove($item);
                        $this->redirect($list);
                        break;
                    case 'clear':
                        $noteList->clear();
                        break;
                    default:
                }
                throw new \InvalidArgumentException('Unknown action name ' . $action);
            }
        }
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
     * Retrieve an item from the MetaModel.
     *
     * @param IMetaModel $metaModel The MetaModel instance to retrieve the item from.
     * @param string     $itemId    The item id to retrieve.
     *
     * @return IItem
     *
     * @throws \InvalidArgumentException When the item could not be found.
     */
    private function getItemFromMetaModel(IMetaModel $metaModel, $itemId)
    {
        $item = $metaModel->findById($itemId);
        if (null === $item) {
            throw new \InvalidArgumentException('Item ' . $itemId . ' could not be found.');
        }

        return $item;
    }

    /**
     * Retrieve an URL builder containing the current URL.
     *
     * @return UrlBuilder
     */
    private function getCurrentUrl()
    {
        return new UrlBuilder(Environment::getInstance()->get('requestUri'));
    }
}
