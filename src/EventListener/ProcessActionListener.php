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

namespace MetaModels\NoteListBundle\EventListener;

use MetaModels\IItem;
use MetaModels\NoteList\NoteListBundle\ProcessActionEvent;

/**
 * This handles the normal events like add, remove and clear.
 */
class ProcessActionListener
{
    /**
     * Handle the event.
     *
     * @param ProcessActionEvent $event The event.
     *
     * @return void
     */
    public function handleEvent(ProcessActionEvent $event)
    {
        switch ($event->getAction()) {
            case 'add':
                $payload = $event->getPayload();
                unset($payload['item']);
                $event->getNoteList()->add($this->getItemFromMetaModel($event), $payload);
                $event->setSuccess();
                return;
            case 'remove':
                $event->getNoteList()->remove($this->getItemFromMetaModel($event));
                $event->setSuccess();
                return;
            case 'clear':
                $event->getNoteList()->clear();
                $event->setSuccess();
                return;
            default:
        }
    }

    /**
     * Retrieve an item from the MetaModel.
     *
     * @param ProcessActionEvent $event The event.
     *
     * @return IItem
     *
     * @throws \InvalidArgumentException When the item could not be found.
     */
    private function getItemFromMetaModel(ProcessActionEvent $event)
    {
        $item = $event->getMetaModel()->findById($event->getPayloadValue('item'));
        if (null === $item) {
            throw new \InvalidArgumentException('Item ' . $event->getPayloadValue('item') . ' could not be found.');
        }

        return $item;
    }
}
