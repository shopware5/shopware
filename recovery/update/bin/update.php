#!/usr/bin/env php
<?php
set_time_limit(0);
ini_set('display_errors', 1);
error_reporting(-1);

require_once __DIR__ . '/../../common/autoload.php';


define('UPDATE_PATH', __DIR__ . '/../../update');
$isManual = is_dir(SW_PATH . '/update-assets');
if ($isManual) {
    define('UPDATE_IS_MANUAL', true);
    define('UPDATE_FILES_PATH', null);
    define('UPDATE_ASSET_PATH', SW_PATH . '/update-assets');
} else {
    define('UPDATE_IS_MANUAL', false);
    define('UPDATE_FILES_PATH', SW_PATH . '/files/update/files');
    define('UPDATE_ASSET_PATH', SW_PATH . '/files/update/update-assets');
}

use Symfony\Component\Console\Input\ArgvInput;

$input = new ArgvInput();

$env = getenv('ENV') ?: getenv('REDIRECT_ENV') ?: 'production';
$env = $input->getParameterOption(array('--env', '-e'), $env);

$application = new Shopware\Recovery\Update\Console\Application($env);
$application->run($input);
