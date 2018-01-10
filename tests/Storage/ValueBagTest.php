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

namespace MetaModels\NoteList\Test\Storage;

use MetaModels\NoteList\Storage\ValueBag;
use PHPUnit\Framework\TestCase;

/**
 * This tests the ValueBag class.
 */
class ValueBagTest extends TestCase
{
    /**
     * Test that the class can be instantiated.
     *
     * @return void
     *
     * @covers \MetaModels\NoteList\Storage\ValueBag::__construct()
     */
    public function testInstantiation()
    {
        $bag = new ValueBag([]);

        $this->assertInstanceOf('MetaModels\NoteList\Storage\ValueBag', $bag);
    }

    /**
     * Test the has method
     *
     * @return void
     *
     * @covers \MetaModels\NoteList\Storage\ValueBag::has()
     */
    public function testHasWorksProperly()
    {
        $bag = new ValueBag(['key' => 'value']);

        $this->assertTrue($bag->has('key'));
        $this->assertFalse($bag->has('unknown-key'));
    }

    /**
     * Test the get method
     *
     * @return void
     *
     * @covers \MetaModels\NoteList\Storage\ValueBag::get()
     * @covers \MetaModels\NoteList\Storage\ValueBag::require()
     */
    public function testGetWorksProperly()
    {
        $bag = new ValueBag(['key' => 'value']);

        $this->assertSame('value', $bag->get('key'));
    }

    /**
     * Test the get method
     *
     * @return void
     *
     * @covers \MetaModels\NoteList\Storage\ValueBag::get()
     * @covers \MetaModels\NoteList\Storage\ValueBag::require()
     */
    public function testGetUnknownThrowsException()
    {
        $bag = new ValueBag([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The value "unkown" does not exist');

        $bag->get('unkown');
    }

    /**
     * Test the set method
     *
     * @return void
     *
     * @covers \MetaModels\NoteList\Storage\ValueBag::set()
     */
    public function testSetWorksProperly()
    {
        $bag = new ValueBag(['key' => 'value']);

        $this->assertSame($bag, $bag->set('key', 'another-value'));
        $this->assertSame('another-value', $bag->get('key'));
    }

    /**
     * Test the remove method
     *
     * @return void
     *
     * @covers \MetaModels\NoteList\Storage\ValueBag::remove()
     * @covers \MetaModels\NoteList\Storage\ValueBag::require()
     */
    public function testRemoveWorksProperly()
    {
        $bag = new ValueBag(['key' => 'value']);

        $this->assertSame($bag, $bag->remove('key'));
        $this->assertFalse($bag->has('key'));
    }

    /**
     * Test the remove method
     *
     * @return void
     *
     * @covers \MetaModels\NoteList\Storage\ValueBag::remove()
     * @covers \MetaModels\NoteList\Storage\ValueBag::require()
     */
    public function testRemoveUnknownThrowsException()
    {
        $bag = new ValueBag([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The value "unkown" does not exist');

        $bag->remove('unkown');
    }

    /**
     * Test the getIterator() method.
     *
     * @return void
     *
     * @covers \MetaModels\NoteList\Storage\ValueBag::getIterator()
     */
    public function testGetIteratorReturnsIterator()
    {
        $bag = new ValueBag(['a' => 1, 'b' => 2]);

        $iterator = $bag->getIterator();

        $this->assertInstanceOf(\ArrayIterator::class, $iterator);
        $this->assertSame(['a' => 1, 'b' => 2], iterator_to_array($iterator));
    }

    /**
     * Test the getArrayCopy() method.
     *
     * @return void
     *
     * @covers \MetaModels\NoteList\Storage\ValueBag::getArrayCopy()
     */
    public function testGetArrayCopyReturnsArray()
    {
        $bag = new ValueBag(['a' => 1, 'b' => 2]);

        $this->assertSame(['a' => 1, 'b' => 2], $bag->getArrayCopy());
    }

    /**
     * Test the count() method.
     *
     * @return void
     *
     * @covers \MetaModels\NoteList\Storage\ValueBag::count()
     */
    public function testCountReturnsCorrectValue()
    {
        $bag = new ValueBag(['a' => 1, 'b' => 2]);

        $this->assertSame(2, $bag->count());
    }
}
