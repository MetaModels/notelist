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

declare(strict_types=1);

namespace MetaModels\NoteListBundle\Test;

use MetaModels\NoteListBundle\Bridge\DcaCallbackBridge;
use MetaModels\NoteListBundle\Bridge\FormFieldBridge;
use MetaModels\NoteListBundle\Bridge\InsertTagBridge;
use MetaModels\NoteListBundle\Bridge\ProcessFormDataBridge;
use MetaModels\NoteListBundle\Event\ManipulateNoteListEvent;
use MetaModels\NoteListBundle\Event\NoteListEvents;
use MetaModels\NoteListBundle\Event\ParseNoteListFormEvent;
use MetaModels\NoteListBundle\Event\ProcessActionEvent;
use MetaModels\NoteListBundle\EventListener\DcGeneral\AdapterListListener;
use MetaModels\NoteListBundle\EventListener\DcGeneral\BreadCrumbNoteList;
use MetaModels\NoteListBundle\EventListener\DcGeneral\BuildNoteListNameWidgetListener;
use MetaModels\NoteListBundle\EventListener\DcGeneral\FilterIdToMetaModelTrait;
use MetaModels\NoteListBundle\EventListener\DcGeneral\FilterSettingsListListener;
use MetaModels\NoteListBundle\EventListener\DcGeneral\FilterSettingTypeRenderer;
use MetaModels\NoteListBundle\EventListener\DcGeneral\FormListListener;
use MetaModels\NoteListBundle\EventListener\DcGeneral\NoteListListListener;
use MetaModels\NoteListBundle\EventListener\DcGeneral\RenderNoteListNameAsReadablePropertyValueListener;
use MetaModels\NoteListBundle\EventListener\ParseItemListener;
use MetaModels\NoteListBundle\EventListener\ProcessActionListener;
use MetaModels\NoteListBundle\Filter\NoteListFilterRule;
use MetaModels\NoteListBundle\Filter\NoteListFilterSetting;
use MetaModels\NoteListBundle\Filter\NoteListFilterSettingTypeFactory;
use MetaModels\NoteListBundle\Form\Form;
use MetaModels\NoteListBundle\Form\FormBuilder;
use MetaModels\NoteListBundle\Form\FormRenderer;
use MetaModels\NoteListBundle\InsertTags;
use MetaModels\NoteListBundle\NoteListFactory;
use MetaModels\NoteListBundle\Storage\AdapterInterface;
use MetaModels\NoteListBundle\Storage\ContaoSessionAdapter;
use MetaModels\NoteListBundle\Storage\Exception\AdapterNotFoundException;
use MetaModels\NoteListBundle\Storage\NoteListStorage;
use MetaModels\NoteListBundle\Storage\PhpSessionVariableAdapter;
use MetaModels\NoteListBundle\Storage\StorageAdapterFactory;
use MetaModels\NoteListBundle\Storage\ValueBag;
use PHPUnit\Framework\TestCase;

/**
 * This class tests if the deprecated autoloader works.
 *
 * @package MetaModels\AttributeUrlBundle\Test
 */
