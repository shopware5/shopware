<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

$lockFile = __DIR__ . '/data/install.lock';
if (is_file($lockFile)) {
    header('Content-type: text/html; charset=utf-8', true, 503);
    echo '<br /><h4>Der Installer wurde bereits ausgeführt.</h4>Wenn Sie den Installationsvorgang erneut ausführen möchten, löschen Sie die Datei recovery/install/data/install.lock!<br /><br /><br />';
    echo '<h4>The installation process has already been finished.</h4>If you want to run the installation process again, delete the file recovery/install/data/install.lock!';
    exit;
}

// Check the minimum required php version
if (PHP_VERSION_ID < 70200) {
    header('Content-type: text/html; charset=utf-8', true, 503);
    echo '<h2>Fehler</h2>';
    echo 'Auf Ihrem Server läuft PHP version ' . PHP_VERSION . ', Shopware 5 benötigt mindestens PHP 7.2.0.';
    echo '<h2>Error</h2>';
    echo 'Your server is running PHP version ' . PHP_VERSION . ' but Shopware 5 requires at least PHP 7.2.0.';
    exit;
}

require_once __DIR__ . '/../common/autoload.php';

error_reporting(-1);
ini_set('display_errors', 1);
date_default_timezone_set('UTC');
set_time_limit(0);

use Shopware\Recovery\Install\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;

if (PHP_SAPI === 'cli') {
    $input = new ArgvInput();
    $env = $input->getParameterOption(['--env', '-e'], 'production');

    $application = new Application($env);

    return $application->run($input);
}

//the execution time will be increased, because the import can take a while
ini_set('max_execution_time', 120);

// Redirect to no mod rewrite path
if (!isset($_SERVER['MOD_REWRITE']) && isset($_SERVER['SCRIPT_NAME']) && isset($_SERVER['REQUEST_URI'])) {
    if (empty($_SERVER['PATH_INFO']) && strpos($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']) !== 0) {
        header('Location: ' . $_SERVER['SCRIPT_NAME'], true);

        return;
    }
}

$app = require __DIR__ . '/src/app.php';
$app->run();
