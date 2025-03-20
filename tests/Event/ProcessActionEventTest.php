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

namespace MetaModels\NoteListBundle\Test\Event;

use MetaModels\IMetaModel;
use MetaModels\NoteListBundle\Event\ProcessActionEvent;
use MetaModels\NoteListBundle\Storage\NoteListStorage;
use MetaModels\NoteListBundle\Test\TestCase;

/**
 * This tests the process action event.
 *
 * @covers \MetaModels\NoteListBundle\Event\ProcessActionEvent
 */
class ProcessActionEventTest extends TestCase
{
    /**
     * Test all getters.
     *
     * @return void
     */
    public function testGetters()
    {
        $action    = 'some-action';
        $payload   = ['key1' => 'value', 'key2' => 42];
        $noteList  = $this->getMockBuilder(NoteListStorage::class)->disableOriginalConstructor()->getMock();
        $metaModel = $this->getMockForAbstractClass(IMetaModel::class);

        $event = new ProcessActionEvent($action, $payload, $noteList, $metaModel);

        $this->assertSame($action, $event->getAction());
        $this->assertSame($payload, $event->getPayload());
        $this->assertSame(array_keys($payload), $event->getPayloadKeys());
        foreach ($payload as $key => $value) {
            $this->assertTrue($event->hasPayloadValue($key));
            $this->assertSame($value, $event->getPayloadValue($key));
        }
        $this->assertSame($noteList, $event->getNoteList());
        $this->assertSame($metaModel, $event->getMetaModel());
        $this->assertFalse($event->isSuccess());
        $this->assertSame($event, $event->setSuccess());
        $this->assertTrue($event->isSuccess());
    }
}
