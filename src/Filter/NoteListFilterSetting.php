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
    private $factory;

    /**
     * The note list to show.
     *
     * @var string
     */
    private $notelistId;

    /**
     * The MetaModel instance.
     *
     * @var IMetaModel
     */
    private $metaModel;

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
    public function get($strKey)
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
    public function generateFilterUrlFrom(IItem $objItem, IRenderSettings $objRenderSetting)
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getParameters()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getParameterDCA()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getParameterFilterNames()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getParameterFilterWidgets(
        $arrIds,
        $arrFilterUrl,
        $arrJumpTo,
        FrontendFilterOptions $objFrontendFilterOptions
    ) {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getReferencedAttributes()
    {
        return [];
    }
}
