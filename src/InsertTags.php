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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2017 The MetaModels team.
 * @license    https://github.com/MetaModels/notelist/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

declare(strict_types = 1);

namespace MetaModels\NoteList;

use MetaModels\IFactory;

/**
 * This class handles the insert tag processing for note lists.
 */
class InsertTags
{
    /**
     * The factory.
     *
     * @var NoteListFactory
     */
    private $factory;

    /**
     * The MetaModel factory.
     *
     * @var IFactory
     */
    private $metaModelFactory;

    /**
     * Create a new instance.
     *
     * @param NoteListFactory $factory          The note list factory.
     * @param IFactory        $metaModelFactory The MetaModel factory.
     */
    public function __construct(NoteListFactory $factory, IFactory $metaModelFactory)
    {
        $this->factory          = $factory;
        $this->metaModelFactory = $metaModelFactory;
    }

    /**
     * Process the "sum" insert tag.
     *
     * @param array $arguments The arguments.
     *
     * @return bool|string
     */
    public function processNoteListSum(array $arguments)
    {
        $metaModel = $this->metaModelFactory->getMetaModel($arguments[0]);
        if (!$metaModel) {
            return false;
        }

        if (!empty($arguments[1])) {
            $lists = explode(',', $arguments[1]);
        } else {
            $lists = array_keys($this->factory->getConfiguredListsFor($metaModel));
        }

        $sum = 0;
        foreach ($lists as $list) {
            $sum += $this->factory->getList($metaModel, (string) $list)->getCount();
        }

        return $sum;
    }
}
