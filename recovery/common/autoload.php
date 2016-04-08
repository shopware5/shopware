<?php
define('SW_PATH', realpath(__DIR__ . '/../../'));

/** @var \Composer\Autoload\ClassLoader $autoloader */
$autoloader = require_once __DIR__ . '/vendor/autoload.php';

$autoloader->addPsr4(
    'Shopware\\Components\\Migrations\\',
    SW_PATH . '/engine/Shopware/Components/Migrations/'
);

return $autoloader;
