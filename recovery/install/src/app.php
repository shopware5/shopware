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

use Pimple\Container;
use Shopware\Recovery\Common\Utils;
use Shopware\Recovery\Install\ContainerProvider;
use Shopware\Recovery\Install\DatabaseFactory;
use Shopware\Recovery\Install\MenuHelper;
use Shopware\Recovery\Install\Requirements;
use Shopware\Recovery\Install\RequirementsPath;
use Shopware\Recovery\Install\Service\AdminService;
use Shopware\Recovery\Install\Service\ConfigWriter;
use Shopware\Recovery\Install\Service\CurrencyService;
use Shopware\Recovery\Install\Service\DatabaseService;
use Shopware\Recovery\Install\Service\LicenseInstaller;
use Shopware\Recovery\Install\Service\LocaleSettingsService;
use Shopware\Recovery\Install\Service\LocalLicenseUnpackService;
use Shopware\Recovery\Install\Service\ShopService;
use Shopware\Recovery\Install\Service\ThemeService;
use Shopware\Recovery\Install\Service\TranslationService;
use Shopware\Recovery\Install\Struct\DatabaseConnectionInformation;
use Shopware\Recovery\Install\Struct\LicenseUnpackRequest;

$config = require __DIR__ . '/../config/production.php';
$container = new Container();
$container->register(new ContainerProvider($config));

/** @var \Slim\Slim $app */
$app = $container->offsetGet('slim.app');

// After instantiation
$sessionPath = str_replace('index.php', '', $app->request()->getScriptName());
$app->config('cookies.path', $sessionPath);

if (!isset($_SESSION)) {
    session_cache_limiter(false);
    session_set_cookie_params(600, $sessionPath);
    session_start();
}

if (!isset($_SESSION['parameters'])) {
    $_SESSION['parameters'] = [];
}

if (isset($_SESSION['databaseConnectionInfo'])) {
    $connectionInfo = $_SESSION['databaseConnectionInfo'];

    try {
        $databaseFactory = new DatabaseFactory();
        $connection = $databaseFactory->createPDOConnection($connectionInfo);

        // Init db in container
        $container->offsetSet('db', $connection);
    } catch (\Exception $e) {
        // Jump to form
        throw $e;
    }
}

/**
 * @return array|string
 */
function selectLanguage(array $allowedLanguages)
{
    /**
     * Load language file
     */
    $selectedLanguage = 'de';
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $selectedLanguage = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        $selectedLanguage = strtolower(substr($selectedLanguage[0], 0, 2));
    }
    if (empty($selectedLanguage) || !in_array($selectedLanguage, $allowedLanguages)) {
        $selectedLanguage = 'de';
    }

    if (isset($_REQUEST['language']) && in_array($_REQUEST['language'], $allowedLanguages)) {
        $selectedLanguage = $_REQUEST['language'];
        unset($_SESSION['parameters']['c_config_shop_language'],
            $_SESSION['parameters']['c_config_shop_currency'],
            $_SESSION['parameters']['c_config_admin_language']);
        $_SESSION['language'] = $selectedLanguage;

        return $selectedLanguage;
    } elseif (isset($_SESSION['language']) && in_array($_SESSION['language'], $allowedLanguages)) {
        $selectedLanguage = $_SESSION['language'];

        return $selectedLanguage;
    }
    $_SESSION['language'] = $selectedLanguage;

    return $selectedLanguage;
}

/**
 * @param string $language
 *
 * @return string
 */
function localeForLanguage($language)
{
    switch (strtolower($language)) {
        case 'de':
            return 'de_DE';
        case 'en':
            return 'en_GB';
        case 'nl':
            return 'nl_NL';
        case 'it':
            return 'it_IT';
        case 'fr':
            return 'fr_FR';
        case 'es':
            return 'es_ES';
        case 'pt':
            return 'pt_PT';
        case 'pl':
            return 'pl_PL';
    }

    return strtolower($language) . '_' . strtoupper($language);
}

function prefixSessionVars(\Slim\Slim $app)
{
    // Save post parameters starting with 'c_' to session
    $params = $app->request()->params();
    foreach ($params as $key => $value) {
        if (strpos($key, 'c_') !== false) {
            $_SESSION['parameters'][$key] = $value;
        }
    }
}

prefixSessionVars($app);
$selectedLanguage = selectLanguage($container->offsetGet('config')['languages']);
$translations = require __DIR__ . "/../data/lang/$selectedLanguage.php";

$container->offsetSet('translations', $translations);

/** @var TranslationService $translationService */
$translationService = $container->offsetGet('translation.service');

