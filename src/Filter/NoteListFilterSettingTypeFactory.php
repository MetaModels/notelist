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

namespace MetaModels\NoteList\Filter;

use MetaModels\Filter\Setting\IFilterSettingTypeFactory;
use MetaModels\NoteList\NoteListFactory;

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
    private $factory;

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
    public function getTypeName()
    {
        return 'notelist';
    }

    /**
     * {@inheritDoc}
     */
    public function getTypeIcon()
    {
        return 'system/modules/metamodels_notelist/public/images/icons/notelist.png';
    }

    /**
     * {@inheritDoc}
     */
    public function createInstance($information, $filterSettings)
    {
        if (empty($information['notelist'])) {
            return null;
        }

        return new NoteListFilterSetting(
            $this->factory,
            $information['notelist'],
            $filterSettings->getMetaModel()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function isNestedType()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getMaxChildren()
    {
        return 0;
    }

    /**
     * {@inheritDoc}
     */
    public function getKnownAttributeTypes()
    {
        return null;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \LogicException As this class does not support this method.
     */
    public function addKnownAttributeType($typeName)
    {
        throw new \LogicException('You must not add attribute types to ' . __CLASS__);
    }
}
