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

namespace MetaModels\NoteListBundle\Storage;

use Contao\Session;
use MetaModels\NoteListBundle\Storage\Exception\AdapterNotFoundException;

/**
 * This class represents the storage adapter factory.
 */
class StorageAdapterFactory
{
    /**
     * The created instances.
     *
     * @var AdapterInterface[]
     */
    private $instances = [];

    /**
     * Retrieve an adapter by name.
     *
     * @param string $identifier The identifier of the adapter.
     *
     * @return AdapterInterface
     *
     * @throws AdapterNotFoundException When the adapter could not be located.
     */
    public function getAdapter(string $identifier)
    {
        if (isset($this->instances[$identifier])) {
            return $this->instances[$identifier];
        }

        switch ($identifier) {
            case 'php-session':
                return $this->instances[$identifier] = new PhpSessionVariableAdapter();
            case 'contao-session':
                return $this->instances[$identifier] = new ContaoSessionAdapter(Session::getInstance());
            default:
        }
        throw new AdapterNotFoundException();
    }

    /**
     * Retrieve the list of identifiers.
     *
     * @return array
     */
    public function getIdentifiers()
    {
        return [
            'php-session',
            'contao-session'
        ];
    }
}
