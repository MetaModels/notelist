<?php

/**
 * This file is part of MetaModels/notelist.
 *
 * (c) 2017-2023 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2017-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/notelist/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types = 1);

namespace MetaModels\NoteListBundle\Form;

use MetaModels\IItems;
use MetaModels\IMetaModel;
use MetaModels\NoteListBundle\Event\NoteListEvents;
use MetaModels\NoteListBundle\Event\ParseNoteListFormEvent;
use MetaModels\NoteListBundle\Filter\NoteListFilterRule;
use MetaModels\NoteListBundle\NoteListFactory;
use MetaModels\Render\Setting\IRenderSettingFactory;
use MetaModels\Render\Template;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This class takes care of configuring and obtaining note list instances.
 */
class FormRenderer
{
    /**
     * The MetaModel.
     *
     * @var IMetaModel
     */
    private $metaModel;

    /**
     * The render setting factory.
     *
     * @var IRenderSettingFactory
     */
    private $renderSettingFactory;

    /**
     * The note list factory.
     *
     * @var NoteListFactory
     */
    private $noteListFactory;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Create a new instance.
     *
     * @param IMetaModel               $metaModel            The MetaModel.
     * @param IRenderSettingFactory    $renderSettingFactory The render settings factory.
     * @param NoteListFactory          $noteListFactory      The note list factory.
     * @param EventDispatcherInterface $dispatcher           The event dispatcher to use.
     */
    public function __construct(
        IMetaModel $metaModel,
        IRenderSettingFactory $renderSettingFactory,
        NoteListFactory $noteListFactory,
        EventDispatcherInterface $dispatcher
    ) {
        $this->metaModel            = $metaModel;
        $this->renderSettingFactory = $renderSettingFactory;
        $this->noteListFactory      = $noteListFactory;
        $this->dispatcher           = $dispatcher;
    }

    /**
     * Render the contents.
     *
     * @param string $noteListId      The id of the note list to render.
     * @param string $renderSettingId The id of the render setting to use.
     * @param string $format          The desired output format (defaults to 'text').
     *
     * @return string
     */
    public function render(string $noteListId, string $renderSettingId, string $format = 'text')
    {
        $renderSetting = $this->renderSettingFactory->createCollection($this->metaModel, $renderSettingId);

        $items = $this->getItemsForList($noteListId);

        $template = new Template($renderSetting->get('template'));

        $event = new ParseNoteListFormEvent($this->metaModel, $renderSetting, $noteListId);
        $this->dispatcher->dispatch($event, NoteListEvents::PARSE_NOTE_LIST_FORM);

        $template->view  = $renderSetting;
        $template->items = $items;
        $translator      = \Contao\System::getContainer()->get('translator');
        foreach ([
                     'MSC.' . $this->metaModel->getTableName() . '.' . $renderSettingId . '.noItemsMsg',
                     'MSC.' . $this->metaModel->getTableName() . '.noItemsMsg',
                     'MSC.noItemsMsg',
                 ] as $key) {
            if ($key !== $translated = $translator->trans($key, [], 'contao_default')) {
                break;
            }
        }
        $template->noItemsMsg = $translated;

        if ($items->getCount()) {
            $template->data = $items->parseAll($format, $renderSetting);
        } else {
            $template->data = [];
        }

        return $template->parse($format, true);
    }

    /**
     * Retrieve the items from the list.
     *
     * @param string $noteListId The list identifier.
     *
     * @return IItems
     */
    private function getItemsForList(string $noteListId)
    {
        $list   = $this->noteListFactory->getList($this->metaModel, $noteListId);
        $filter = $this->metaModel->getEmptyFilter();

        $filter->addFilterRule(new NoteListFilterRule($list));

        return $this->metaModel->findByFilter($filter);
    }
}
