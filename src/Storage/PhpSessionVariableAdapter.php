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

/**
 * This class is the implementation of the PHP session storage.
 */
class PhpSessionVariableAdapter implements AdapterInterface
{
    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getKey(string $key): array
    {
        if (isset($_SESSION['metamodel_notelists']['metamodel_notelist_' . $key])) {
            return (array) $_SESSION['metamodel_notelists']['metamodel_notelist_' . $key];
        }

        return [];
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function setKey(string $key, array $value)
    {
        $_SESSION['metamodel_notelists']['metamodel_notelist_' . $key] = $value;
    }
}
