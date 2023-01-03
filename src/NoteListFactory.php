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

namespace MetaModels\NoteListBundle;

use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use MetaModels\Filter\Setting\FilterSettingFactory;
use MetaModels\IMetaModel;
use MetaModels\ITranslatedMetaModel;
use MetaModels\NoteListBundle\Storage\NoteListStorage;
use MetaModels\NoteListBundle\Storage\StorageAdapterFactory;
use MetaModels\NoteListBundle\Storage\ValueBag;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This class takes care of configuring and obtaining note list instances.
 */
class NoteListFactory
{
    /**
     * The database to use.
     *
     * @var Connection
     */
    private Connection $connection;

    /**
     * The storage factory to use.
     *
     * @var StorageAdapterFactory
     */
    private StorageAdapterFactory $storageFactory;

    /**
     * The created instances.
     *
     * @var NoteListStorage[]
     */
    private array $instances = [];

    /**
     * The filter setting factory.
     *
     * @var FilterSettingFactory
     */
    private FilterSettingFactory $filterFactory;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $dispatcher;

    /**
     * Create a new instance.
     *
     * @param EventDispatcherInterface $dispatcher     The event disapatcher.
     * @param Connection               $connection     The database to use.
     * @param StorageAdapterFactory    $storageFactory The storage factory.
     * @param FilterSettingFactory     $filterFactory  The filter setting factory.
     */
    public function __construct(
        EventDispatcherInterface $dispatcher,
        Connection $connection,
        StorageAdapterFactory $storageFactory,
        FilterSettingFactory $filterFactory
    ) {
        $this->dispatcher     = $dispatcher;
        $this->connection     = $connection;
        $this->storageFactory = $storageFactory;
        $this->filterFactory  = $filterFactory;
    }

    /**
     * Obtain the list of configured list instances for the passed MetaModel.
     *
     * @param IMetaModel $metaModel The MetaModel.
     *
     * @return array
     */
    public function getConfiguredListsFor(IMetaModel $metaModel): array
    {
        $query = $this
            ->connection
            ->createQueryBuilder()
            ->select('*')
            ->from('tl_metamodel_notelist')
            ->where('pid=:pid')
            ->setParameter('pid', $metaModel->get('id'))
            ->executeQuery()
            ->fetchAllAssociative();

        $languages = ($metaModel instanceof ITranslatedMetaModel)
            ? [
                $metaModel->getLanguage(),
                $metaModel->getMainLanguage()
            ] : [];

        $result = [];
        foreach ($query as $item) {
            $result[$item['id']] = $this->getName(StringUtil::deserialize($item['name'], true), $languages);
        }

        return $result;
    }

    /**
     * Retrieve a note list instance.
     *
     * @param IMetaModel $metaModel  The MetaModel to retrieve a notelist for.
     * @param string     $identifier The identifier of the notelist to retrieve this must be unique across MetaModels.
     *
     * @return NoteListStorage
     *
     * @throws \LogicException When the notelist does not belong to the MetaModel or could not be retrieved.
     */
    public function getList(IMetaModel $metaModel, string $identifier): NoteListStorage
    {
        if (isset($this->instances[$identifier])) {
            return $this->instances[$identifier];
        }

        $metaModelId = $metaModel->get('id');
        $noteList    = $this
            ->connection
            ->createQueryBuilder()
            ->select('*')
            ->from('tl_metamodel_notelist')
            ->where('id=:id')
            ->setParameter('id', $identifier)
            ->executeQuery()
            ->fetchAssociative();

        if (false === $noteList) {
            throw new \LogicException('Notelist ' . $identifier . ' could not be found.');
        }

        if ($metaModelId !== $noteList['pid']) {
            throw new \LogicException('Notelist ' . $identifier . ' does not belong to MetaModel ' . $metaModelId);
        }

        $adapter = $this->storageFactory->getAdapter((string) $noteList['storageAdapter']);

        return $this->instances[$identifier] = new NoteListStorage(
            $this->dispatcher,
            $metaModel,
            $adapter,
            $identifier,
            StringUtil::deserialize($noteList['name'], true),
            !empty($noteList['filter']) ? $this->filterFactory->createCollection($noteList['filter']) : null,
            new ValueBag($noteList)
        );
    }

    /**
     * Retrieve the name of this storage.
     *
     * @param string[] $names     The names.
     * @param string[] $languages The languages.
     *
     * @return string
     */
    public function getName(array $names, array $languages): string
    {
        if ($languages) {
            if (isset($names[$languages[0]])) {
                return $names[$languages[0]];
            }
            if (isset($names[$languages[1]])) {
                return $names[$languages[1]];
            }
        }

        return \current($names);
    }
}
