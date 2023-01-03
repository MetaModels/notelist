<?php

/**
 * This file is part of MetaModels/notelist.
 *
 * (c) 2017-2023 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2017-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/notelist/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

declare(strict_types = 1);

namespace MetaModels\NoteListBundle\EventListener\DcGeneral;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use MetaModels\NoteListBundle\Storage\StorageAdapterFactory;

/**
 * This class provides the list of registered storage adapters for the backend.
 */
class AdapterListListener
{
    /**
     * The adapter factory.
     *
     * @var StorageAdapterFactory
     */
    private $factory;

    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Create a new instance.
     *
     * @param StorageAdapterFactory $factory    The adapter factory.
     *
     * @param TranslatorInterface   $translator The translator.
     */
    public function __construct(StorageAdapterFactory $factory, TranslatorInterface $translator)
    {
        $this->factory    = $factory;
        $this->translator = $translator;
    }

    /**
     * Retrieve the list of adapters.
     *
     * @param GetPropertyOptionsEvent $event The event to process.
     *
     * @return void
     */
    public function getAdapterListOptions(GetPropertyOptionsEvent $event)
    {
        if (null !== $event->getOptions()) {
            return;
        }

        if (('storageAdapter' !== $event->getPropertyName())
            || ('tl_metamodel_notelist' !== $event->getEnvironment()->getDataDefinition()->getName())) {
            return;
        }

        $adapters = $this->factory->getIdentifiers();
        $result   = [];
        foreach ($adapters as $adapter) {
            $result[$adapter] = $this->translator->translate('metamodels_notelist.adapter.' . $adapter, 'metamodels_notelist');
        }

        $event->setOptions($result);
    }
}
