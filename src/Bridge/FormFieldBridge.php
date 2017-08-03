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

namespace MetaModels\NoteList\Bridge;

use Contao\Widget;
use MetaModels\IFactory;
use MetaModels\NoteList\Form\FormRenderer;

/**
 * This renders a form field listing all the items in the note list.
 *
 * @property string metamodel_notelist
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
    protected $strTemplate = 'form_metamodels_notelist';

    /**
     * {@inheritDoc}
     */
    public function __set($strKey, $varValue)
    {
        if ('metamodel_notelist' === $strKey) {
            $data = unserialize($varValue);
            foreach ($data as $entry) {
                $listId                             = $entry['notelist'];
                $this->lists[]                      = $listId;
                $this->renderSettings[$listId]      = $entry['frontend'];
                $this->renderSettingsEmail[$listId] = $entry['email'];
            }

            return;
        }
        parent::__set($strKey, $varValue);
    }

    /**
     * {@inheritDoc}
     */
    public function __get($strKey)
    {
        if ('metamodel_notelist' === $strKey) {
            $data = [];
            foreach ($this->lists as $listId) {
                $data[] = [
                    'notelist' => $listId,
                    'frontend' => $this->renderSettings[$listId],
                    'email'    => $this->renderSettingsEmail[$listId]
                ];
            }

            return serialize($data);
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
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function parse($arrAttributes = null)
    {
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
            $factory->translateIdToMetaModelName($this->arrConfiguration['metamodel'])
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
            $parsed[$listId] = $renderer->render($listId, $this->renderSettings[$listId], $this->strFormat);
        }

        $this->parsed = $parsed;

        return parent::parse($arrAttributes);
    }

    /**
     * Retrieve the configured list ids.
     *
     * @return array
     */
    protected function getLists()
    {
        return $this->lists;
    }
}
