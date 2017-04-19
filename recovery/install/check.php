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

error_reporting(-1);
ini_set('display_errors', true);

$tokenFile = __DIR__ . '/tmp/token';
$token = '';
if (is_readable($tokenFile)) {
    $token = file_get_contents($tokenFile);
}
$token = trim($token);

if (!$token
    || empty($token)
    || !isset($_GET['token'])
    || empty($_GET['token'])
    || $token != $_GET['token']
) {
    header('HTTP/1.1 403 Forbidden');
    echo 'Forbidden';
    exit;
}

$result = [
    'ioncubeLoader' => checkIonCubeLoader(),
    'phpversion' => phpversion(),
];

if (defined(JSON_PRETTY_PRINT)) {
    echo json_encode($result, JSON_PRETTY_PRINT);
} else {
    echo json_encode($result, JSON_PRETTY_PRINT);
}

/**
 * Checks the ion cube loader
 *
 * @return bool|string
 */
function checkIonCubeLoader()
{
    if (!extension_loaded('ionCube Loader')) {
        return false;
    }

    if (!function_exists('ioncube_loader_version')) {
        return false;
    }

    return ioncube_loader_version();
}
