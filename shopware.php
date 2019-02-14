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
if (PHP_VERSION_ID < 70200) {
    header('Content-type: text/html; charset=utf-8', true, 503);

    echo '<h2>Error</h2>';
    echo 'Your server is running PHP version ' . PHP_VERSION . ' but Shopware 5 requires at least PHP 7.2.0';

    echo '<h2>Fehler</h2>';
    echo 'Auf Ihrem Server läuft PHP version ' . PHP_VERSION . ', Shopware 5 benötigt mindestens PHP 7.2.0';

    return;
}

// Check for active auto update or manual update
if (is_file('files/update/update.json') || is_dir('update-assets')) {
    header('Content-type: text/html; charset=utf-8', true, 503);
    header('Status: 503 Service Temporarily Unavailable');
    header('Retry-After: 1200');
    if (file_exists(__DIR__ . '/maintenance.html')) {
        echo file_get_contents(__DIR__ . '/maintenance.html');
    } else {
        echo file_get_contents(__DIR__ . '/recovery/update/maintenance.html');
    }

    return;
}

// Check for installation
if (is_dir('recovery/install') && !is_file('recovery/install/data/install.lock')) {
    if (PHP_SAPI === 'cli') {
        echo 'Shopware 5 must be configured before use. Please run the Shopware installer by executing \'php recovery/install/index.php\'.' . PHP_EOL;
    } else {
        $basePath = 'recovery/install';
        $baseURL = str_replace(basename(__FILE__), '', $_SERVER['SCRIPT_NAME']);
        $baseURL = rtrim($baseURL, '/');
        $installerURL = $baseURL . '/' . $basePath;

        if (strpos($_SERVER['REQUEST_URI'], $basePath) === false) {
            header('Location: ' . $installerURL);
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

// Check for composer autoloader
if (!file_exists('vendor/autoload.php')) {
    $template = '%s: ';
    if (PHP_SAPI !== 'cli') {
        $template = '<h2>%s</h2>';
        header('Content-type: text/html; charset=utf-8', true, 503);
    }

    echo sprintf($template, 'Error');
    echo "Please execute \"composer install\" from the command line to install the required dependencies for Shopware 5\n";

    echo sprintf($template, 'Fehler');
    echo "Bitte führen Sie zuerst \"composer install\" aus um alle von Shopware 5 benötigten Abhängigkeiten zu installieren.\n";

    exit(1);
}

require __DIR__ . '/autoload.php';

use Shopware\Components\HttpCache\AppCache;
use Shopware\Kernel;
use Symfony\Component\HttpFoundation\Request;

$environment = getenv('SHOPWARE_ENV') ?: getenv('REDIRECT_SHOPWARE_ENV') ?: 'production';

$kernel = new Kernel($environment, $environment !== 'production');
if ($kernel->isHttpCacheEnabled()) {
    $kernel = new AppCache($kernel, $kernel->getHttpCacheConfig());
}

// Set commandline args as request uri
// This is used for legacy cronjob routing.
// e.g: /usr/bin/php shopware.php /backend/cron
if (PHP_SAPI === 'cli' && isset($_SERVER['argv'][1])) {
    $_SERVER['REQUEST_URI'] = $_SERVER['argv'][1];
    // We have to use a shutdown function to prevent "headers already sent" errors.
    register_shutdown_function(function () {
        echo PHP_EOL;
        echo 'WARNING: Executing shopware.php via CLI is deprecated. Please use the command line tool in bin/console instead.' . PHP_EOL;
    });
}

$request = Request::createFromGlobals();

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
