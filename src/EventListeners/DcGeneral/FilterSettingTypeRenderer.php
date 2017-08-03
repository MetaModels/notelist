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

namespace MetaModels\NoteList\EventListeners\DcGeneral;

use Contao\Database;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use MetaModels\IFactory;
use MetaModels\NoteList\NoteListFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Handles rendering of model from tl_metamodel_filtersetting.
 */
class FilterSettingTypeRenderer
{
    use FilterIdToMetaModelTrait;

    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * The note list factory.
     *
     * @var NoteListFactory
     */
    private $noteListFactory;

    /**
     * The MetaModels factory.
     *
     * @var IFactory
     */
    private $factory;

    /**
     * The database connection.
     *
     * @var Database
     */
    private $database;

    /**
     * Create a new instance.
     *
     * @param TranslatorInterface      $translator      The translator.
     * @param EventDispatcherInterface $dispatcher      The dispatcher.
     * @param NoteListFactory          $noteListFactory The note list factory.
     * @param IFactory                 $factory         The MetaModels factory.
     * @param Database                 $database        The database connection.
     */
    public function __construct(
        TranslatorInterface $translator,
        EventDispatcherInterface $dispatcher,
        NoteListFactory $noteListFactory,
        IFactory $factory,
        Database $database
    ) {
        $this->translator      = $translator;
        $this->dispatcher      = $dispatcher;
        $this->noteListFactory = $noteListFactory;
        $this->factory         = $factory;
        $this->database        = $database;
    }

    /**
     * Render a filter setting into html.
     *
     * @param ModelToLabelEvent $event The event.
     *
     * @return void
     */
    public function modelToLabel(ModelToLabelEvent $event)
    {
        $model = $event->getModel();

        if (($model->getProviderName() !== 'tl_metamodel_filtersetting')
            || ('notelist' !== $event->getModel()->getProperty('type'))
        ) {
            return;
        }

        $event
            ->setLabel($this->translator->translate('typedesc.notelist', 'tl_metamodel_filtersetting'))
            ->setArgs($this->getLabelParameters($model));
    }

    /**
     * Retrieve the parameters for the label.
     *
     * @param ModelInterface $model The model.
     *
     * @return array
     */
    private function getLabelParameters(ModelInterface $model)
    {
        $metaModel = $this->getMetaModel(
            $model->getProperty('fid'),
            $this->factory,
            $this->database
        );
        if (null === $metaModel) {
            return [];
        }
        $lists = $this->noteListFactory->getConfiguredListsFor($metaModel);

        return [
            $this->getLabelImage($model),
            $this->getLabelText(),
            $this->getLabelComment($model->getProperty('comment')),
            $lists[$model->getProperty('notelist')]
        ];
    }

    /**
     * Retrieve the image for the label.
     *
     * @param ModelInterface $model The filter setting to render.
     *
     * @return string
     */
    private function getLabelImage(ModelInterface $model)
    {
        $image = !$model->getProperty('enabled')
            ? 'system/modules/metamodels_notelist/public/images/icons/notelist_1.png'
            : 'system/modules/metamodels_notelist/public/images/icons/notelist.png';

        /** @var AddToUrlEvent $urlEvent */
        $urlEvent = $this->dispatcher->dispatch(
            ContaoEvents::BACKEND_ADD_TO_URL,
            new AddToUrlEvent('act=edit&amp;id='.$model->getId())
        );

        /** @var GenerateHtmlEvent $imageEvent */
        $imageEvent = $this->dispatcher->dispatch(
            ContaoEvents::IMAGE_GET_HTML,
            new GenerateHtmlEvent(
                $image,
                $this->translator->translate('typedesc.notelist', 'tl_metamodel_filtersetting')
            )
        );

        return sprintf(
            '<a href="%s">%s</a>',
            $urlEvent->getUrl(),
            $imageEvent->getHtml()
        );
    }

    /**
     * Retrieve the label text for a filter setting.
     *
     * @return string
     */
    private function getLabelText()
    {
        return $this->translate('typenames.notelist', 'note list');
    }

    /**
     * Retrieve the comment for the label.
     *
     * @param string $comment The comment.
     *
     * @return string
     */
    private function getLabelComment(string $comment)
    {
        if (!empty($comment)) {
            return sprintf(
                $this->translator->translate('typedesc._comment_', 'tl_metamodel_filtersetting'),
                specialchars($comment)
            );
        }
        return '';
    }

    /**
     * Translate a string and return the default if not translated.
     *
     * @param string $string  The language key to translate.
     *
     * @param string $default The default text to return.
     *
     * @return string
     */
    private function translate(string $string, string $default = '')
    {
        $label = $this->translator->translate($string, 'tl_metamodel_filtersetting');
        if ($label == 'typenames.notelist') {
            return $default;
        }
        return $label;
    }
}