$container->offsetSet('install.language', $selectedLanguage);

/** @var MenuHelper $helper */
$menuHelper = $container->offsetGet('menu.helper');

// Set global variables
$app->view()->setData('version', $container->offsetGet('shopware.version'));
$app->view()->setData('t', $translationService);
$app->view()->setData('menuHelper', $menuHelper);
$app->view()->setData('selectedLanguage', $selectedLanguage);
$app->view()->setData('translations', $translations);
$app->view()->setData('baseUrl', Utils::getBaseUrl($app));
$app->view()->setData('app', $app);
$app->view()->setData('error', false);
$app->view()->setData('parameters', $_SESSION['parameters']);

$app->setCookie('installed-locale', localeForLanguage($selectedLanguage), time() + 7200, '/');

$app->error(function (\Exception $e) use ($app) {
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
    $response->header('Content-Type', 'application/json');
    $response->body(json_encode($data));
});

$app->map('/', function () use ($app, $container, $menuHelper) {
    $menuHelper->setCurrent('language-selection');

    $container['shopware.notify']->doTrackEvent('Installer started');

    $app->view()->set('languages', $container->offsetGet('config')['languages']);

    $app->render('/language-selection.php');
})->via('GET', 'POST')->name('language-selection');

$app->map('/license', function () use ($app, $menuHelper, $container) {
    $menuHelper->setCurrent('license');

    if ($app->request()->isPost()) {
        if ($app->request->post('tos')) {
            $app->redirect($menuHelper->getNextUrl());

            return;
        }

        $app->view()->set('error', true);
    }

    $tosUrls = $container->offsetGet('config')['tos.urls'];
    $tosUrl = $tosUrls['en'];

    if (array_key_exists($container->offsetGet('install.language'), $tosUrls)) {
        $tosUrl = $tosUrls[$container->offsetGet('install.language')];
    }

    $app->view()->setData('tosUrl', $tosUrl);

    $app->render('/license.php');
})->via('GET', 'POST')->name('license');

$app->map('/requirements/', function () use ($app, $container, $menuHelper) {
    $menuHelper->setCurrent('requirements');

    // Check system requirements
    /** @var Requirements $shopwareSystemCheck */
    $shopwareSystemCheck = $container->offsetGet('install.requirements');
    $systemCheckResults = $shopwareSystemCheck->toArray();

    $app->view()->setData('warning', (bool) $systemCheckResults['hasWarnings']);
    $app->view()->setData('error', (bool) $systemCheckResults['hasErrors']);
    $app->view()->setData('systemError', (bool) $systemCheckResults['hasErrors']);
    $app->view()->setData('phpVersionNotSupported', $systemCheckResults['phpVersionNotSupported']);

    // Check file & directory permissions
    /** @var RequirementsPath $shopwareSystemCheckPath */
    $shopwareSystemCheckPath = $container->offsetGet('install.requirementsPath');
    $shopwareSystemCheckPathResult = $shopwareSystemCheckPath->check();

    $app->view()->setData('pathError', false);

    if ($shopwareSystemCheckPathResult->hasError()) {
        $app->view()->setData('error', true);
        $app->view()->setData('pathError', true);
    }

    if ($app->request()->isPost() && $app->view()->getData('error') == false) {
        // No errors and submitted form - proceed with next-step
        $app->redirect($menuHelper->getNextUrl());
    }

    $app->render('/requirements.php', [
        'systemCheckResults' => $systemCheckResults['checks'],
        'systemCheckResultsWritePermissions' => $shopwareSystemCheckPathResult->toArray(),
    ]);
})->name('requirements')->via('GET', 'POST');

