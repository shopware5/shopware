<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    $template = '%s: ';
    if (PHP_SAPI !== 'cli') {
        $template = '<h2>%s</h2>';
        header('Content-type: text/html; charset=utf-8', true, 503);
    }

    echo sprintf($template, 'Error');
    echo 'Please execute "composer install --working-dir=' . __DIR__ . '" or "composer install" in the directory ' .
        __DIR__ . " from the command line to install the required dependencies for the install/update application for Shopware 5\n";

    echo sprintf($template, 'Fehler');
    echo 'Bitte führen Sie zuerst "composer install --working-dir=' . __DIR__ . '" oder "composer install" im Verzeichnis ' .
        __DIR__ . " aus um alle von der Shopware 5 Installations-/Aktualisierungsanwendung benötigten Abhängigkeiten zu installieren.\n";

    exit(1);
}
date_default_timezone_set(@date_default_timezone_get());

\define('SW_PATH', realpath(__DIR__ . '/../../'));

/** @var \Composer\Autoload\ClassLoader $autoloader */
$autoloader = require_once __DIR__ . '/vendor/autoload.php';

$autoloader->addPsr4(
    'Shopware\\Components\\Migrations\\',
    SW_PATH . '/engine/Shopware/Components/Migrations/'
);

return $autoloader;
