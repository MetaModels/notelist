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

use Contao\FormHidden;
use Contao\FormModel;
use Contao\FormSubmit;
use Contao\Input;
use Contao\StringUtil;
use Contao\Widget;
use MetaModels\IItem;
use MetaModels\NoteListBundle\Storage\NoteListStorage;
use MetaModels\Render\Template;

/**
 * This represents a form for submitting meta data.
 */
class Form
{
    /**
     * The sub form.
     *
     * @var FormModel
     */
    private $form;

    /**
     * The widgets.
     *
     * @var array
     */
    private $widgets;

    /**
     * The form id.
     *
     * @var string
     */
    private string $formId;

    /**
     * The POST action.
     *
     * @var string
     */
    private string $action;

    /**
     * The note list.
     *
     * @var NoteListStorage
     */
    private NoteListStorage $noteList;

    /**
     * Create a new instance.
     *
     * @param FormModel       $form     The sub form.
     * @param array           $widgets  The widgets.
     * @param string          $action   The POST action.
     * @param NoteListStorage $noteList The note list.
     */
    public function __construct (FormModel $form, array $widgets, string $action, NoteListStorage $noteList)
    {
        $this->form     = $form;
        $this->widgets  = $widgets;
        $this->formId   = 'mm_note_list_' . $noteList->getStorageKey();
        $this->action   = $action;
        $this->noteList = $noteList;
    }

    /**
     * Retrieve the submitted data.
     *
     * @return null|array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getSubmittedData(): ?array
    {
        // Validate the input
        if (Input::post('FORM_SUBMIT') !== $this->formId) {
            return null;
        }

        $doNotSubmit = false;
        $submitted   = [];
        foreach ($this->widgets as $widget) {
            $name = $widget->name;
            $widget->validate();
            if ($widget->hasErrors()) {
                $doNotSubmit = true;
                continue;
            }
            if ($widget->submitInput()) {
                // Store current value in the session to keep value when unsetting. - See contao/core#5474
                $submitted[$name]             = $widget->value;
                $_SESSION['FORM_DATA'][$name] = $widget->value;
                unset($_POST[$name]);
            }
        }

        if ($doNotSubmit) {
            return null;
        }

        /** @var FormHidden $itemId */
        $itemId = new $GLOBALS['TL_FFL']['hidden'](['name' => 'NOTELIST_ITEM']);
        $itemId->validate();
        $submitted['item'] = $itemId->value;

        return $submitted;
    }

    /**
     * Render the form as string.
     *
     * @param IItem $item The item to build the form for.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function render(IItem $item): string
    {
        // Just to ensure the widgets are validated.
        $this->getSubmittedData();

        /** @var FormHidden $itemId */
        $itemId = new $GLOBALS['TL_FFL']['hidden'](['name' => 'NOTELIST_ITEM', 'value' => $item->get('id')]);
        $fields = $itemId->parse();

        $idCount = 1;
        $data    = ($this->noteList->has($item)) ? $this->noteList->getMetaDataFor($item) : [];
        foreach ($this->widgets as $widget) {
            if (array_key_exists($widget->name, $data)) {
                $widget->value = $data[$widget->name];
                $widget->id    = $item->get('id') . '_' . $idCount;
                $idCount++;
            }
            $fields .= $widget->parse();
        }

        $action      = !$this->noteList->getMetaDataFor($item) ? 'add' : 'edit';
        $submitLabel =
            \sprintf($GLOBALS['TL_LANG']['MSC']['metamodel_notelist_' . $action], $this->noteList->getName());

        /** @var FormSubmit $submit */
        $submit = new $GLOBALS['TL_FFL']['submit'](
            ['label' => $submitLabel, 'id' => $item->get('id') . '_' . $idCount]
        );
        $fields .= $submit->parse();

        $cssClass = trim(StringUtil::deserialize($this->form->attributes, true)[1]);

        $template = new Template('form_wrapper');
        $template->setData(
            [
                'formId'       => 'nl_' . $this->noteList->getStorageKey() . '_f' . $item->get('id'),
                'hidden'       => '',
                'formSubmit'   => $this->formId,
                'requestToken' => REQUEST_TOKEN,
                'cssID'        => '',
                'style'        => '',
                'headline'     => '',
                'attributes'   => '',
                'novalidate'   => $this->form->novalidate,
                'maxFileSize'  => '',
                'method'       => 'post',
                'fields'       => $fields,
                'action'       => $this->action,
                'enctype'      => 'multipart/form-data',
                'class'        => $action . (!empty($cssClass) ? ' ' . $cssClass : '')
            ]
        );

        return $template->parse('html5');
    }
}
