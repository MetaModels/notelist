<?php

/**
 * This file is part of MetaModels/notelist.
 *
 * (c) 2017 - 2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2017 - 2018 The MetaModels team.
 * @license    https://github.com/MetaModels/notelist/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

declare(strict_types = 1);

namespace MetaModels\NoteListBundle\Bridge;

use Doctrine\DBAL\Connection;
use MetaModels\IFactory;
use MetaModels\MetaModelsServiceContainer;
use MetaModels\NoteListBundle\Event\ProcessActionEvent;
use MetaModels\NoteListBundle\NoteListFactory;

/**
 * This class handles data after send form.
 */
class ProcessFormDataBridge
{
    /**
     * Retrieve all form data and check if set clear notelist.
     *
     * @param array $submitted The submitted data.
     * @param array $data      All data.
     * @param array $files     The submitted files.
     * @param array $labels    The form labels.
     * @param array $formData  The form data.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function clearNotelistFormData($submitted, $data, $files, $labels, $formData)
    {
        $container  = \Contao\System::getContainer()->get('metamodels-notelist.bridge-locator');
        $connection = $container->get(Connection::class);
        /** @var Connection $connection */
        $notelistFormWidgets = $connection
            ->createQueryBuilder()
            ->select('*')
            ->from('tl_form_field')
            ->where('pid=:pid')->setParameter('pid', $formData->id)
            ->andWhere('type=:type')->setParameter('type', 'metamodel_notelist')
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($notelistFormWidgets as $notelistFormWidget) {
            $metaModelId        = $notelistFormWidget['metamodel'];
            $metaModelNotelists = deserialize($notelistFormWidget['metamodel_notelist'], true);

            /** @var IFactory $factory */
            $factory = $container->get(IFactory::class);
            /** @var NoteListFactory $noteListFactory */
            $noteListFactory = $container->get(NoteListFactory::class);

            $metaModel = $factory->getMetaModel($factory->translateIdToMetaModelName($metaModelId));

            foreach (is_array($metaModelNotelists) ? $metaModelNotelists : [] as $metaModelNotelist) {
                if ($metaModelNotelist['clearlist'] && $metaModelNotelist['notelist']) {
                    $event = new ProcessActionEvent(
                        'clear',
                        [],
                        $noteListFactory->getList($metaModel, $metaModelNotelist['notelist']),
                        $metaModel
                    );
                    $event->getNoteList()->clear();
                    $event->setSuccess();
                }
            }
        }
    }
}
