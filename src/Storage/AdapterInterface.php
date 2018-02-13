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

namespace MetaModels\NoteListBundle\Storage;

/**
 * This interface describes an storage adapter for note lists.
 *
 * The adapter must accept arrays of arbitrary depth containing literals (int, string, ...)
 *
 * The passed arrays MUST NOT contain object instances.
 */
interface AdapterInterface
{
    /**
     * Retrieve the value from the storage.
     *
     * @param string $key The key to obtain.
     *
     * @return array
     */
    public function getKey(string $key) : array;

    /**
     * Set the value in the storage.
     *
     * @param string $key   The key to set.
     *
     * @param array  $value The value to set.
     *
     * @return array
     */
    public function setKey(string $key, array $value);
}
