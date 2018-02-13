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

/** @var \DependencyInjection\Container\PimpleGate $container */
$container->provideSymfonyService('metamodels-notelist.storage-factory');
$container->provideSymfonyService('metamodels-notelist.factory');
$container->provideSymfonyService('metamodels-notelist.form-builder');
$container->provideSymfonyService('metamodels-notelist.filter-setting-factory');
$container->provideSymfonyService('metamodels-notelist.parse-item-listener');
$container->provideSymfonyService('metamodels-notelist.process-action-listener');
$container->provideSymfonyService('metamodels-notelist.insert-tags');

/*
 * Backend from here on.
 */

$container->provideSymfonyService('metamodels-notelist.backend.adapter-option-listener');
$container->provideSymfonyService('metamodels-notelist.backend.notelist-list-option-listener');
$container->provideSymfonyService('metamodels-notelist.backend.notelist-form-option-listener');
$container->provideSymfonyService('metamodels-notelist.backend.filter-settings-option-listener');
$container->provideSymfonyService('metamodels-notelist.backend.breadcrumb-renderer');
$container->provideSymfonyService('metamodels-notelist.backend.prepare-name-widget');
$container->provideSymfonyService('metamodels-notelist.backend.render-name-widget');
$container->provideSymfonyService('metamodels-notelist.backend.filter-setting-type-renderer');
