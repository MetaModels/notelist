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

use MetaModels\NoteListBundle\Storage\PhpSessionVariableAdapter;
use PHPUnit\Framework\TestCase;

/**
 * This tests the PhpSessionVariableAdapter class.
 */
class PhpSessionVariableAdapterTest extends TestCase
{
    /**
     * Test that the class can be instantiated.
     *
     * @return void
     */
    public function testInstantiation()
    {
        $adapter = new PhpSessionVariableAdapter();

        $this->assertInstanceOf('MetaModels\NoteList\Storage\PhpSessionVariableAdapter', $adapter);
    }

    /**
     * Test that getting of values works.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function testGetter()
    {
        $_SESSION = ['metamodel_notelists' => ['metamodel_notelist_foo' => ['bar']]];

        $adapter = new PhpSessionVariableAdapter();

        $this->assertEquals(['bar'], $adapter->getKey('foo'));
    }

    /**
     * Test that getting unknown keys returns an empty array.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function testGetterReturnsEmptyForUnknown()
    {
        $_SESSION = [];

        $adapter = new PhpSessionVariableAdapter();

        $this->assertEquals([], $adapter->getKey('unknown'));
    }

    /**
     * Test that setting of values works.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function testSetter()
    {
        $_SESSION = [];

        $adapter = new PhpSessionVariableAdapter();

        $adapter->setKey('foo', ['bar']);

        $this->assertEquals(['bar'], $_SESSION['metamodel_notelists']['metamodel_notelist_foo']);
    }
}
