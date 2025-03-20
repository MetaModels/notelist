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
 * @license    https://github.com/MetaModels/notelist/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\NoteListBundle\Filter;

use MetaModels\Filter\IFilter;
use MetaModels\Filter\Setting\ISimple;
use MetaModels\FrontendIntegration\FrontendFilterOptions;
use MetaModels\IItem;
use MetaModels\IMetaModel;
use MetaModels\NoteListBundle\NoteListFactory;
use MetaModels\Render\Setting\ICollection as IRenderSettings;

/**
 * This filter setting takes care of filtering for note list entries.
 */
class NoteListFilterSetting implements ISimple
{
    /**
     * The notelist factory.
     *
     * @var NoteListFactory
     */
    private NoteListFactory $factory;

    /**
     * The note list to show.
     *
     * @var string
     */
    private string $notelistId;

    /**
     * The MetaModel instance.
     *
     * @var IMetaModel
     */
    private IMetaModel $metaModel;

    /**
     * Create a new instance.
     *
     * @param NoteListFactory $factory    The note list factory.
     * @param string          $notelistId The note list id.
     * @param IMetaModel      $metaModel  The MetaModel instance.
     */
    public function __construct(NoteListFactory $factory, string $notelistId, IMetaModel $metaModel)
    {
        $this->factory    = $factory;
        $this->notelistId = $notelistId;
        $this->metaModel  = $metaModel;
    }

    /**
     * {@inheritDoc}
     */
    public function get($strKey): mixed
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function prepareRules(IFilter $objFilter, $arrFilterUrl)
    {
        $objFilter->addFilterRule(
            new NoteListFilterRule($this->factory->getList($this->metaModel, $this->notelistId))
        );
    }

    /**
     * {@inheritDoc}
     */
    public function generateFilterUrlFrom(IItem $objItem, IRenderSettings $objRenderSetting): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getParameters(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getParameterDCA(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getParameterFilterNames(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function getParameterFilterWidgets(
        $arrIds,
        $arrFilterUrl,
        $arrJumpTo,
        FrontendFilterOptions $objFrontendFilterOptions
    ): array {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getReferencedAttributes(): array
    {
        return [];
    }
}
