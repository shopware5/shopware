<?php
// check for composer autoloader
if (!file_exists(__DIR__ . '/../common/autoload.php')) {
    header('Content-type: text/html; charset=utf-8', true, 503);

    echo '<h2>Error</h2>';
    echo 'Please execute "composer install" from the command line to install the required dependencies for Shopware 4';

    echo '<h2>Fehler</h2>';
    echo 'Bitte führen Sie zuerst "composer install" aus.';

    return;
}

$autoloader = require_once __DIR__ . '/../common/autoload.php';

define('UPDATE_PATH', __DIR__ . '/../update');
$isManual = is_dir(SW_PATH . '/update-assets');
if ($isManual) {
    define('UPDATE_IS_MANUAL', true);
    define('UPDATE_FILES_PATH', null);
    define('UPDATE_ASSET_PATH', SW_PATH . '/update-assets');
    define('UPDATE_META_FILE', null);
} else {
    define('UPDATE_IS_MANUAL', false);
    define('UPDATE_FILES_PATH', SW_PATH . '/files/update/files');
    define('UPDATE_ASSET_PATH', SW_PATH . '/files/update/update-assets');
    define('UPDATE_META_FILE', SW_PATH . '/files/update/update.json');
}

$app = require __DIR__ . '/src/app.php';
$app->run();
