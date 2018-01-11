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
use MetaModels\NoteList\Form\FormBuilder;
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
     * Key to use for flag in the render setting for disabling form rendering.
     */
    const NOTELIST_LIST_DISABLE_FORM = '$note-lists-no-form';

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
     * The form builder.
     *
     * @var FormBuilder
     */
    private $formBuilder;

    /**
     * Create a new instance.
     *
     * @param NoteListFactory          $factory     The factory.
     * @param EventDispatcherInterface $dispatcher  The event dispatcher.
     * @param FormBuilder              $formBuilder The form builder.
     */
    public function __construct(
        NoteListFactory $factory,
        EventDispatcherInterface $dispatcher,
        FormBuilder $formBuilder
    ) {
        $this->factory     = $factory;
        $this->dispatcher  = $dispatcher;
        $this->formBuilder = $formBuilder;
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
     */
    public function handleFormRendering(ParseNoteListFormEvent $event)
    {
        $renderSetting = $event->getRenderSetting();

        $lists = [$event->getNoteListId()];

        if (!$this->processActions($event->getMetaModel(), $lists)) {
            $renderSetting->set(self::NOTELIST_LIST, $lists);
            $renderSetting->set(self::NOTELIST_LIST_DISABLE_FORM, true);
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

            $parsed['notelists']['notelist_' . $list] = $storage->getMetaDataFor($item);
            if ($formId = $storage->getMeta()->get('form')) {
                if (!$settings->get(self::NOTELIST_LIST_DISABLE_FORM)) {
                    // Need to render the form here.
                    $parsed['actions']['notelist_' . $list] = $this->generateForm($item, $storage, intval($formId));
                }
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
        foreach ($lists as $list) {
            if ($event = $this->buildActionEvent($metaModel, $list)) {
                $this->dispatcher->dispatch(NoteListEvents::PROCESS_NOTE_LIST_ACTION, $event);
                if ($event->isSuccess()) {
                    $this->redirect($list);
                    return true;
                }

                throw new \InvalidArgumentException(
                    'Failed to process action ' . $event->getAction() . ' for list ' . $list
                );
            }
        }

        return false;
    }

    /**
     * Try to obtain payload parameters for action event.
     *
     * @param IMetaModel $metaModel The MetaModel.
     * @param string     $list      The note list id.
     *
     * @return ProcessActionEvent|null
     */
    private function buildActionEvent(IMetaModel $metaModel, string $list)
    {
        $url = $this->getCurrentUrl();
        if ($url->hasQueryParameter('notelist_' . $list . '_action')) {
            return new ProcessActionEvent(
                $url->getQueryParameter('notelist_' . $list . '_action'),
                ['item' => $url->getQueryParameter('notelist_' . $list . '_item')],
                $this->factory->getList($metaModel, $list),
                $metaModel
            );
        }
        if (!($noteList = $this->factory->getList($metaModel, $list))) {
            return null;
        }
        $valueBag = $noteList->getMeta();
        if ($valueBag->has('form') && ($formId = $valueBag->get('form'))) {
            $form = $this->formBuilder->getForm(intval($formId), $noteList, $this->getCurrentUrl()->getUrl());
            if ($data = $form->getSubmittedData()) {
                return new ProcessActionEvent(
                    'add',
                    $data,
                    $noteList,
                    $metaModel
                );
            }
        }
        return null;
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
            'name'  => $storage->getName(),
            'label' => sprintf($GLOBALS['TL_LANG']['MSC']['metamodel_notelist_' . $action], $storage->getName()),
            'href'  => $url->getUrl(),
            'class' => $action,
            'meta'  => $storage->getMetaDataFor($item),
        ];
    }

    /**
     * Generate a form for the passed item.
     *
     * @param IItem           $item    The item to generate the button for.
     * @param NoteListStorage $storage The storage to use.
     * @param int             $formId  The form id.
     *
     * @return array
     */
    private function generateForm(IItem $item, NoteListStorage $storage, int $formId)
    {
        $form = $this->formBuilder->getForm($formId, $storage, $this->getCurrentUrl()->getUrl());

        return ['html' => $form->render($item)];
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
