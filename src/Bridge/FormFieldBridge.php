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

namespace MetaModels\NoteListBundle\Bridge;

use Contao\System;
use Contao\Widget;
use MetaModels\IFactory;
use MetaModels\NoteListBundle\Form\FormRenderer;
use MetaModels\NoteListBundle\NoteListFactory;
use MetaModels\Render\Setting\IRenderSettingFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This renders a form field listing all the items in the note list.
 *
 * @property string      metamodel_notelist
 * @property string|null metamodel_customTplEmail
 * @property string[]    parsed
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class FormFieldBridge extends Widget
{
    /**
     * The lists to be embedded.
     *
     * @var string[]
     */
    private array $lists;

    /**
     * The list of render settings to apply (indexed by list id).
     *
     * @var string[]
     */
    private array $renderSettings;

    /**
     * The list of render settings to apply (indexed by list id).
     *
     * @var string[]
     */
    private array $renderSettingsEmail;

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
                $data = \unserialize($varValue, ['allowed_classes' => false]);
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
                return \serialize($data);
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
            $this->strFormat,
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
        /** @psalm-suppress UndefinedThisPropertyFetch */
        return $this->abstractParse(
            $this->renderSettingsEmail,
            'text',
            $this->metamodel_customTplEmail ?: 'email_metamodels_notelist.text'
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function abstractParse(array $renderSetting, string $format, string $template, $attributes = null): string
    {
        $keepTemplate      = $this->customTpl;
        $keepFormat        = $this->strFormat;
        $this->customTpl   = $template;
        $this->strFormat   = $format;
        $this->strTemplate = $template;

        /** @var IFactory $factory */
        $container      = System::getContainer();
        $serviceLocator = $container->get('metamodels-notelist.bridge-locator');
        assert($serviceLocator instanceof ServiceLocator);
        $factory     = $serviceLocator->get(IFactory::class);
        $metaModelId = $this->arrConfiguration['metamodel'];
        $metaModel   = $factory->getMetaModel($factory->translateIdToMetaModelName($metaModelId));

        $isBackend = (bool) System::getContainer()
            ->get('contao.routing.scope_matcher')
            ?->isBackendRequest(
                System::getContainer()->get('request_stack')?->getCurrentRequest() ?? Request::create('')
            );

        if ($isBackend) {
            $translator = $container->get('translator');
            assert($translator instanceof TranslatorInterface);

            return $translator->trans(
                'notelist.display_backend',
                [
                    '%id%' => ($metaModel ? $metaModel->getName() : 'Unknown MetaModel id ' . $metaModelId)
                ],
                'notelist_default'
            );
        }

        if (null === $metaModel) {
            return '';
        }

        $renderer = new FormRenderer(
            $metaModel,
            $serviceLocator->get(IRenderSettingFactory::class),
            $serviceLocator->get(NoteListFactory::class),
            $serviceLocator->get(EventDispatcherInterface::class)
        );

        $parsed = [];
        $names  = [];
        foreach ($this->lists as $listId) {
            $names[$listId]  = $serviceLocator->get(NoteListFactory::class)->getList($metaModel, $listId)->getName();
            $parsed[$listId] = $renderer->render($listId, $renderSetting[$listId], $format);
        }

        /** @psalm-suppress UndefinedThisPropertyAssignment */
        $this->names       = $names;
        /** @psalm-suppress UndefinedThisPropertyAssignment */
        $this->parsed      = $parsed;
        $result            = parent::parse($attributes);
        $this->customTpl   = $keepTemplate;
        $this->strFormat   = $keepFormat;
        $this->strTemplate = 'form_metamodels_notelist';

        return $result;
    }
}
