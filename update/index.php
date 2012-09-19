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

set_include_path(
    '.' . PATH_SEPARATOR .
    dirname(__FILE__) . '/libs/' . PATH_SEPARATOR .
    dirname(dirname(__FILE__)) . '/' . PATH_SEPARATOR
);

// Check active shopware 4 installation
if (file_exists('cache/templates/compile/')) {
    header('Content-type: text/html; charset=utf-8', true, 503);
    echo "<h4>Der Installer wurde bereits ausgeführt</h4>";
    echo "<p>Wenn Sie den Installationsvorgang erneut ausführen möchten, löschen Sie alle Dateien und Ordner unterhalb des Ordners cache/templates!</p>";
    echo "<h4>The installation process has already been finished.</h4>";
    echo "<p> If you want to run the installation process again, delete all the files and directories under the folder cache/templates!</p>";
    exit;
}

// Check the minimum required php version
if (version_compare(PHP_VERSION, '5.3.0', '<')) {
    header('Content-type: text/html; charset=utf-8', true, 503);
    echo '<h2>Fehler</h2>';
    echo 'Auf Ihrem Server läuft PHP version ' . PHP_VERSION . ', Shopware 4 benötigt mindestens PHP 5.3';
    echo '<h2>Error</h2>';
    echo 'Your server is running PHP version ' . PHP_VERSION . ' but Shopware 4 requires at least PHP 5.3';
    return;
}

require_once 'Slim/Slim.php';
require_once 'Shopware/Install.php';

$app = new Shopware_Install();
return $app->run();