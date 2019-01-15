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
use Doctrine\DBAL\Connection;
use MetaModels\BackendIntegration\TemplateList;
use MetaModels\IFactory;
use MetaModels\NoteListBundle\NoteListFactory;
use MenAtWork\MultiColumnWizardBundle\Contao\Widgets\MultiColumnWizard;

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
     */
    public static function getNoteListOptions(DataContainer $dataContainer)
    {
        $container       = self::getLocator();
        $factory         = $container->get(IFactory::class);
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
     */
    public static function getMetaModelOptions()
    {
        $container = self::getLocator();
        $factory   = $container->get(IFactory::class);

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
     */
    public static function getNoteListOptionsMcw(MultiColumnWizard $wizard)
    {
        $container       = self::getLocator();
        $factory         = $container->get(IFactory::class);
        $noteListFactory = $container->get(NoteListFactory::class);

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
     */
    public function getRenderSettingsMcw(MultiColumnWizard $wizard)
    {
        /** @var Connection $database */
        $database = $this->getLocator()->get(Connection::class);

        $renderSettings = $database
            ->createQueryBuilder()
            ->select('*')
            ->from('tl_metamodel_rendersettings')
            ->where('pid=:pid')
            ->setParameter('pid', $wizard->activeRecord->metamodel)
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);

        $result = array();
        foreach ($renderSettings as $renderSetting) {
            $result[$renderSetting['id']] = $renderSetting['name'];
        }

        // Sort the render settings.
        asort($result);
        return $result;
    }

    /**
     * Fetch the template group for the form field.
     *
     * @return array
     */
    public function getEmailTemplates()
    {
        $list = $this->getLocator()->get(TemplateList::class);

        return $list->getTemplatesForBase('email_metamodels_notelist');
    }

    /**
     * Obtain the service locator.
     *
     * @return \Symfony\Component\DependencyInjection\ServiceLocator
     */
    private static function getLocator()
    {
        return \Contao\System::getContainer()->get('metamodels-notelist.bridge-locator');
    }
}
