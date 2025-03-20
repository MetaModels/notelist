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

use MetaModels\Filter\Setting\IFilterSettingTypeFactory;
use MetaModels\Filter\Setting\ISimple;
use MetaModels\NoteListBundle\NoteListFactory;

/**
 * Attribute type factory for note list filter settings.
 */
class NoteListFilterSettingTypeFactory implements IFilterSettingTypeFactory
{
    /**
     * The notelist factory.
     *
     * @var NoteListFactory
     */
    private NoteListFactory $factory;

    /**
     * Create a new instance.
     *
     * @param NoteListFactory $factory The note list factory.
     */
    public function __construct(NoteListFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritDoc}
     */
    public function getTypeName(): string
    {
        return 'notelist';
    }

    /**
     * {@inheritDoc}
     */
    public function getTypeIcon(): string
    {
        return 'system/modules/metamodels_notelist/public/images/icons/notelist.png';
    }

    /**
     * {@inheritDoc}
     */
    public function createInstance($information, $filterSettings): ISimple|NoteListFilterSetting|null
    {
        if (empty($information['notelist'])) {
            return null;
        }

        return new NoteListFilterSetting(
            $this->factory,
            (string) $information['notelist'],
            $filterSettings->getMetaModel()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function isNestedType(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getMaxChildren(): ?int
    {
        return 0;
    }

    /**
     * {@inheritDoc}
     */
    public function getKnownAttributeTypes(): ?array
    {
        return null;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \LogicException As this class does not support this method.
     */
    public function addKnownAttributeType($typeName): IFilterSettingTypeFactory
    {
        throw new \LogicException('You must not add attribute types to ' . __CLASS__);
    }
}
