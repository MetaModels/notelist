<?php

/**
 * This file is part of MetaModels/notelist.
 *
 * (c) 2017-2025 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2017-2025 The MetaModels team.
 * @license    https://github.com/MetaModels/notelist/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\NoteListBundle\Test\Storage;

use Contao\Session;
use MetaModels\NoteListBundle\Storage\ContaoSessionAdapter;
use PHPUnit\Framework\TestCase;

/**
 * This tests the ContaoSessionAdapter class.
 */
class ContaoSessionAdapterTest extends TestCase
{
    /**
     * Test that the class can be instantiated.
     *
     * @return void
     */
    public function testInstantiation()
    {
        $session = $this
            ->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $adapter = new ContaoSessionAdapter($session);

        $this->assertInstanceOf('MetaModels\NoteList\Storage\ContaoSessionAdapter', $adapter);
    }

    /**
     * Test that getting of values works.
     *
     * @return void
     */
    public function testGetter()
    {
        $session = $this
            ->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $session
            ->expects($this->once())
            ->method('get')
            ->with('metamodel_notelists')
            ->willReturn(['metamodel_notelist_foo' => ['bar']]);

        $adapter = new ContaoSessionAdapter($session);

        $this->assertEquals(['bar'], $adapter->getKey('foo'));
    }

    /**
     * Test that getting unknown keys returns an empty array.
     *
     * @return void
     */
    public function testGetterReturnsEmptyForUnknown()
    {
        $session = $this
            ->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $session
            ->expects($this->once())
            ->method('get')
            ->with('metamodel_notelists')
            ->willReturn([]);

        $adapter = new ContaoSessionAdapter($session);

        $this->assertEquals([], $adapter->getKey('unknown'));
    }

    /**
     * Test that setting of values works.
     *
     * @return void
     */
    public function testSetter()
    {
        $session = $this
            ->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['set', 'get'])
            ->getMock();

        $session
            ->expects($this->once())
            ->method('set')
            ->with('metamodel_notelists')
            ->willReturn(['metamodel_notelist_foo' => ['bar']]);
        $session
            ->expects($this->once())
            ->method('get')
            ->with('metamodel_notelists')
            ->willReturn([]);

        $adapter = new ContaoSessionAdapter($session);

        $adapter->setKey('foo', ['bar']);
    }
}
