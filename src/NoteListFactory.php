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

namespace MetaModels\NoteList;

use Contao\Database;
use MetaModels\IMetaModel;
use MetaModels\NoteList\Storage\NoteListStorage;
use MetaModels\NoteList\Storage\StorageAdapterFactory;

/**
 * This class takes care of configuring and obtaining note list instances.
 */
class NoteListFactory
{
    /**
     * The database to use.
     *
     * @var Database
     */
    private $database;

    /**
     * The storage factory to use.
     *
     * @var StorageAdapterFactory
     */
    private $storageFactory;

    /**
     * The created instances.
     *
     * @var NoteListStorage[]
     */
    private $instances = [];

    /**
     * Create a new instance.
     *
     * @param Database              $database       The database to use.
     * @param StorageAdapterFactory $storageFactory The storage factory.
     */
    public function __construct(Database $database, StorageAdapterFactory $storageFactory)
    {
        $this->database       = $database;
        $this->storageFactory = $storageFactory;
    }

    /**
     * Obtain the list of configured list instances for the passed MetaModel.
     *
     * @param IMetaModel $metaModel The MetaModel.
     *
     * @return array
     */
    public function getConfiguredListsFor(IMetaModel $metaModel)
    {
        $query = $this
            ->database
            ->prepare('SELECT * FROM tl_metamodel_notelist WHERE pid=?')
            ->execute($metaModel->get('id'));

        $languages = $metaModel->isTranslated()
            ? [
                $metaModel->getActiveLanguage(),
                $metaModel->getFallbackLanguage()
            ] : [];

        $result = [];
        while ($query->next()) {
            $result[$query->id] = $this->getName(deserialize($query->name, true), $languages);
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
    public function getList(IMetaModel $metaModel, string $identifier)
    {
        if (isset($this->instances[$identifier])) {
            return $this->instances[$identifier];
        }

        $metaModelId = $metaModel->get('id');
        $noteList    = $this
            ->database
            ->prepare('SELECT * FROM tl_metamodel_notelist WHERE id=?')
            ->execute($identifier);

        if (0 === $noteList->length) {
            throw new \LogicException('Notelist ' . $identifier . ' could not be found.');
        }

        if ($metaModelId !== $noteList->pid) {
            throw new \LogicException('Notelist ' . $identifier . ' does not belong to MetaModel ' . $metaModelId);
        }

        $adapter = $this->storageFactory->getAdapter((string) $noteList->storageAdapter);

        return $this->instances[$identifier] = new NoteListStorage(
            $metaModel,
            $adapter,
            $identifier,
            deserialize($noteList->name, true)
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
    public function getName(array $names, array $languages)
    {
        if ($languages) {
            if (isset($names[$languages[0]])) {
                return $names[$languages[0]];
            }
            if (isset($names[$languages[1]])) {
                return $names[$languages[1]];
            }
        }

        return current($names);
    }
}
