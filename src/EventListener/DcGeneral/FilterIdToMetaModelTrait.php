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

namespace MetaModels\NoteList\EventListener\DcGeneral;

use Contao\Database;
use MetaModels\IFactory;

/**
 * This trait converts a 'fid' value from tl_metamodel_filtersetting to a MetaModel instance.
 *
 * @internal
 */
trait FilterIdToMetaModelTrait
{
    /**
     * Retrieve the MetaModel from the passed filter id.
     *
     * @param string   $fid      The filter id (tl_metamodel_filtersetting.fid).
     * @param IFactory $factory  The MetaModels factory.
     * @param Database $database The database connection.
     *
     * @return \MetaModels\IMetaModel|null
     */
    private function getMetaModel($fid, IFactory $factory, Database $database)
    {
        // This is pretty lame and hardcoded - we need to adjust this when we have non DB based definitions.
        $filter = $database
            ->prepare('SELECT * FROM tl_metamodel_filter WHERE id=?')
            ->execute($fid);
        if (0 === $filter->numRows) {
            return null;
        }

        if (null === ($metaModelName = $factory->translateIdToMetaModelName($filter->pid))) {
            return null;
        }
        if (null === ($metaModel = $factory->getMetaModel($metaModelName))) {
            return null;
        }
        return $metaModel;
    }
}
