<?php

/**
 * This file is part of MetaModels/notelist.
 *
 * (c) 2017-2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2018 The MetaModels team.
 * @license    https://github.com/MetaModels/notelist/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

declare(strict_types = 1);

namespace MetaModels\NoteListBundle\Test\DependencyInjection;

use MetaModels\NoteListBundle\DependencyInjection\MetaModelsNoteListExtension;
use MetaModels\NoteListBundle\Filter\NoteListFilterSettingTypeFactory;
use MetaModels\NoteListBundle\Form\FormBuilder;
use MetaModels\NoteListBundle\InsertTags;
use MetaModels\NoteListBundle\NoteListFactory;
use MetaModels\NoteListBundle\Storage\StorageAdapterFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * This test case test the extension.
 */
class MetaModelsNoteListExtensionTest extends TestCase
{
    /**
     * Test that extension can be instantiated.
     *
     * @return void
     */
    public function testInstantiation()
    {
        $extension = new MetaModelsNoteListExtension();

        $this->assertInstanceOf(MetaModelsNoteListExtension::class, $extension);
        $this->assertInstanceOf(ExtensionInterface::class, $extension);
    }

    /**
     * Data provider to test the services are properly aliased.
     *
     * @return array
     */
    public function serviceAliasProvider()
    {
        return [
            [StorageAdapterFactory::class, 'metamodels-notelist.storage-factory'],
            [NoteListFactory::class, 'metamodels-notelist.factory'],
            [FormBuilder::class, 'metamodels-notelist.form-builder'],
            [NoteListFilterSettingTypeFactory::class, 'metamodels-notelist.filter-setting-factory'],
            [InsertTags::class, 'metamodels-notelist.insert-tags'],
            // [],
        ];
    }

    /**
     * Test that the services are loaded and aliased.
     *
     * @param string $serviceClass
     * @param string $serviceAlias
     *
     * @return void
     *
     * @dataProvider serviceAliasProvider
     */
    public function testFactoryIsRegistered(string $serviceClass, string $serviceAlias)
    {
        $container = new ContainerBuilder();

        $extension = new MetaModelsNoteListExtension();
        $extension->load([], $container);

        $this->assertAliased($container, $serviceClass, $serviceAlias);
    }

    /**
     * Assert that the service has been properly defined and aliased.
     *
     * @param ContainerBuilder $container The container.
     * @param string           $service   The service name.
     * @param string           $alias     The service alias.
     *
     * @return void
     */
    private function assertAliased(ContainerBuilder $container, string $service, string $alias)
    {
        $this->assertTrue($container->has($service), 'Service not defined: ' . $service);
        $this->assertTrue($container->hasAlias($alias), 'Alias not defined: ' . $alias);
        $serviceDef = $container->getDefinition($service);
        $aliasDef   = $container->getAlias($alias);
        $this->assertTrue($serviceDef->isPrivate(), 'Service is not private: ' . $service);
        $this->assertFalse($aliasDef->isPrivate(), 'Alias is private: ' . $alias);
        $this->assertSame($service, (string) $aliasDef, 'Alias ' . $alias . ' does not map to ' . $service);
    }
}
