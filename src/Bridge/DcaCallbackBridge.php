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

namespace MetaModels\NoteListBundle\Bridge;

use Contao\DataContainer;
use MetaModels\BackendIntegration\TemplateList;
use MetaModels\IFactory;
use MetaModels\MetaModelsServiceContainer;
use MetaModels\NoteListBundle\NoteListFactory;
use MultiColumnWizard;
use Pimple;

/**
 * This class bridges Contao callbacks to proper listeners.
 */
class DcaCallbackBridge
{
    /**
     * Retrieve the note list options for the current tl_content or tl_module entry.
     *
     * @param DataContainer $dataContainer The current data container.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function getNoteListOptions(DataContainer $dataContainer)
    {
        $container       = \Contao\System::getContainer();
        $factory         = $container->get('metamodels.factory');
        $noteListFactory = $container->get(NoteListFactory::class);

        $metaModelId = $dataContainer->activeRecord->metamodel;
        $metaModel   = $factory->getMetaModel($factory->translateIdToMetaModelName($metaModelId));
        if (!$metaModel) {
            return [];
        }
        return $noteListFactory->getConfiguredListsFor($metaModel);
    }

    /**
     * Retrieve the options
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function getMetaModelOptions()
    {
        /** @var Pimple $container */
        $container = $GLOBALS['container'];
        /** @var IFactory $factory */
        $factory = $container['metamodels-factory.factory'];

        $metaModels = $factory->collectNames();

        $result = [];
        foreach ($metaModels as $metaModel) {
            $instance = $factory->getMetaModel($metaModel);

            $result[$instance->get('id')] = $instance->getName();
        }

        return $result;
    }

    /**
     * Initialize the options from MCW.
     *
     * @param MultiColumnWizard $wizard The wizard.
     *
     * @return string[]
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function getNoteListOptionsMcw(MultiColumnWizard $wizard)
    {
        /** @var Pimple $container */
        $container = $GLOBALS['container'];
        /** @var MetaModelsServiceContainer $metaModelContainer */
        $metaModelContainer = $container['metamodels-service-container'];
        /** @var IFactory $factory */
        $factory = $metaModelContainer->getFactory();
        /** @var NoteListFactory $noteListFactory */
        $noteListFactory = $container['metamodels-notelist.factory'];

        $metaModelId = $wizard->activeRecord->metamodel;
        $metaModel   = $factory->getMetaModel($factory->translateIdToMetaModelName($metaModelId));
        if (!$metaModel) {
            return [];
        }
        return $noteListFactory->getConfiguredListsFor($metaModel);
    }

    /**
     * Fetch all available render settings for the current meta model.
     *
     * @param MultiColumnWizard $wizard The wizard.
     *
     * @return string[] array of all attributes as id => human name
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getRenderSettingsMcw(MultiColumnWizard $wizard)
    {
        /** @var Pimple $container */
        $container = $GLOBALS['container'];

        $renderSettings = $container['database.connection']
            ->prepare('SELECT * FROM tl_metamodel_rendersettings WHERE pid=?')
            ->execute($wizard->activeRecord->metamodel);

        $result = array();
        while ($renderSettings->next()) {
            $result[$renderSettings->id] = $renderSettings->name;
        }

        // Sort the render settings.
        asort($result);
        return $result;
    }

    /**
     * Fetch the template group for the form field.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getEmailTemplates()
    {
        $list = \Contao\System::getContainer()->get('metamodels.template_list');

        return $list->getTemplatesForBase('email_metamodels_notelist');
    }
}
