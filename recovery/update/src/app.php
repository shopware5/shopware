<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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
use Shopware\Recovery\Update\Utils;

date_default_timezone_set('Europe/Berlin');

ini_set('display_errors', 1);
error_reporting(-1);

$config = require __DIR__ . '/../config/config.php';
$container = new Container(new Pimple(), $config);

/** @var \Slim\Slim $app */
$app = $container->get('app');

$app->hook('slim.before.dispatch', function () use ($app, $container) {
    $baseUrl = \Shopware\Recovery\Common\Utils::getBaseUrl($app);

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

    session_set_cookie_params(7200, $baseUrl);

    //Silence errors during session start, Work around session_start(): ps_files_cleanup_dir: opendir(/var/lib/php5) failed: Permission denied (13)
    @session_start();
    @set_time_limit(0);

    $selectedLanguage = Utils::getLanguage($app->request, $lang);
    $language = require __DIR__ .  "/../data/lang/$selectedLanguage.php";
    $_SESSION["language"] = $language;

    $clientIp = Utils::getRealIpAddr();

    $app->view()->set('version', $updateConfig['version']);
    $app->view()->set('app', $app);
    $app->view()->set('app', $app);
    $app->view()->set('clientIp', $clientIp);
    $app->view()->set('baseUrl', $baseUrl);
    $app->view()->set('language', $language);
    $app->view()->set('selectedLanguage', $selectedLanguage);

    $ipCheckEnabled = (bool) $app->config('check.ip');
    if ($ipCheckEnabled && !Utils::isAllowed($clientIp)) {
        $app->view()->setData('filePath', UPDATE_PATH . '/' . 'allowed_ip.txt');

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
    $data = array(
        'code'    => $e->getCode(),
        'message' => $e->getMessage(),
        'file'    => $e->getFile(),
        'line'    => $e->getLine(),
        'trace'   => $e->getTraceAsString(),
    );
    $response['Content-Type'] = 'application/json';
    $response->body(json_encode($data));
});

$app->map('/noaccess', function () use ($app) {
    $app->view()->setData('filePath', UPDATE_PATH . '/' . 'allowed_ip.txt');

    $app->render('noaccess.php');
    $app->response()->status(403);

})->via('GET', 'POST')->name("noAccess");

$app->map('/', function () use ($app) {
    $app->render('welcome.php');

    if (isset($_SESSION['language'])) {
        $app->redirect($app->urlFor("checks"));

        return;
    }
})->via('GET', 'POST')->name("welcome");

// Check file & directory permissions
$app->map('/checks', function () use ($app, $container) {
    $paths = Utils::getPaths(SW_PATH . "/engine/Shopware/Components/Check/Data/Path.xml");

    clearstatcache();
    $systemCheckPathResults = Utils::checkPaths($paths, SW_PATH);

    foreach ($systemCheckPathResults as $value) {
        if (!$value['result']) {
            $fileName = SW_PATH . '/' . $value['name'];
            @mkdir($fileName, 0777, true);
            @chmod($fileName, 0777);
        }
    }

    clearstatcache();
    $systemCheckPathResults = Utils::checkPaths($paths, SW_PATH);

    $hasErrors = false;
    foreach ($systemCheckPathResults as $value) {
        if (!$value['result']) {
            $hasErrors = true;
        }
    }

    $directoriesToDelete = array(
        'cache/proxies/'                   => false,
        'cache/doctrine/filecache/'	       => false,
        'cache/doctrine/proxies/'	       => false,
        'cache/doctrine/attributes/'       => false,
        'cache/general/'                   => false,
        'cache/templates/'                 => false,
        'engine/Library/Mpdf/tmp'          => false,
        'engine/Library/Mpdf/ttfontdata'   => false,
    );

    if (function_exists('apc_clear_cache')) {
        apc_clear_cache();
        apc_clear_cache('user');
    }

    $results = array();
    foreach ($directoriesToDelete as $directory => $deleteDirecory) {
        $result = true;
        $filePath = SW_PATH . '/' . $directory;

        Utils::deleteDir($filePath, $deleteDirecory);
        if ($deleteDirecory && is_dir($filePath)) {
            $result = false;
            $hasErrors = true;
        }

        if ($deleteDirecory) {
            $results[$directory] = $result;
        }
    }

    if (!$hasErrors && $app->request()->get("action")) {
        // No errors and submitted form - proceed with next-step
        $app->redirect($app->urlFor("dbmigration"));
    }

    if (!$hasErrors && $app->request()->get("force") !== "1") {
        // No errors, skip page except if force parameter is set
        $app->redirect($app->urlFor("dbmigration"));
    }

    $isSkippableCheck = $app->config('skippable.check');
    if ($isSkippableCheck && $app->request()->get("force") !== "1") {
        // No errors, skip page except if force parameter is set
        $app->redirect($app->urlFor("dbmigration"));
    }

    $app->render('checks.php', array(
        'systemCheckResultsWritePermissions' => $systemCheckPathResults,
        'filesToDelete'                      => $results,
        'error'                              => $hasErrors,
    ));
})->via('GET', 'POST')->name("checks");

$app->map('/dbmigration', function () use ($app) {
    $app->render('dbmigration.php');
})->via('GET', 'POST')->name('dbmigration');

$app->map('/applyMigrations', function () use ($app, $container) {
    $container->get('controller.batch')->applyMigrations();
})->via('GET', 'POST')->name('applyMigrations');

$app->map('/importSnippets', function () use ($app, $container) {
    $container->get('controller.batch')->importSnippets();
})->via('GET', 'POST')->name('importSnippets');

$app->map('/unpack', function () use ($app, $container) {
    $container->get('controller.batch')->unpack();
})->via('GET', 'POST')->name("unpack");

$app->map('/cleanup', function () use ($app) {
    $_SESSION['DB_DONE'] = true;

    $cleanupFile = UPDATE_ASSET_PATH . '/cleanup.php';
    if (!is_file($cleanupFile)) {
        $_SESSION['CLEANUP_DONE'] = true;

        $url = $app->urlFor('done');
        $app->response()->redirect($url);

        return;
    }

    $rawList = require $cleanupFile;
    $cleanupList = array();

    foreach ($rawList as $path) {
        $realpath = SW_PATH.'/'.$path;
        if (file_exists($realpath)) {
            $cleanupList[] = $path;
        }
    }

    if (count($cleanupList) == 0) {
        $_SESSION['CLEANUP_DONE'] = true;
        $url = $app->urlFor('done');
        $app->response()->redirect($url);
    }

    if ($app->request()->isPost()) {
        $result = array();
        foreach ($cleanupList as $path) {
            $result = array_merge($result, Utils::cleanPath(SW_PATH.'/'.$path));
        }

        if (count($result) == 0) {
            $_SESSION['CLEANUP_DONE'] = true;

            $url = $app->urlFor('done');
            $app->response()->redirect($url);
        } else {
            $result = array_map(function ($path) { return substr($path, strlen(__DIR__.'/../../')); }, $result);
            $app->render('cleanup.php', array('cleanupList' => $result, 'error' => true));
        }

    } else {
        $app->render('cleanup.php', array('cleanupList' => $cleanupList, 'error' => false ));
    }
})->via('GET', 'POST')->name('cleanup');

$app->map('/done', function () use ($app) {
    $app->render('done.php');
})->via('GET', 'POST')->name('done');

$app->get('/redirect/:target', function ($target) use ($app) {
    unlink(UPDATE_META_FILE);

    $url = str_replace('/index.php', '', $app->request()->getRootUri());
    $url = str_replace('/recovery/update', '/', $url);
    if ($target == 'backend') {
        $url = $url . 'backend';
    }

    session_destroy();
    $app->response()->redirect($url);

})->name('redirect');

return $app;
