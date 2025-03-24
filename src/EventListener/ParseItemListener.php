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

namespace MetaModels\NoteListBundle\EventListener;

use ContaoCommunityAlliance\UrlBuilder\UrlBuilder;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilderFactoryInterface;
use Contao\ContentModel;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\FormFieldModel;
use Contao\Model\Collection;
use Contao\ModuleModel;
use Contao\System;
use MetaModels\Events\ParseItemEvent;
use MetaModels\Events\RenderItemListEvent;
use MetaModels\FrontendIntegration\HybridList;
use MetaModels\IItem;
use MetaModels\IMetaModel;
use MetaModels\NoteListBundle\Event\NoteListEvents;
use MetaModels\NoteListBundle\Event\ParseNoteListFormEvent;
use MetaModels\NoteListBundle\Event\ProcessActionEvent;
use MetaModels\NoteListBundle\Form\FormBuilder;
use MetaModels\NoteListBundle\NoteListFactory;
use MetaModels\NoteListBundle\Storage\NoteListStorage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This class adds notelist actions in MetaModel frontend lists.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ParseItemListener
{
    /**
     * Key to use in the render setting.
     */
    public const NOTELIST_LIST = '$note-lists';

    /**
     * Key to use for flag in the render setting for disabling form rendering.
     */
    public const NOTELIST_LIST_DISABLE_FORM = '$note-lists-no-form';

    /**
     * The note list factory.
     *
     * @var NoteListFactory
     */
    private NoteListFactory $factory;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $dispatcher;

    /**
     * The form builder.
     *
     * @var FormBuilder
     */
    private FormBuilder $formBuilder;

    /**
     * The url builder factory.
     *
     * @var UrlBuilderFactoryInterface
     */
    private UrlBuilderFactoryInterface $urlBuilderFactory;

    /**
     * The request stack.
     *
     * @var RequestStack
     */
    private RequestStack $requestStack;

    /**
     * Create a new instance.
     *
     * @param NoteListFactory            $factory           The factory.
     * @param EventDispatcherInterface   $dispatcher        The event dispatcher.
     * @param FormBuilder                $formBuilder       The form builder.
     * @param UrlBuilderFactoryInterface $urlBuilderFactory The url builder factory.
     * @param RequestStack               $requestStack      The request stack.
     */
    public function __construct(
        NoteListFactory $factory,
        EventDispatcherInterface $dispatcher,
        FormBuilder $formBuilder,
        UrlBuilderFactoryInterface $urlBuilderFactory,
        RequestStack $requestStack
    ) {
        $this->factory           = $factory;
        $this->dispatcher        = $dispatcher;
        $this->formBuilder       = $formBuilder;
        $this->urlBuilderFactory = $urlBuilderFactory;
        $this->requestStack      = $requestStack;
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
     *
     * @psalm-suppress UndefinedMagicPropertyFetch
     */
    public function handleListRendering(RenderItemListEvent $event): void
    {
        $caller = $event->getCaller();
        if (
            !($caller instanceof HybridList)
            && !($caller instanceof ContentModel)
            && !($caller instanceof ModuleModel)
        ) {
            return;
        }

        if (!(bool) $caller->metamodel_add_notelist) {
            return;
        }

        $lists = null !== ($tmp = $caller->metamodel_notelist) ? \unserialize($tmp, ['allowed_classes' => false]) : [];

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
    public function handleFormRendering(ParseNoteListFormEvent $event): void
    {
        $renderSetting = $event->getRenderSetting();

        $lists = [$event->getNoteListId()];

        if (!$this->processActions($event->getMetaModel(), $lists)) {
            $renderSetting->set(self::NOTELIST_LIST, $lists);
            $renderSetting->set(self::NOTELIST_LIST_DISABLE_FORM, true);
        }
    }

    /**
     * Add the notelist action buttons .
     *
     * @param ParseItemEvent $event Parse the passed item.
     *
     * @return void
     */
    public function addNoteListActions(ParseItemEvent $event): void
    {
        $settings = $event->getRenderSettings();
        if (null === ($lists = $settings->get(self::NOTELIST_LIST))) {
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

            // Add note list name.
            $parsed['notelists_names']['notelist_' . $list] = $storage->getName();

            if ($formId = $storage->getMeta()->get('form')) {
                // Add payload values.
                if (\count($storageData = $storage->getMetaDataFor($item))) {
                    $parsed['notelists_payload_values']['notelist_' . $list] = $storageData;
                }

                // Add payload labels.
                $parsed['notelists_payload_labels']['notelist_' . $list] = $this->getFormFieldLabels((int) $formId);
            }

            // Hide all other actions input if form disabled.
            if (null !== $settings->get(self::NOTELIST_LIST_DISABLE_FORM)) {
                continue;
            }

            if ($formId = $storage->getMeta()->get('form')) {
                // Need to render the form here.
                $parsed['actions']['notelist_' . $list . '_form'] =
                    $this->generateForm($item, $storage, (int) $formId);
                if (!$storage->has($item)) {
                    continue;
                }
            }
            $parsed['actions']['notelist_' . $list . '_button'] = $this->generateButton($item, $storage);
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
    private function processActions(IMetaModel $metaModel, array $lists): bool
    {
        foreach ($lists as $list) {
            if ($event = $this->buildActionEvent($metaModel, $list)) {
                $this->dispatcher->dispatch($event, NoteListEvents::PROCESS_NOTE_LIST_ACTION);
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
    private function buildActionEvent(IMetaModel $metaModel, string $list): ?ProcessActionEvent
    {
        $url = $this->getCurrentUrl();

        if ($url->hasQueryParameter('notelist_' . $list . '_action')) {
            return new ProcessActionEvent(
                $url->getQueryParameter('notelist_' . $list . '_action') ?? '',
                ['item' => $url->getQueryParameter('notelist_' . $list . '_item') ?? ''],
                $this->factory->getList($metaModel, $list),
                $metaModel
            );
        }

        $noteList = $this->factory->getList($metaModel, $list);

        $valueBag = $noteList->getMeta();
        if ($valueBag->has('form') && ($formId = $valueBag->get('form'))) {
            $form = $this->formBuilder->getForm((int) $formId, $noteList, $this->getCurrentUrl()->getUrl());
            if (null !== ($data = $form->getSubmittedData())) {
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
    private function generateButton(IItem $item, NoteListStorage $storage): array
    {
        $action = !$storage->has($item) ? 'add' : 'remove';
        $url    = $this
            ->getCurrentUrl()
            ->setQueryParameter('notelist_' . $storage->getStorageKey() . '_action', $action)
            ->setQueryParameter('notelist_' . $storage->getStorageKey() . '_item', $item->get('id'));

        // Obtain list and generate button for it.
        $translator = System::getContainer()->get('translator');
        assert($translator instanceof TranslatorInterface);

        return [
            'name'  => $storage->getName(),
            'label' => $translator->trans(
                'metamodel_notelist.' . $action,
                ['%list_name%' => $storage->getName()],
                'notelist_default'
            ),
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
    private function generateForm(IItem $item, NoteListStorage $storage, int $formId): array
    {
        $form = $this->formBuilder->getForm($formId, $storage, $this->getCurrentUrl()->getUrl());

        return ['html' => $form->render($item)];
    }

    /**
     * Retrieve the labels of payload form.
     *
     * @param int $formId The form id.
     *
     * @return array
     */
    private function getFormFieldLabels(int $formId): array
    {
        $formLabels = [];

        if ($formId) {
            $objFields = FormFieldModel::findPublishedByPid($formId);
            assert($objFields instanceof Collection);
            $parser    = System::getContainer()->get('contao.insert_tag.parser');
            assert($parser instanceof InsertTagParser);

            foreach ($objFields as $objField) {
                $formLabels[$objField->name] = $parser->replace($objField->label);
            }
        }

        return $formLabels;
    }

    /**
     * Strip our parameters and redirect.
     *
     * @param string $identifier The identifier in the parameters.
     *
     * @return void
     *
     * @throws RedirectResponseException In order to redirect.
     */
    private function redirect(string $identifier): void
    {
        $url = $this
            ->getCurrentUrl()
            ->unsetQueryParameter('notelist_' . $identifier . '_action')
            ->unsetQueryParameter('notelist_' . $identifier . '_item')
            ->getUrl();

        throw new RedirectResponseException($url, 303);
    }

    /**
     * Retrieve a URL builder containing the current URL.
     *
     * @return UrlBuilder
     *
     * @internal
     */
    protected function getCurrentUrl(): UrlBuilder
    {
        $request = $this->requestStack->getCurrentRequest();
        assert($request instanceof Request);

        return $this->urlBuilderFactory->create($request->getRequestUri());
    }
}
