<?php

// set default timezone, if not configured fallback to UTC
date_default_timezone_set(@date_default_timezone_get());

define('SW_PATH', realpath(__DIR__ . '/../../'));

/** @var \Composer\Autoload\ClassLoader $autoloader */
$autoloader = require_once __DIR__ . '/vendor/autoload.php';

$autoloader->addPsr4(
    'Shopware\\Components\\Migrations\\',
    SW_PATH . '/engine/Shopware/Components/Migrations/'
);

return $autoloader;
