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

use Contao\StringUtil;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilder;
use MetaModels\CoreBundle\EventListener\DcGeneral\Breadcrumb\AbstractBreadcrumbListener;
use MetaModels\CoreBundle\EventListener\DcGeneral\Breadcrumb\BreadcrumbStore;
use MetaModels\CoreBundle\EventListener\DcGeneral\Breadcrumb\GetMetaModelTrait;

/**
 * Generate a breadcrumb for table tl_metamodel_notelist.
 */
class BreadCrumbNoteList extends AbstractBreadcrumbListener
{
    use GetMetaModelTrait;

    /**
     * {@inheritDoc}
     */
    protected function wantToHandle(GetBreadcrumbEvent $event): bool
    {
        $container = $event->getEnvironment()->getDataDefinition();
        assert($container instanceof ContainerInterface);

        return 'tl_metamodel_notelist' === $container->getName();
    }

    /**
     * {@inheritDoc}
     */
    protected function getBreadcrumbElements(EnvironmentInterface $environment, BreadcrumbStore $elements)
    {
        if (!$elements->hasId('tl_metamodel')) {
            $elements->setId(
                'tl_metamodel',
                $this->extractIdFrom($environment, 'pid')
            );
        }

        parent::getBreadcrumbElements($environment, $elements);

        $builder = UrlBuilder::fromUrl($elements->getUri())
            ->setQueryParameter('do', 'metamodels')
            ->setQueryParameter('table', 'tl_metamodel_notelist')
            ->setQueryParameter(
                'pid',
                ModelId::fromValues('tl_metamodel', $elements->getId('tl_metamodel'))->getSerialized()
            )
            ->unsetQueryParameter('act')
            ->unsetQueryParameter('id');

        $elements->push(
            StringUtil::ampersand($builder->getUrl()),
            \sprintf(
                $elements->getLabel('tl_metamodel_notelist'),
                $this->getMetaModel($elements->getId('tl_metamodel') ?? '')->getName()
            ),
            'bundles/metamodelsnotelist/images/icons/notelist.png'
        );
    }
}
