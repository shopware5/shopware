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

// Check the minimum required php version
if (version_compare(PHP_VERSION, '5.4.0', '<')) {
    header('Content-type: text/html; charset=utf-8', true, 503);

    echo '<h2>Error</h2>';
    echo 'Your server is running PHP version ' . PHP_VERSION . ' but Shopware 5 requires at least PHP 5.4.0';

    echo '<h2>Fehler</h2>';
    echo 'Auf Ihrem Server läuft PHP version ' . PHP_VERSION . ', Shopware 5 benötigt mindestens PHP 5.4.0';

    return;
}

// Check for active auto update or manual update
if (is_file('files/update/update.json') || is_dir('update-assets')) {
    header('Content-type: text/html; charset=utf-8', true, 503);
    header('Status: 503 Service Temporarily Unavailable');
    header('Retry-After: 1200');
    echo file_get_contents(__DIR__ . '/recovery/update/maintenance.html');
    return;
}

// Check for installation
if (is_dir('recovery/install') && !is_file('recovery/install/data/install.lock')) {
    if (PHP_SAPI == 'cli') {
        echo 'Shopware 5 must be configured before use. Please run the Shopware installer by executing \'php recovery/install/index.php\'.'.PHP_EOL;
    } else {
        $basePath = 'recovery/install';
        $baseURL = str_replace(basename(__FILE__), '', $_SERVER['SCRIPT_NAME']);
        $baseURL = rtrim($baseURL, '/');
        $installerURL = $baseURL.'/'.$basePath;

        if (strpos($_SERVER['REQUEST_URI'], $basePath) === false) {
            header('Location: '.$installerURL);
            exit;
        }

        header('Content-type: text/html; charset=utf-8', true, 503);

        echo '<h2>Error</h2>';
        echo 'Shopware 5 must be configured before use. Please run the <a href="recovery/install/?language=en">installer</a>.';

        echo '<h2>Fehler</h2>';
        echo 'Shopware 5 muss zunächst konfiguriert werden. Bitte führen Sie den <a href="recovery/install/?language=de">Installer</a> aus.';
    }
    exit;
}

// check for composer autoloader
if (!file_exists('vendor/autoload.php')) {
    header('Content-type: text/html; charset=utf-8', true, 503);

    echo '<h2>Error</h2>';
    echo 'Please execute "composer install" from the command line to install the required dependencies for Shopware 5';

    echo '<h2>Fehler</h2>';
    echo 'Bitte führen Sie zuerst "composer install" aus.';

    return;
}

require __DIR__ . '/autoload.php';

use Shopware\Kernel;
use Shopware\Components\HttpCache\AppCache;
use Symfony\Component\HttpFoundation\Request;

$environment = getenv('SHOPWARE_ENV') ?: getenv('REDIRECT_SHOPWARE_ENV') ?: 'production';

$kernel = new Kernel($environment, $environment !== 'production');
if ($kernel->isHttpCacheEnabled()) {
    $kernel = new AppCache($kernel, $kernel->getHttpCacheConfig());
}

$request = Request::createFromGlobals();

$kernel->handle($request)
       ->send();