$app->map('/database-configuration/', function () use ($app, $container, $menuHelper) {
    $menuHelper->setCurrent('database-configuration');

    if (!$app->request()->isPost()) {
        $app->render('database-configuration.php');

        return;
    }

    // Initiate database object
    $databaseParameters = [
        'user' => isset($_SESSION['parameters']['c_database_user']) ? $_SESSION['parameters']['c_database_user'] : '',
        'password' => isset($_SESSION['parameters']['c_database_password']) ? $_SESSION['parameters']['c_database_password'] : '',
        'host' => isset($_SESSION['parameters']['c_database_host']) ? $_SESSION['parameters']['c_database_host'] : '',
        'port' => isset($_SESSION['parameters']['c_database_port']) ? $_SESSION['parameters']['c_database_port'] : '',
        'socket' => isset($_SESSION['parameters']['c_database_socket']) ? $_SESSION['parameters']['c_database_socket'] : '',
        'database' => isset($_SESSION['parameters']['c_database_schema']) ? $_SESSION['parameters']['c_database_schema'] : '',
    ];

    if (empty($databaseParameters['user'])
        || empty($databaseParameters['host'])
        || empty($databaseParameters['port'])
        || empty($databaseParameters['database'])
    ) {
        $app->render('database-configuration.php', ['error' => 'Please fill in all fields']);

        return;
    }

    $connectionInfo = new DatabaseConnectionInformation();
    $connectionInfo->username = $databaseParameters['user'];
    $connectionInfo->hostname = $databaseParameters['host'];
    $connectionInfo->port = $databaseParameters['port'];
    $connectionInfo->databaseName = $databaseParameters['database'];
    $connectionInfo->password = $databaseParameters['password'];

    try {
        $databaseFactory = new DatabaseFactory();
        $databaseFactory->createPDOConnection($connectionInfo); // check connection
    } catch (\Exception $e) {
        $app->render('database-configuration.php', ['error' => $e->getMessage()]);

        return;
    }

    $_SESSION['databaseConnectionInfo'] = $connectionInfo;

    try {
        /** @var ConfigWriter $configWriter */
        $configWriter = $container->offsetGet('config.writer');
        $configWriter->writeConfig($connectionInfo);
    } catch (\Exception $e) {
        $app->render('database-configuration.php', ['error' => $e->getMessage()]);

        return;
    }

    // Redirect to next step - (everything seems to be okay)
    $app->redirect($menuHelper->getNextUrl());
})->name('database-configuration')->via('GET', 'POST');

$app->map('/database-import/', function () use ($app, $container, $menuHelper) {
    $menuHelper->setCurrent('database-import');

    if ($app->request()->isPost()) {
        $app->redirect($menuHelper->getNextUrl());
    }

    try {
        /** @var \PDO $connection */
        $connection = $container->offsetGet('db');
    } catch (\Exception $e) {
        $menuHelper->setCurrent('database-configuration');
        $app->render(
            'database-configuration.php',
            ['error' => 'Please fill in all fields']
        );

        return;
    }

    try {
        $connection->query('SELECT * FROM s_schema_version')->fetchAll();
        $hasSchema = true;
    } catch (\Exception $e) {
        $hasSchema = false;
    }

    $app->view()->set('hasSchema', $hasSchema);
    $app->render('database-import.php');
})->name('database-import')->via('GET', 'POST');

$app->map('/edition/', function () use ($app, $translations, $container, $menuHelper, $translationService) {
    $menuHelper->setCurrent('edition');

    try {
        $container->offsetGet('db');
    } catch (\Exception $e) {
        $menuHelper->setCurrent('database-configuration');
        $app->render('database-configuration.php', ['error' => 'Please fill in all fields']);

        return;
    }

    /** @var LocalLicenseUnpackService $licenseUnpackService */
    $licenseUnpackService = $container->offsetGet('license.service');

    if ($app->request()->isPost()) {
        if ($app->request()->post('c_edition') == 'ce') {
            // If ce-edition continue with installation
            $app->redirect($menuHelper->getNextUrl());
        }

        // If PE/EE/EEC check license
        if (!$app->request()->post('c_license')) {
            $app->view()->setData('error', $translations['edition_license_error']);
        } else {
            $unpackRequest = new LicenseUnpackRequest(
                $app->request()->post('c_license'),
                $_SERVER['HTTP_HOST']
            );

            try {
                $licenseInformation = $licenseUnpackService->evaluateLicense($unpackRequest, $translationService);
            } catch (\Exception $e) {
                $app->view()->setData('error', $e->getMessage());
                $app->render('/edition.php');

                return;
            }

            /** @var LicenseInstaller $licenseInstaller */
            $licenseInstaller = $container->offsetGet('license.installer');
            $licenseInstaller->installLicense($licenseInformation);

            $app->redirect($menuHelper->getNextUrl());
        }
    }

    if (empty($_SESSION['parameters']['c_edition'])) {
        $_SESSION['parameters']['c_edition'] = 'ce';
    }
    if (empty($_SESSION['parameters']['c_license'])) {
        $_SESSION['parameters']['c_license'] = '';
    }

    $app->view()->setData('parameters', $_SESSION['parameters']);
    $app->render('/edition.php', []);
})->name('edition')->via('GET', 'POST');

