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

namespace MetaModels\NoteListBundle\Form;

use Contao\Controller;
use Contao\FormFieldModel;
use Contao\FormHidden;
use Contao\FormModel;
use Contao\Model\Collection;
use Contao\StringUtil;
use Contao\Widget;
use MetaModels\NoteListBundle\Storage\NoteListStorage;

/**
 * This class takes care of building Contao Forms, rendering them and retrieving the payload.
 */
class FormBuilder
{
    /**
     * Build a form.
     *
     * @param int             $formId   The id of the form.
     * @param NoteListStorage $noteList The note list to build a form for.
     * @param string          $action   The POST action.
     *
     * @return Form
     */
    public function getForm(int $formId, NoteListStorage $noteList, string $action): Form
    {
        return new Form(
            $this->getFormConfig($formId),
            $this->getFormWidgets($formId),
            $action,
            $noteList
        );
    }

    /**
     * Retrieve the form config of a form.
     *
     * @param int $formId The form ID.
     *
     * @return FormModel
     */
    private function getFormConfig(int $formId): FormModel
    {
        $form = FormModel::findById($formId);
        assert($form instanceof FormModel);

        return $form;
    }

    /**
     * Retrieve the form fields of a form.
     *
     * @param int $formId The form ID.
     *
     * @return Widget[]
     */
    private function getFormWidgets(int $formId): array
    {
        Controller::loadDataContainer('tl_form_field');
        // Get all form fields
        $objFields = FormFieldModel::findPublishedByPid($formId);
        if (!($objFields instanceof Collection) || 0 === $objFields->count()) {
            return [];
        }
        $fields = [];
        $hidden = [];

        // Process the fields.
        $row    = 0;
        $maxRow = \count($fields);
        foreach ($objFields as $objField) {
            $widget = $this->buildWidget($objField, $row, $maxRow);
            if (null === $widget) {
                continue;
            }
            if ($widget instanceof FormHidden) {
                $hidden[] = $widget;
                --$maxRow;
                continue;
            }

            $fields[] = $widget;
            ++$row;
        }

        return \array_merge($hidden, $fields);
    }

    /**
     * Build a widget.
     *
     * @param FormFieldModel $field  The form field model to build the widget for.
     * @param int            $row    The current row.
     * @param int            $maxRow The row count.
     *
     * @return Widget|null
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function buildWidget(FormFieldModel $field, int &$row, int &$maxRow): Widget|\Widget|null
    {
        // Continue if the class is not defined
        if (!\class_exists($strClass = ($GLOBALS['TL_FFL'][$field->type] ?? ''))) {
            return null;
        }

        $arrData                   = $field->row();
        $arrData['decodeEntities'] = true;
        $arrData['allowHtml']      = true;
        $arrData['rowClass']       = $this->getWidgetClass($row, $maxRow);
        $arrData['tableless']      = true;

        // Increase the row count if it's a password field
        if ($field->type === 'password') {
            ++$row;
            ++$maxRow;
            $arrData['rowClassConfirm'] = $this->getWidgetClass($row, $maxRow);
        }

        // Submit buttons do not use the name attribute
        if ($field->type === 'submit') {
            $arrData['name'] = '';
        }

        // Unset the default value depending on the field type (see #4722)
        if (!empty($arrData['value'])) {
            if (
                !\in_array(
                    'value',
                    StringUtil::trimsplit('[,;]', $GLOBALS['TL_DCA']['tl_form_field']['palettes'][$field->type])
                )
            ) {
                $arrData['value'] = '';
            }
        }

        /** @var \Widget $objWidget */
        $objWidget           = new $strClass($arrData);
        $objWidget->required = (bool) $field->mandatory;

        return $objWidget;
    }

    /**
     * Determine the CSS classes for a widget.
     *
     * @param int $row  The current row.
     * @param int $last The last row.
     *
     * @return string
     */
    private function getWidgetClass(int $row, int $last): string
    {
        $class = 'row_' . $row;
        if (0 === $row) {
            $class .= ' row_first';
        }
        if (($last - 1) === $row) {
            $class .= ' row_last';
        }
        $class .= (0 === ($row % 2)) ? ' even' : ' odd';

        return $class;
    }
}
