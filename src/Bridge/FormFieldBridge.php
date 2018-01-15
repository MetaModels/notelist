<?php

/**
 * This file is part of MetaModels/notelist.
 *
 * (c) 2017 - 2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2017 - 2018 The MetaModels team.
 * @license    https://github.com/MetaModels/notelist/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

declare(strict_types = 1);

namespace MetaModels\NoteList\Bridge;

use Contao\Widget;
use MetaModels\IFactory;
use MetaModels\NoteList\Form\FormRenderer;

/**
 * This renders a form field listing all the items in the note list.
 *
 * @property string      metamodel_notelist
 * @property string|null metamodel_customTplEmail
 * @property string[]    parsed
 */
class FormFieldBridge extends Widget
{
    /**
     * The lists to be embedded.
     *
     * @var string[]
     */
    private $lists;

    /**
     * The list of render settings to apply (indexed by list id).
     *
     * @var string[]
     */
    private $renderSettings;

    /**
     * The list of render settings to apply (indexed by list id).
     *
     * @var string[]
     */
    private $renderSettingsEmail;

    /**
     * {@inheritDoc}
     */
    protected $blnSubmitInput = true;

    /**
     * {@inheritDoc}
     */
    protected $strTemplate = 'form_metamodels_notelist';

    /**
     * {@inheritDoc}
     */
    public function __set($strKey, $varValue)
    {
        switch ($strKey) {
            case 'metamodel_notelist':
                $data = unserialize($varValue);
                foreach ($data as $entry) {
                    $listId                             = $entry['notelist'];
                    $this->lists[]                      = $listId;
                    $this->renderSettings[$listId]      = $entry['frontend'];
                    $this->renderSettingsEmail[$listId] = $entry['email'];
                }
                return;
            case 'value':
                // Can not set value!
                return;
            default:
        }
        parent::__set($strKey, $varValue);
    }

    /**
     * {@inheritDoc}
     */
    public function __get($strKey)
    {
        switch ($strKey) {
            case 'metamodel_notelist':
                $data = [];
                foreach ($this->lists as $listId) {
                    $data[] = [
                        'notelist' => $listId,
                        'frontend' => $this->renderSettings[$listId],
                        'email'    => $this->renderSettingsEmail[$listId]
                    ];
                }
                return serialize($data);
            case 'value':
                return $this->parseValue();
            default:
        }

        return parent::__get($strKey);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException This method is not supported by this widget.
     */
    public function generate()
    {
        throw new \RuntimeException('This should not be called in Contao 3.5+');
    }

    /**
     * {@inheritDoc}
     */
    public function parse($arrAttributes = null)
    {
        return $this->abstractParse(
            $this->renderSettings,
            'text',
            $this->customTpl ?: $this->strTemplate
        );
    }

    /**
     * Parse the value and return it as string.
     *
     * @return string
     */
    public function parseValue()
    {
        return $this->abstractParse(
            $this->renderSettingsEmail,
            'text',
            $this->metamodel_customTplEmail ?: 'email_metamodels_notelist'
        );
    }

    /**
     * Parse the list.
     *
     * @param string[] $renderSetting The render settings to use.
     * @param string   $format        The format to use.
     * @param string   $template      The template to use.
     * @param null     $attributes    The attributes to use.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function abstractParse($renderSetting, $format, $template, $attributes = null)
    {
        $keepTemplate    = $this->customTpl;
        $keepFormat      = $this->strFormat;
        $this->customTpl = $template;
        $this->strFormat = $format;

        /** @var IFactory $factory */
        $factory     = $GLOBALS['container']['metamodels-factory.factory'];
        $metaModelId = $this->arrConfiguration['metamodel'];
        $metaModel   = $factory->getMetaModel($factory->translateIdToMetaModelName($metaModelId));

        if ('BE' === TL_MODE) {
            return sprintf(
                $GLOBALS['TL_LANG']['MSC']['metamodel_notelist_display_backend'],
                ($metaModel ? $metaModel->getName() : 'unknown MetaModel id ' . $metaModelId)
            );
        }

        $metaModel = $factory->getMetaModel(
            $factory->translateIdToMetaModelName($metaModelId)
        );

        if (!$metaModel) {
            return '';
        }

        $renderer = new FormRenderer(
            $metaModel,
            $GLOBALS['container']['metamodels-render-setting-factory.factory'],
            $GLOBALS['container']['metamodels-notelist.factory'],
            $GLOBALS['container']['event-dispatcher']
        );

        $parsed = [];
        foreach ($this->lists as $listId) {
            $parsed[$listId] = $renderer->render($listId, $renderSetting[$listId], $format);
        }

        $this->parsed    = $parsed;
        $result          = parent::parse($attributes);
        $this->customTpl = $keepTemplate;
        $this->strFormat = $keepFormat;

        return $result;
    }
}
