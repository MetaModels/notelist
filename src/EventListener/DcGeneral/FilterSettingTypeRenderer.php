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

namespace MetaModels\NoteListBundle\EventListener\DcGeneral;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use MetaModels\IFactory;
use MetaModels\NoteListBundle\NoteListFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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
    private TranslatorInterface $translator;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $dispatcher;

    /**
     * The note list factory.
     *
     * @var NoteListFactory
     */
    private NoteListFactory $noteListFactory;

    /**
     * The MetaModels factory.
     *
     * @var IFactory
     */
    private IFactory $factory;

    /**
     * The database connection.
     *
     * @var Connection
     */
    private Connection $connection;

    /**
     * Create a new instance.
     *
     * @param TranslatorInterface      $translator      The translator.
     * @param EventDispatcherInterface $dispatcher      The dispatcher.
     * @param NoteListFactory          $noteListFactory The note list factory.
     * @param IFactory                 $factory         The MetaModels factory.
     * @param Connection               $connection      The database connection.
     */
    public function __construct(
        TranslatorInterface $translator,
        EventDispatcherInterface $dispatcher,
        NoteListFactory $noteListFactory,
        IFactory $factory,
        Connection $connection
    ) {
        $this->translator      = $translator;
        $this->dispatcher      = $dispatcher;
        $this->noteListFactory = $noteListFactory;
        $this->factory         = $factory;
        $this->connection      = $connection;
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

        if (
            ($model->getProviderName() !== 'tl_metamodel_filtersetting')
            || ('notelist' !== $event->getModel()->getProperty('type'))
        ) {
            return;
        }

        $event
            ->setLabel($this->translator->trans('typedesc.notelist', [], 'tl_metamodel_filtersetting'))
            ->setArgs($this->getLabelParameters($model));
    }

    /**
     * Retrieve the parameters for the label.
     *
     * @param ModelInterface $model The model.
     *
     * @return array
     */
    private function getLabelParameters(ModelInterface $model): array
    {
        $metaModel = $this->getMetaModel(
            (string) $model->getProperty('fid'),
            $this->factory,
            $this->connection
        );

        if (null === $metaModel) {
            return [];
        }

        $lists = $this->noteListFactory->getConfiguredListsFor($metaModel);

        return [
            $this->getLabelImage($model),
            $this->getLabelText(),
            $lists[$model->getProperty('notelist')],
            $this->getLabelComment($model->getProperty('comment')),
        ];
    }

    /**
     * Retrieve the image for the label.
     *
     * @param ModelInterface $model The filter setting to render.
     *
     * @return string
     */
    private function getLabelImage(ModelInterface $model): string
    {
        $image = !$model->getProperty('enabled')
            ? 'bundles/metamodelsnotelist/images/icons/notelist_1.png'
            : 'bundles/metamodelsnotelist/images/icons/notelist.png';

        /** @var AddToUrlEvent $urlEvent */
        $urlEvent = $this->dispatcher->dispatch(
            new AddToUrlEvent('act=edit&amp;id=' . $model->getId()),
            ContaoEvents::BACKEND_ADD_TO_URL
        );

        /** @var GenerateHtmlEvent $imageEvent */
        $imageEvent = $this->dispatcher->dispatch(
            new GenerateHtmlEvent(
                $image,
                $this->translator->trans('typedesc.notelist', [], 'tl_metamodel_filtersetting')
            ),
            ContaoEvents::IMAGE_GET_HTML
        );

        return \sprintf(
            '<a href="%s">%s</a>',
            $urlEvent->getUrl(),
            $imageEvent->getHtml() ?? ''
        );
    }

    /**
     * Retrieve the label text for a filter setting.
     *
     * @return string
     */
    private function getLabelText(): string
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
    private function getLabelComment(string $comment): string
    {
        if (!empty($comment)) {
            return $this->translator->trans(
                'typedesc._comment_',
                ['%comment%' => StringUtil::specialchars($comment)],
                'tl_metamodel_filtersetting'
            );
        }

        return '';
    }

    /**
     * Translate a string and return the default if not translated.
     *
     * @param string $string  The language key to translate.
     * @param string $default The default text to return.
     *
     * @return string
     */
    private function translate(string $string, string $default = ''): string
    {
        $label = $this->translator->trans($string, [], 'tl_metamodel_filtersetting');
        if ($label === 'typenames.notelist') {
            return $default;
        }

        return $label;
    }
}
