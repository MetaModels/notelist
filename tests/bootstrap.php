<?php

require __DIR__ . '/../vendor/autoload.php';

$projectRoot = dirname(__DIR__);

// Enable bypassing of readonly and final classes only in our own src.
DG\BypassFinals::allowPaths([$projectRoot . '/src/*']);
DG\BypassFinals::setCacheDirectory($projectRoot . '/.cache/bypass-final');
DG\BypassFinals::enable();
