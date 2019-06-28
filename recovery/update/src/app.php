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

use Shopware\Recovery\Update\DependencyInjection\Container;
use Shopware\Recovery\Update\PluginCheck;
use Shopware\Recovery\Update\Utils;

date_default_timezone_set('Europe/Berlin');
ini_set('display_errors', 1);
error_reporting(-1);

$config = require __DIR__ . '/../config/config.php';
$container = new Container(new \Pimple\Container(), $config);

/** @var \Slim\Slim $app */
$app = $container->get('app');

$app->hook('slim.before.dispatch', function () use ($app, $container) {
    $baseUrl = \Shopware\Recovery\Common\Utils::getBaseUrl($app);

    $lang = null;
    if (!UPDATE_IS_MANUAL) {
        if (!is_file(UPDATE_META_FILE)) {
            $shopPath = str_replace('/recovery/update', '/', $app->request()->getRootUri());
            $app->response()->redirect($shopPath);
            $app->response()->status(302);
            $app->stop();
        }

        $file = file_get_contents(UPDATE_META_FILE);
        $updateConfig = json_decode($file, true);
        $container->setParameter('update.config', $updateConfig);
        $lang = substr($updateConfig['locale'], 0, 2);
    }

    session_set_cookie_params(7200, $baseUrl);

    // Silence errors during session start, Work around session_start(): ps_files_cleanup_dir: opendir(/var/lib/php5) failed: Permission denied (13)
    @session_start();
    @set_time_limit(0);

    $selectedLanguage = Utils::getLanguage($app->request, $lang);
    $language = require __DIR__ . "/../data/lang/$selectedLanguage.php";

    $clientIp = Utils::getRealIpAddr();

    $app->view()->set('version', $container->get('shopware.version'));
    $app->view()->set('app', $app);
    $app->view()->set('clientIp', $clientIp);
    $app->view()->set('baseUrl', $baseUrl);
    $app->view()->set('language', $language);
    $app->view()->set('selectedLanguage', $selectedLanguage);

    $ipCheckEnabled = (bool) $app->config('check.ip');
    if ($ipCheckEnabled && !Utils::isAllowed($clientIp)) {
        $app->view()->setData('filePath', UPDATE_PATH . '/allowed_ip.txt');

        $app->render('noaccess.php');
        $app->response()->status(403);
        $app->stop();
    }

    // Redirect to "done" page if file cleanup was done
    if (false && isset($_SESSION['CLEANUP_DONE']) && $app->router()->getCurrentRoute()->getName() !== 'done') {
        $url = $app->urlFor('done');
        $app->response()->redirect($url);
    } elseif (isset($_SESSION['DB_DONE']) && !isset($_SESSION['CLEANUP_DONE']) && $app->router()->getCurrentRoute()->getName() !== 'cleanup') {
        $url = $app->urlFor('cleanup');
        $app->response()->redirect($url);
    }
});

$app->error(function (Exception $e) use ($app) {
    if (!$app->request()->isAjax()) {
        throw $e;
    }

    $response = $app->response();
    $data = [
        'code' => $e->getCode(),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
    ];
    $response['Content-Type'] = 'application/json';
    $response->body(json_encode($data));
});

$app->map('/noaccess', function () use ($app) {
    $app->view()->setData('filePath', UPDATE_PATH . '/allowed_ip.txt');

    $app->render('noaccess.php');
    $app->response()->status(403);
})->via('GET', 'POST')->name('noAccess');

$app->map('/', function () use ($app) {
    $app->render('welcome.php');

    if (!UPDATE_IS_MANUAL) {
        $app->redirect($app->urlFor('checks'));

        return;
    }
})->via('GET', 'POST')->name('welcome');

// Check file & directory permissions
$app->map('/checks', function () use ($app, $container) {
    $container->get('controller.requirements')->checkRequirements();
})->via('GET', 'POST')->name('checks');

$app->map('/plugin-checks', function () use ($app, $container) {
    /** @var PluginCheck $pluginCheck */
    $pluginCheck = $container->get('plugin.check');
    $plugins = $pluginCheck->checkPlugins();

    $app->render('plugins.php', ['plugins' => $plugins]);
})->via('GET', 'POST')->name('plugin-checks');

$app->map('/dbmigration', function () use ($app) {
    $app->render('dbmigration.php');
})->via('GET', 'POST')->name('dbmigration');

$app->map('/applyMigrations', function () use ($app, $container) {
    $container->get('controller.batch')->applyMigrations();
})->via('GET', 'POST')->name('applyMigrations');

$app->map('/importSnippets', function () use ($container) {
    $container->get('controller.batch')->importSnippets();
})->via('GET', 'POST')->name('importSnippets');

$app->map('/unpack', function () use ($container) {
    $container->get('controller.batch')->unpack();
})->via('GET', 'POST')->name('unpack');

$app->map('/cleanup', function () use ($container) {
    $container->get('controller.cleanup')->cleanupOldFiles();
})->via('GET', 'POST')->name('cleanup');

$app->map('/clearCache', function () use ($container) {
    $container->get('controller.cleanup')->deleteOutdatedFolders();
})->via('GET', 'POST')->name('clearCache');

$app->map('/done', function () use ($app, $container) {
    $container->get('shopware.update.chmod')->changePermissions();

    /** @var \Shopware\Components\Theme\Installer $themeService */
    $themeService = $container->get('shopware.theme_installer');
    $themeService->synchronize();

    if (is_dir(SW_PATH . '/recovery/install')) {
        /** @var \Shopware\Recovery\Common\SystemLocker $systemLocker */
        $systemLocker = $container->get('system.locker');
        $systemLocker();
    }

    $changedTheme = (bool) !empty($_SESSION['changedTheme']) ?: false;
    $app->view()->set('changedTheme', $changedTheme);

    if (UPDATE_IS_MANUAL) {
        $app->render('done_manual.php');
    } else {
        $app->render('done.php');
    }
})->via('GET', 'POST')->name('done');

$app->get('/redirect/:target', function ($target) use ($app) {
    unlink(UPDATE_META_FILE);

    $url = str_replace('/index.php', '', $app->request()->getRootUri());
    $url = str_replace('/recovery/update', '/', $url);
    if ($target == 'backend') {
        $url .= 'backend';
    }

    session_destroy();
    $app->response()->redirect($url);
})->name('redirect');

return $app;
