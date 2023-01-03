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

declare(strict_types = 1);

namespace MetaModels\NoteListBundle\EventListener\DcGeneral;

use Doctrine\DBAL\Connection;
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
     * @param string     $fid        The filter id (tl_metamodel_filtersetting.fid).
     * @param IFactory   $factory    The MetaModels factory.
     * @param Connection $connection The database connection.
     *
     * @return \MetaModels\IMetaModel|null
     */
    private function getMetaModel($fid, IFactory $factory, Connection $connection): ?\MetaModels\IMetaModel
    {
        // This is pretty lame and hardcoded - we need to adjust this when we have non DB based definitions.
        $filter = $connection
            ->createQueryBuilder()
            ->select('*')
            ->from('tl_metamodel_filter')
            ->where('id=:id')
            ->setParameter('id', $fid)
            ->executeQuery()
            ->fetchAssociative();

        if (false === $filter) {
            return null;
        }

        if (null === ($metaModelName = $factory->translateIdToMetaModelName($filter['pid']))) {
            return null;
        }
        if (null === ($metaModel = $factory->getMetaModel($metaModelName))) {
            return null;
        }
        return $metaModel;
    }
}
