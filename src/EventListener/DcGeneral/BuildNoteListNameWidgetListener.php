<?php

/**
 * This file is part of MetaModels/notelist.
 *
 * (c) 2017-2020 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2017-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/notelist/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\NoteListBundle\EventListener\DcGeneral;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use MetaModels\IFactory;
use MetaModels\IMetaModel;

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
    private $factory;

    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    private $translator;

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
    public function decodeNameValue(DecodePropertyValueForWidgetEvent $event)
    {
        if (('tl_metamodel_notelist' !== $event->getModel()->getProviderName())
            || ($event->getProperty() !== 'name')) {
            return;
        }

        $metaModel = $this->getMetaModelByModelPid($event->getModel());
        $value     = deserialize($event->getValue());
        if (!$metaModel->isTranslated()) {
            // If we have an array, return the first value and exit, if not an array, return the value itself.
            $event->setValue(is_array($value) ? $value[key($value)] : $value);
            return;
        }

        // Sort like in MetaModel definition.
        $output = [];
        if (!empty($languages = $metaModel->getAvailableLanguages())) {
            foreach ($languages as $strLangCode) {
                if (is_array($value)) {
                    $subValue = $value[$strLangCode];
                } else {
                    $subValue = $value;
                }

                if (is_array($subValue)) {
                    $output[] = array_merge($subValue, ['langcode' => $strLangCode]);
                } else {
                    $output[] = ['langcode' => $strLangCode, 'value' => $subValue];
                }
            }
        }
        $event->setValue(serialize($output));
    }

    /**
     * Encode the given value from a real language array into a serialized language array.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event.
     *
     * @return void
     */
    public function encodeNameValue(EncodePropertyValueFromWidgetEvent $event)
    {
        if (('tl_metamodel_notelist' !== $event->getModel()->getProviderName())
            || ($event->getProperty() !== 'name')) {
            return;
        }

        $metaModel = $this->getMetaModelByModelPid($event->getModel());
        // Not translated, nothing to do.
        if (!$metaModel->isTranslated()) {
            return;
        }
        $output = [];

        foreach (deserialize($event->getValue()) as $subValue) {
            $langcode = $subValue['langcode'];
            unset($subValue['langcode']);
            if (count($subValue) > 1) {
                $output[$langcode] = $subValue;
            } else {
                $keys              = array_keys($subValue);
                $output[$langcode] = $subValue[$keys[0]];
            }
        }
        $event->setValue(serialize($output));
    }

    /**
     * Build the widget for the MCW.
     *
     * @param BuildWidgetEvent $event The event.
     *
     * @return void
     */
    public function buildWidget(BuildWidgetEvent $event)
    {
        if (('tl_metamodel_notelist' !== $event->getEnvironment()->getDataDefinition()->getName())
            || ($event->getProperty()->getName() !== 'name')) {
            return;
        }

        $metaModel = $this->getMetaModelByModelPid($event->getModel());
        $property  = $event->getProperty();

        if (!$metaModel->isTranslated()) {
            $extra = $property->getExtra();

            $extra['tl_class'] .= 'w50';

            $property
                ->setWidgetType('text')
                ->setExtra($extra);

            return;
        }

        $values     = deserialize($event->getModel()->getProperty($event->getProperty()->getName()), true);
        $fallback   = $metaModel->getFallbackLanguage();
        $languages  = $this->buildLanguageArray($metaModel, $this->translator);
        $neededKeys = array_keys($languages);

        // Ensure we have values for all languages present.
        if (array_diff_key(array_keys($values), $neededKeys)) {
            foreach ($neededKeys as $langCode) {
                $values[$langCode] = '';
            }
        }

        $rowClasses = [];
        foreach (array_keys($values) as $langCode) {
            $rowClasses[] = ($langCode == $fallback) ? 'fallback_language' : 'normal_language';
        }

        $languageLabel = $this->translator->translate('name_langcode', 'tl_metamodel_notelist');
        $valueLabel    = $this->translator->translate('name_value', 'tl_metamodel_notelist');

        $extra                   = $property->getExtra();
        $extra['minCount']       =
        $extra['maxCount']       = count($languages);
        $extra['disableSorting'] = true;
        $extra['hideButtons']    = true;
        $extra['tl_class']       = 'clr w50';
        $extra['columnFields']   = [
            'langcode' => [
                'label'     => $languageLabel,
                'exclude'   => true,
                'inputType' => 'justtextoption',
                'options'   => $languages,
                'eval'      => [
                    'rowClasses' => $rowClasses,
                    'valign'     => 'center',
                    'style'      => 'min-width:85px;display:block;'
                ]
            ],
            'value'    => [
                'label'     => $valueLabel,
                'exclude'   => true,
                'inputType' => 'text',
                'eval'      => [
                    'rowClasses' => $rowClasses,
                    'style'      => 'width:100%;',
                ]
            ],
        ];

        $property
            ->setWidgetType('multiColumnWizard')
            ->setExtra($extra);
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
    private function getMetaModelByModelPid($model)
    {
        $metaModel = $this
            ->factory
            ->getMetaModel($this->factory->translateIdToMetaModelName($model->getProperty('pid')));

        if ($metaModel === null) {
            throw new \InvalidArgumentException('Could not retrieve MetaModel ' . $model->getProperty('pid'));
        }

        return $metaModel;
    }

    /**
     * Extract all languages from the MetaModel and return them as array.
     *
     * @param IMetaModel          $metaModel  The MetaModel to extract the languages from.
     *
     * @param TranslatorInterface $translator The translator to use.
     *
     * @return \string[]
     */
    private function buildLanguageArray(IMetaModel $metaModel, TranslatorInterface $translator)
    {
        $languages = array();
        foreach ((array) $metaModel->getAvailableLanguages() as $langCode) {
            $languages[$langCode] = $translator->translate('LNG.' . $langCode, 'languages');
        }
        asort($languages);

        return $languages;
    }
}
