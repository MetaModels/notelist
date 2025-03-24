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

declare(strict_types=1);

namespace MetaModels\NoteListBundle\Storage;

use Countable;
use InvalidArgumentException;
use IteratorAggregate;

/**
 * A generic bag containing values.
 *
 * @implements IteratorAggregate<string, mixed>
 */
final class ValueBag implements IteratorAggregate, Countable
{
    /**
     * All values in this bag.
     *
     * @var array
     */
    private array $values = [];

    /**
     * Create a new instance of a bag.
     *
     * @param array $values The initial values to use.
     */
    public function __construct(array $values)
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Check if a value exists in this bag.
     *
     * @param string $name The name of the value to check.
     *
     * @return bool
     */
    public function has(string $name)
    {
        return \array_key_exists($name, $this->values);
    }

    /**
     * Return a value.
     *
     * @param string $name The name of the value to check.
     *
     * @return mixed
     *
     * @throws InvalidArgumentException If the value is not contained within the bag.
     */
    public function get(string $name)
    {
        $this->require($name);
        return $this->values[$name];
    }

    /**
     * Set a value.
     *
     * @param string $name  The name of the value to set.
     *
     * @param mixed  $value The value to use.
     *
     * @return ValueBag
     */
    public function set(string $name, $value): ValueBag
    {
        $this->values[$name] = $value;

        return $this;
    }

    /**
     * Remove a value.
     *
     * @param string $name The name of the value to remove.
     *
     * @return ValueBag
     *
     * @throws InvalidArgumentException If the value is not contained within the bag.
     */
    public function remove(string $name): ValueBag
    {
        $this->require($name);
        unset($this->values[$name]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->values);
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return \count($this->values);
    }

    /**
     * Exports the values as array.
     *
     * @return array
     */
    public function getArrayCopy(): array
    {
        return $this->values;
    }

    /**
     * Check if a value exists, otherwise through an exception.
     *
     * @param string $name The name of the value to require.
     *
     * @return void
     *
     * @throws InvalidArgumentException If the value is not registered.
     *
     * @internal
     */
    private function require(string $name)
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException('The value "' . $name . '" does not exist.');
        }
    }
}