$app->map('/configuration/', function () use ($app, $translationService, $container, $menuHelper) {
    $menuHelper->setCurrent('configuration');

    try {
        $db = $container->offsetGet('db');
    } catch (\Exception $e) {
        $menuHelper->setCurrent('database-configuration');
        $app->render('database-configuration.php', ['error' => 'Please fill in all fields']);

        return;
    }

    if ($app->request()->isPost()) {
        $adminUser = new \Shopware\Recovery\Install\Struct\AdminUser([
            'email' => $_SESSION['parameters']['c_config_admin_email'],
            'username' => $_SESSION['parameters']['c_config_admin_username'],
            'name' => $_SESSION['parameters']['c_config_admin_name'],
            'password' => $_SESSION['parameters']['c_config_admin_password'],
            'locale' => localeForLanguage($_SESSION['language']),
        ]);

        $shop = new \Shopware\Recovery\Install\Struct\Shop([
            'name' => $_SESSION['parameters']['c_config_shopName'],
            'locale' => $_SESSION['parameters']['c_config_shop_language'],
            'currency' => $_SESSION['parameters']['c_config_shop_currency'],
            'email' => $_SESSION['parameters']['c_config_mail'],
            'host' => $_SERVER['HTTP_HOST'],
            'basePath' => str_replace('/recovery/install/index.php', '', $_SERVER['SCRIPT_NAME']),
        ]);
        $locale = $_SESSION['parameters']['c_config_shop_language'] ?: 'de_DE';

        $shopService = new ShopService($db, $container['uniqueid.generator']);
        $currencyService = new CurrencyService($db);
        $adminService = new AdminService($db);
        $localeSettingsService = new LocaleSettingsService($db, $container);

        $hasErrors = false;
        try {
            $adminService->createAdmin($adminUser);
            $adminService->addWidgets($adminUser);
            $shopService->updateShop($shop);
            $currencyService->updateCurrency($shop);
            $shopService->updateConfig($shop);
            $localeSettingsService->updateLocaleSettings($locale);
        } catch (\Exception $e) {
            $hasErrors = true;
            $app->view()->setData('error', $e->getMessage());
        }

        if (!$hasErrors) {
            $app->redirect($app->urlFor('finalize'));
        }
    }

    $domain = $_SERVER['HTTP_HOST'];
    $basepath = str_replace('/recovery/install/index.php', '', $_SERVER['SCRIPT_NAME']);
    // Load shop-url
    $app->view()->setData('shop', ['domain' => $domain, 'basepath' => $basepath]);

    if (empty($_SESSION['parameters']['c_config_shop_language'])) {
        $_SESSION['parameters']['c_config_shop_language'] = $translationService->translate('locale');
    }
    if (empty($_SESSION['parameters']['c_config_shop_currency'])) {
        $_SESSION['parameters']['c_config_shop_currency'] = $translationService->translate('currency');
    }
    if (empty($_SESSION['parameters']['c_config_admin_language'])) {
        $_SESSION['parameters']['c_config_admin_language'] = $translationService->translate('locale');
    }

    $app->view()->setData('parameters', $_SESSION['parameters']);
    $app->render('/configuration.php', []);
})->name('configuration')->via('GET', 'POST');

$app->map('/finalize/', function () use ($app, $container) {
    /** @var ThemeService $themeService */
    $themeService = $container->offsetGet('theme.service');
    $themeService->activateResponsiveTheme();

    $app->redirect($app->urlFor('finish'));
})->name('finalize')->via('GET', 'POST');

$app->map('/finish/', function () use ($app, $menuHelper, $container) {
    $menuHelper->setCurrent('finish');

    $basepath = str_replace('/recovery/install/index.php', '', $_SERVER['SCRIPT_NAME']);

    /** @var \Shopware\Recovery\Common\SystemLocker $systemLocker */
    $systemLocker = $container->offsetGet('system.locker');
    $systemLocker();

    $container['uniqueid.persister']->store();

    $additionalInformation = [
        'language' => $container->offsetGet('install.language'),
        'method' => 'installer',
    ];

    $container->offsetGet('shopware.notify')->doTrackEvent('Installer finished', $additionalInformation);

    $schema = 'http';
    // This is for supporting Apache 2.2
    if (array_key_exists('HTTPS', $_SERVER) && strtolower($_SERVER['HTTPS']) === 'on') {
        $schema = 'https';
    }
    if (array_key_exists('REQUEST_SCHEME', $_SERVER)) {
        $schema = $_SERVER['REQUEST_SCHEME'];
    }

    $app->render('finish.php', ['url' => $schema . '://' . $_SERVER['HTTP_HOST'] . $basepath]);
})->name('finish')->via('GET', 'POST');

