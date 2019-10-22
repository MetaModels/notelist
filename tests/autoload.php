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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2017-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/notelist/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

require_once __DIR__ . '/../vendor/autoload.php';

spl_autoload_register(function ($class) {
    if (substr($class, 0, 7) === 'Contao\\') {
        return null;
    }

    if (class_exists('Contao\\' . $class)
        || interface_exists('Contao\\' . $class)
        || trait_exists('Contao\\' . $class)
    ) {
        class_alias('Contao\\' . $class, $class);
        return true;
    }

    return null;
});
