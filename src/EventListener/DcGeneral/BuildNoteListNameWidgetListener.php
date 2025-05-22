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
 * @license    https://github.com/MetaModels/notelist/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\NoteListBundle\EventListener\DcGeneral;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use Contao\StringUtil;
use MetaModels\Dca\Helper;
use MetaModels\IFactory;
use MetaModels\IMetaModel;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * This class handles the building of the MCW for note list names.
 */
class BuildNoteListNameWidgetListener
{
    /**
     * The MetaModel factory.
     *
     * @var IFactory
     */
    private IFactory $factory;

    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * Create a new instance.
     *
     * @param IFactory            $factory    The MetaModels factory.
     * @param TranslatorInterface $translator The translator.
     */
    public function __construct(IFactory $factory, TranslatorInterface $translator)
    {
        $this->factory    = $factory;
        $this->translator = $translator;
    }

    /**
     * Decode the given value from a serialized language array into the real language array.
     *
     * @param DecodePropertyValueForWidgetEvent $event The event.
     *
     * @return void
     */
    public function decodeNameValue(DecodePropertyValueForWidgetEvent $event): void
    {
        if (
            ($event->getProperty() !== 'name')
            || ('tl_metamodel_notelist' !== $event->getModel()->getProviderName())
        ) {
            return;
        }

        $metaModel = $this->getMetaModelByModelPid($event->getModel());
        $values    = Helper::decodeLangArray($event->getValue(), $metaModel);

        $event->setValue(unserialize($values, ['allowed_classes' => false]));
    }

    /**
     * Encode the given value from a real language array into a serialized language array.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event.
     *
     * @return void
     */
    public function encodeNameValue(EncodePropertyValueFromWidgetEvent $event): void
    {
        if (
            ($event->getProperty() !== 'name')
            || ('tl_metamodel_notelist' !== $event->getModel()->getProviderName())
        ) {
            return;
        }

        $metaModel = $this->getMetaModelByModelPid($event->getModel());
        $values    = Helper::encodeLangArray($event->getValue(), $metaModel);

        $event->setValue($values);
    }

    /**
     * Build the widget for the MCW.
     *
     * @param BuildWidgetEvent $event The event.
     *
     * @return void
     */
    public function buildWidget(BuildWidgetEvent $event): void
    {
        $environment = $event->getEnvironment();
        if (
            !(($dataDefinition = $environment->getDataDefinition()) instanceof ContainerInterface)
            || ('tl_metamodel_notelist' !== $dataDefinition->getName())
            || ($event->getProperty()->getName() !== 'name')
        ) {
            return;
        }

        $metaModel     = $this->getMetaModelByModelPid($event->getModel());
        $property      = $event->getProperty();
        $languageLabel = $this->translator->trans('name_langcode.label', [], 'tl_metamodel_notelist');
        $valueLabel    = $this->translator->trans('name_value.label', [], 'tl_metamodel_notelist');
        $values        =
            StringUtil::deserialize($event->getModel()->getProperty($event->getProperty()->getName()), true);

        Helper::prepareLanguageAwareWidget(
            $environment,
            $property,
            $metaModel,
            $languageLabel,
            $valueLabel,
            false,
            $values
        );
    }

    /**
     * Get the MetaModel instance referenced in the pid property of the Model.
     *
     * @param ModelInterface $model The model.
     *
     * @return IMetaModel
     *
     * @throws \InvalidArgumentException When the MetaModel could not be retrieved.
     */
    private function getMetaModelByModelPid(ModelInterface $model): IMetaModel
    {
        $metaModel = $this
            ->factory
            ->getMetaModel($this->factory->translateIdToMetaModelName($model->getProperty('pid')));

        if ($metaModel === null) {
            throw new \InvalidArgumentException('Could not retrieve MetaModel ' . $model->getProperty('pid'));
        }

        return $metaModel;
    }
}