class DeprecatedAutoloaderTest extends TestCase
{
    /**
     * Urles of old classes to the new one.
     *
     * @var array
     */
    private static $classes = [
        'MetaModels\NoteList\Bridge\DcaCallbackBridge' => DcaCallbackBridge::class,
        'MetaModels\NoteList\Bridge\FormFieldBridge' => FormFieldBridge::class,
        'MetaModels\NoteList\Bridge\InsertTagBridge' => InsertTagBridge::class,
        'MetaModels\NoteList\Bridge\ProcessFormDataBridge' => ProcessFormDataBridge::class,
        'MetaModels\NoteList\Event\ManipulateNoteListEvent' => ManipulateNoteListEvent::class,
        'MetaModels\NoteList\Event\NoteListEvents' => NoteListEvents::class,
        'MetaModels\NoteList\Event\ParseNoteListFormEvent' => ParseNoteListFormEvent::class,
        'MetaModels\NoteList\Event\ProcessActionEvent' => ProcessActionEvent::class,
        'MetaModels\NoteList\EventListener\DcGeneral\AdapterListListener' => AdapterListListener::class,
        'MetaModels\NoteList\EventListener\DcGeneral\BreadCrumbNoteList' => BreadCrumbNoteList::class,
        'MetaModels\NoteList\EventListener\DcGeneral\BuildNoteListNameWidgetListener'
            => BuildNoteListNameWidgetListener::class,
        'MetaModels\NoteList\EventListener\DcGeneral\FilterIdToMetaModelTrait' => FilterIdToMetaModelTrait::class,
        'MetaModels\NoteList\EventListener\DcGeneral\FilterSettingsListListener'
            => FilterSettingsListListener::class,
        'MetaModels\NoteList\EventListener\DcGeneral\FilterSettingTypeRenderer' => FilterSettingTypeRenderer::class,
        'MetaModels\NoteList\EventListener\DcGeneral\FormListListener' => FormListListener::class,
        'MetaModels\NoteList\EventListener\DcGeneral\NoteListListListener' => NoteListListListener::class,
        'MetaModels\NoteList\EventListener\DcGeneral\RenderNoteListNameAsReadablePropertyValueListener'
            => RenderNoteListNameAsReadablePropertyValueListener::class,
        'MetaModels\NoteList\EventListener\ParseItemListener' => ParseItemListener::class,
        'MetaModels\NoteList\EventListener\ProcessActionListener' => ProcessActionListener::class,
        'MetaModels\NoteList\Filter\NoteListFilterRule' => NoteListFilterRule::class,
        'MetaModels\NoteList\Filter\NoteListFilterSetting' => NoteListFilterSetting::class,
        'MetaModels\NoteList\Filter\NoteListFilterSettingTypeFactory' => NoteListFilterSettingTypeFactory::class,
        'MetaModels\NoteList\Form\Form' => Form::class,
        'MetaModels\NoteList\Form\FormBuilder' => FormBuilder::class,
        'MetaModels\NoteList\Form\FormRenderer' => FormRenderer::class,
        'MetaModels\NoteList\Storage\Exception\AdapterNotFoundException' => AdapterNotFoundException::class,
        'MetaModels\NoteList\Storage\AdapterInterface' => AdapterInterface::class,
        'MetaModels\NoteList\Storage\ContaoSessionAdapter' => ContaoSessionAdapter::class,
        'MetaModels\NoteList\Storage\NoteListStorage' => NoteListStorage::class,
        'MetaModels\NoteList\Storage\PhpSessionVariableAdapter' => PhpSessionVariableAdapter::class,
        'MetaModels\NoteList\Storage\StorageAdapterFactory' => StorageAdapterFactory::class,
        'MetaModels\NoteList\Storage\ValueBag' => ValueBag::class,
        'MetaModels\NoteList\InsertTags' => InsertTags::class,
        'MetaModels\NoteList\NoteListFactory' => NoteListFactory::class,
    ];

    /**
     * Provide the alias class map.
     *
     * @return array
     */
    public function provideAliasClassMap()
    {
        $values = [];

        foreach (static::$classes as $url => $class) {
            $values[] = [$url, $class];
        }

        return $values;
    }

    /**
     * Test if the deprecated classes are aliased to the new one.
     *
     * @param string $oldClass Old class name.
     * @param string $newClass New class name.
     *
     * @dataProvider provideAliasClassMap
     */
    public function testDeprecatedClassesAreAliased($oldClass, $newClass)
    {
        $this->assertTrue(
            class_exists($oldClass) || interface_exists($oldClass) || trait_exists($oldClass),
            sprintf('Class/interface/trait "%s" is not found.', $oldClass)
        );

        $oldClassReflection = new \ReflectionClass($oldClass);
        $newClassReflection = new \ReflectionClass($newClass);

        $this->assertSame($newClassReflection->getFileName(), $oldClassReflection->getFileName());
    }
}
