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

require_once __DIR__ . '/../common/autoload.php';

define('UPDATE_PATH', __DIR__);
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

use Shopware\Recovery\Update\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;

if (PHP_SAPI === 'cli') {
    error_reporting(-1);
    ini_set('display_errors', 1);

    $input = new ArgvInput();
    $env = $input->getParameterOption(['--env', '-e'], 'production');

    $application = new Application($env);

    return $application->run($input);
}

$app = require __DIR__ . '/src/app.php';
$app->run();