$app->map('/database-import/importDatabase', function () use ($app, $container) {
    $response = $app->response();
    $request = $app->request();
    $response->header('Content-Type', 'application/json');
    $response->status(200);

    /** @var \PDO $db */
    $db = $container->offsetGet('db');

    /** @var \Shopware\Recovery\Common\DumpIterator $dump */
    $dump = $container->offsetGet('database.dump_iterator');

    $offset = (int) $request->get('offset', 0);
    $totalCount = (int) $request->get('totalCount', 0);

    if ($offset === 0) {
        $totalCount = $dump->count();
    }

    // How many queries should be executed per http request?
    $batchSize = 100;

    /** @var Shopware\Recovery\Install\Service\DatabaseService $databaseService */
    $databaseService = $container->offsetGet('database.service');

    // For end users, we hide the error if we can not create the database or alter it.
    try {
        $databaseService->createDatabase($_SESSION['parameters']['c_database_schema']);
    } catch (\Exception $e) {
    }

    $preSql = <<<'EOD'
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
';
EOD;

    $db->query($preSql);
    $dump->seek($offset);

    foreach (range(0, $batchSize - 1) as $count) {
        $sql = trim($dump->current());

        if (empty($sql)) {
            continue;
        }

        try {
            $db->query($sql);
        } catch (PDOException $e) {
            $data = [
                'query' => $sql,
                'success' => false,
                'offset' => $offset,
                'errorMsg' => $e->getMessage(),
            ];

            $response->body(json_encode($data));

            return;
        }

        $dump->next();
    }

    $data = [
        'valid' => $dump->valid(),
        'offset' => $dump->key(),
        'totalCount' => $totalCount,
        'success' => true,
    ];

    $response->body(json_encode($data));
})->via('GET', 'POST')->name('applyMigrations');

$app->map('/database-import/importSnippets', function () use ($app, $container) {
    $response = $app->response();
    $response->header('Content-Type', 'application/json');
    $response->status(200);

    /** @var \Shopware\Recovery\Common\DumpIterator $dump */
    $dump = $container->offsetGet('database.snippet_dump_iterator');
    $offset = (int) $app->request()->get('offset', 0);
    $totalCount = (int) $app->request()->get('totalCount', 0);

    if ($offset === 0) {
        $totalCount = $dump->count();
    }

    /** @var \PDO $conn */
    $conn = $container->offsetGet('db');

    $preSql = '
       SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
       SET time_zone = "+00:00";
       SET @locale_de_DE = (SELECT id FROM s_core_locales WHERE locale = "de_DE");
       SET @locale_en_GB = (SELECT id FROM s_core_locales WHERE locale = "en_GB");
    ';

    $conn->query($preSql);
    $dump->seek($offset);

    $sql = [];
    $batchSize = 50;
    foreach (range(0, $batchSize - 1) as $count) {
        $current = trim($dump->current());

        if (empty($current)) {
            continue;
        }

        try {
            $conn->exec($current);
            $dump->next();
        } catch (\PDOException $e) {
            $data = [
                'query' => $sql,
                'success' => false,
                'offset' => $offset,
                'errorMsg' => $e->getMessage(),
            ];

            $response->body(json_encode($data));

            return;
        }
    }

    $data = [
        'valid' => $dump->valid(),
        'offset' => $dump->key(),
        'totalCount' => $totalCount,
        'success' => true,
    ];

    $response->body(json_encode($data));
})->via('GET', 'POST')->name('applySnippets');

$app->post('/check-database-connection', function () use ($container, $app) {
    $request = $app->request();
    $response = $app->response();

    $connectionInfo = new DatabaseConnectionInformation([
        'username' => $request->post('c_database_user'),
        'hostname' => $request->post('c_database_host'),
        'port' => $request->post('c_database_port'),
        'password' => $request->post('c_database_password'),
        'socket' => $request->post('c_database_socket'),
    ]);

    try {
        $databaseFactory = new DatabaseFactory();
        $connection = $databaseFactory->createPDOConnection($connectionInfo);
    } catch (\Exception $e) {
        $response->header('Content-Type', 'application/json');
        $response->status(200);
        $response->body(json_encode([]));

        return;
    }

    // Init db in container
    $container->offsetSet('db', $connection);

    /** @var DatabaseService $databaseService */
    $databaseService = $container->offsetGet('database.service');

    // No need for listing the following schemas
    $omitSchemas = ['information_schema', 'performance_schema', 'sys', 'mysql'];
    $databaseNames = $databaseService->getSchemas($omitSchemas);

    $result = [];
    foreach ($databaseNames as $databaseName) {
        $result[] = [
            'value' => $databaseName,
            'display' => $databaseName,
        ];
    }

    $response->header('Content-Type', 'application/json');
    $response->status(200);
    $response->body(json_encode($result));
})->name('database');

return $app;
