<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Shopware
 * @subpackage Shopware
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

// Check the minimum required php version
if (version_compare(PHP_VERSION, '5.3.2', '<')) {
    header('Content-type: text/html; charset=utf-8', true, 503);

    echo '<h2>Fehler</h2>';
    echo 'Auf Ihrem Server läuft PHP version ' . PHP_VERSION . ', Shopware 4 benötigt mindestens PHP 5.3.2';

    echo '<h2>Error</h2>';
    echo 'Your server is running PHP version ' . PHP_VERSION . ' but Shopware 4 requires at least PHP 5.3.2';
    return;
}

// Check the database config
if (file_exists('config.php') && strpos(file_get_contents('config.php'), '%db.database%') !== false) {
    header('Content-type: text/html; charset=utf-8', true, 503);

    echo '<h2>Fehler</h2>';
    echo 'Shopware 4 muss zunächst konfiguriert werden. Bitte führen Sie den Installer unter /install/ aus!';

    echo '<h2>Error</h2>';
    echo 'Shopware 4 must be configured first. Please run the installer under /install/!';
    return;
}

// Check for update-script
if (is_dir('update')) {
    header('Content-type: text/html; charset=utf-8', true, 503);
    header('Status: 503 Service Temporarily Unavailable');
    header('Retry-After: 1200');
    echo file_get_contents(__DIR__ . '/update/maintenance.html');
    return;
}

// check for composer autoloader
if (!file_exists('vendor/autoload.php')) {
    header('Content-type: text/html; charset=utf-8', true, 503);

    echo '<h2>Fehler</h2>';
    echo 'Bitte führen Sie zuerst "composer install" aus.';

    echo '<h2>Error</h2>';
    echo 'Please execute "composer install" install';
    return;
}

set_include_path(
    '.' . PATH_SEPARATOR .
    dirname(__FILE__) . '/engine/Library/' . PATH_SEPARATOR .   // Library
    dirname(__FILE__) . '/engine/' . PATH_SEPARATOR .           // Shopware
    dirname(__FILE__) . '/templates/'                           // Templates
);

// include composer autoloader
require 'vendor/autoload.php';
require 'Shopware/Application.php';
require 'Shopware/Kernel.php';

use Shopware\Kernel;
use Shopware\Components\HttpCache\AppCache;
use Symfony\Component\HttpFoundation\Request;

$environment = getenv('ENV') ? getenv("ENV") : getenv("REDIRECT_ENV");
if (empty($environment)) {
    $environment = 'production';
}

$kernel = new Kernel($environment, $environment !== 'production');

//@TODO implement new cache-switch mechanism here
if (false) {
    $kernel = new AppCache($kernel, array());
}

$request = Request::createFromGlobals();
$kernel->handle($request)
       ->send();
