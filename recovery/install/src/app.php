<?php
use Pimple\Container;
use Shopware\Recovery\Common\Utils;
use Shopware\Recovery\Install\ContainerProvider;
use Shopware\Recovery\Install\DatabaseFactory;
use Shopware\Recovery\Install\MenuHelper;
use Shopware\Recovery\Install\Requirements;
use Shopware\Recovery\Install\RequirementsPath;
use Shopware\Recovery\Install\Service\AdminService;
use Shopware\Recovery\Install\Service\ConfigWriter;
use Shopware\Recovery\Install\Service\DatabaseService;
use Shopware\Recovery\Install\Service\LicenseInstaller;
use Shopware\Recovery\Install\Service\LocaleSettingsService;
use Shopware\Recovery\Install\Service\LocalLicenseUnpackService;
use Shopware\Recovery\Install\Service\ShopService;
use Shopware\Recovery\Install\Service\CurrencyService;
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

if (!isset($_SESSION["parameters"])) {
    $_SESSION["parameters"] = [];
}

if (isset($_SESSION["databaseConnectionInfo"])) {
    $connectionInfo = $_SESSION["databaseConnectionInfo"];

    try {
        $databaseFactory = new DatabaseFactory();
        $connection = $databaseFactory->createPDOConnection($connectionInfo);

        // init db in container
        $container->offsetSet('db', $connection);
    } catch (\Exception $e) {
        // jump to form
        throw $e;
    }
}
/**
 * @return array|string
 */
function selectLanguage()
{
    /**
     * Load language file
     */
    $allowedLanguages = ["de", "en", "nl"];
    $selectedLanguage = "de";
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $selectedLanguage = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        $selectedLanguage = substr($selectedLanguage[0], 0, 2);
    }
    if (empty($selectedLanguage) || !in_array($selectedLanguage, $allowedLanguages)) {
        $selectedLanguage = "de";
    }

    if (isset($_REQUEST["language"]) && in_array($_REQUEST["language"], $allowedLanguages)) {
        $selectedLanguage = $_REQUEST["language"];
        unset($_SESSION["parameters"]["c_config_shop_language"]);
        unset($_SESSION["parameters"]["c_config_shop_currency"]);
        unset($_SESSION["parameters"]["c_config_admin_language"]);
        $_SESSION["language"] = $selectedLanguage;

        return $selectedLanguage;
    } elseif (isset($_SESSION["language"]) && in_array($_SESSION["language"], $allowedLanguages)) {
        $selectedLanguage = $_SESSION["language"];

        return $selectedLanguage;
    } else {
        $_SESSION["language"] = $selectedLanguage;

        return $selectedLanguage;
    }
}

/**
 * @param $app
 */
function prefixSessionVars(\Slim\Slim $app)
{
    // Save post parameters starting with "c_" to session
    $params = $app->request()->params();
    foreach ($params as $key => $value) {
        if (strpos($key, "c_") !== false) {
            $_SESSION["parameters"][$key] = $value;
        }
    }
}

prefixSessionVars($app);
$selectedLanguage = selectLanguage();
$translations = require __DIR__ . "/../data/lang/$selectedLanguage.php";

$container->offsetSet('translations', $translations);

/** @var $translationService TranslationService */
$translationService = $container->offsetGet('translation.service');

$container->offsetSet('install.language', $selectedLanguage);

/** @var $helper MenuHelper */
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
$app->view()->setData('parameters', $_SESSION["parameters"]);

$app->error(function (\Exception $e) use ($app) {
    if (!$app->request()->isAjax()) {
        throw $e;
    }

    $response = $app->response();
    $data = [
        'code'    => $e->getCode(),
        'message' => $e->getMessage(),
        'file'    => $e->getFile(),
        'line'    => $e->getLine(),
        'trace'   => $e->getTraceAsString(),
    ];
    $response->header('Content-Type', 'application/json');
    $response->body(json_encode($data));
});

$app->map('/', function () use ($app, $container, $menuHelper) {
    $menuHelper->setCurrent('language-selection');

    $app->view()->set('languages', ['de', 'en', 'nl']);

    $app->render('/language-selection.php');
})->via('GET', 'POST')->name('language-selection');

$app->map('/license', function () use ($app, $menuHelper, $container) {
    $menuHelper->setCurrent('license');

    if ($app->request()->isPost()) {
        if ($app->request->post('eula')) {
            $app->redirect($menuHelper->getNextUrl());

            return;
        }

        $app->view()->set('error', true);
    }

    if ($container->offsetGet('install.language') == 'de') {
        $eula = file_get_contents(SW_PATH . '/eula.txt');
    } else {
        $eula = file_get_contents(SW_PATH . '/eula_en.txt');
    }

    $app->view()->setData("eula", $eula);
    $app->render("/license.php");
})->via('GET', 'POST')->name("license");

$app->map('/requirements/', function () use ($app, $container, $menuHelper) {
    $menuHelper->setCurrent('requirements');

    // Check system requirements
    /** @var $shopwareSystemCheck Requirements */
    $shopwareSystemCheck = $container->offsetGet('install.requirements');
    $systemCheckResults  = $shopwareSystemCheck->toArray();

    $app->view()->setData('warning', (bool) $systemCheckResults['hasWarnings']);
    $app->view()->setData('error', (bool) $systemCheckResults['hasErrors']);

    // Check file & directory permissions
    /** @var $shopwareSystemCheckPath RequirementsPath */
    $shopwareSystemCheckPath = $container->offsetGet('install.requirementsPath');
    $shopwareSystemCheckPathResult = $shopwareSystemCheckPath->check();

    if ($shopwareSystemCheckPathResult->hasError()) {
        $app->view()->setData('error', true);
    }

    if ($app->request()->isPost() && $app->view()->getData('error') == false) {
        // No errors and submitted form - proceed with next-step
        $app->redirect($menuHelper->getNextUrl());
    }

    $app->render('/requirements.php', [
        'systemCheckResults' => $systemCheckResults['checks'],
        'systemCheckResultsWritePermissions' => $shopwareSystemCheckPathResult->toArray()
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
        "user"          => isset($_SESSION["parameters"]["c_database_user"])     ? $_SESSION["parameters"]["c_database_user"] : "",
        "password"      => isset($_SESSION["parameters"]["c_database_password"]) ? $_SESSION["parameters"]["c_database_password"] : "",
        "host"          => isset($_SESSION["parameters"]["c_database_host"])     ? $_SESSION["parameters"]["c_database_host"] : "",
        "port"          => isset($_SESSION["parameters"]["c_database_port"])     ? $_SESSION["parameters"]["c_database_port"] : "",
        "socket"        => isset($_SESSION["parameters"]["c_database_socket"])   ? $_SESSION["parameters"]["c_database_socket"] : "",
        "database"      => isset($_SESSION["parameters"]["c_database_schema"])   ? $_SESSION["parameters"]["c_database_schema"] : "",
    ];

    if (empty($databaseParameters["user"])
        || empty($databaseParameters["host"])
        || empty($databaseParameters["port"])
        || empty($databaseParameters["database"])
    ) {
        $app->render('database-configuration.php', ["error" => "Please fill in all fields"]);

        return;
    }

    $connectionInfo = new DatabaseConnectionInformation();
    $connectionInfo->username     = $databaseParameters["user"];
    $connectionInfo->hostname     = $databaseParameters["host"];
    $connectionInfo->port         = $databaseParameters["port"];
    $connectionInfo->databaseName = $databaseParameters["database"];
    $connectionInfo->password     = $databaseParameters["password"];

    try {
        $databaseFactory = new DatabaseFactory();
        $databaseFactory->createPDOConnection($connectionInfo); // check connection
    } catch (\Exception $e) {
        $app->render('database-configuration.php', ['error' => $e->getMessage()]);

        return;
    }

    $_SESSION["databaseConnectionInfo"] = $connectionInfo;

    try {
        /** @var $configWriter ConfigWriter */
        $configWriter = $container->offsetGet('config.writer');
        $configWriter->writeConfig($connectionInfo);
    } catch (\Exception $e) {
        $app->render('database-configuration.php', ['error' => $e->getMessage()]);

        return;
    }

    // Redirect to next step - (everything seems to be okay)
    $app->redirect($menuHelper->getNextUrl());
})->name("database-configuration")->via('GET', 'POST');

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
            ["error" => "Please fill in all fields"]
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
    $app->render("database-import.php");
})->name("database-import")->via('GET', 'POST');

$app->map('/edition/', function () use ($app, $translations, $container, $menuHelper) {
    $menuHelper->setCurrent('edition');

    try {
        $container->offsetGet('db');
    } catch (\Exception $e) {
        $menuHelper->setCurrent('database-configuration');
        $app->render('database-configuration.php', ["error" => "Please fill in all fields"]);

        return;
    }

    /** @var $licenseUnpackService LocalLicenseUnpackService */
    $licenseUnpackService = $container->offsetGet('license.service');

    if ($app->request()->isPost()) {
        if ($app->request()->post("c_edition") == "ce") {
            // If ce-edition continue with installation
            $app->redirect($menuHelper->getNextUrl());
        }

        // If PE/EE/EEC check license
        if (!$app->request()->post("c_license")) {
            $app->view()->setData("error", $translations['edition_license_error']);
        } else {
            $unpackRequest = new LicenseUnpackRequest(
                $app->request()->post("c_license"),
                $_SERVER["HTTP_HOST"]
            );

            try {
                $licenseInformation = $licenseUnpackService->evaluateLicense($unpackRequest);
            } catch (\Exception $e) {
                $app->view()->setData("error", $e->getMessage());
                $app->render("/edition.php");

                return;
            }

            /** @var $licenseInstaller LicenseInstaller */
            $licenseInstaller = $container->offsetGet('license.installer');
            $licenseInstaller->installLicense($licenseInformation);

            $app->redirect($menuHelper->getNextUrl());
        }
    }

    if (empty($_SESSION["parameters"]["c_edition"])) {
        $_SESSION["parameters"]["c_edition"] = "ce";
    }
    if (empty($_SESSION["parameters"]["c_license"])) {
        $_SESSION["parameters"]["c_license"] = "";
    }

    $app->view()->setData("parameters", $_SESSION["parameters"]);
    $app->render("/edition.php", []);
})->name("edition")->via('GET', 'POST');

$app->map('/configuration/', function () use ($app, $translationService, $container, $menuHelper) {
    $menuHelper->setCurrent('configuration');

    try {
        $db = $container->offsetGet('db');
    } catch (\Exception $e) {
        $menuHelper->setCurrent('database-configuration');
        $app->render('database-configuration.php', ["error" => "Please fill in all fields"]);

        return;
    }

    if ($app->request()->isPost()) {
        $adminUser = new \Shopware\Recovery\Install\Struct\AdminUser([
            'email'    => $_SESSION["parameters"]['c_config_admin_email'],
            'username' => $_SESSION["parameters"]['c_config_admin_username'],
            'locale'   => $_SESSION["parameters"]['c_config_admin_language'],
            'name'     => $_SESSION["parameters"]['c_config_admin_name'],
            'password' => $_SESSION["parameters"]['c_config_admin_password'],
        ]);

        $shop = new \Shopware\Recovery\Install\Struct\Shop([
            'name'     => $_SESSION["parameters"]['c_config_shopName'],
            'locale'   => $_SESSION["parameters"]['c_config_shop_language'],
            'currency' => $_SESSION["parameters"]['c_config_shop_currency'],
            'email'    => $_SESSION["parameters"]['c_config_mail'],
            'host'     => $_SERVER["HTTP_HOST"],
            'basePath' => str_replace("/recovery/install/index.php", "", $_SERVER["SCRIPT_NAME"]),
        ]);
        $locale = $_SESSION["parameters"]['c_config_shop_language'] ? : 'de_DE';

        $shopService  = new ShopService($db);
        $currencyService  = new CurrencyService($db);
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
            $app->view()->setData("error", $e->getMessage());
        }

        if (!$hasErrors) {
            $app->redirect($app->urlFor('finalize'));
        }
    }

    $domain = $_SERVER["HTTP_HOST"];
    $basepath = str_replace("/recovery/install/index.php", "", $_SERVER["SCRIPT_NAME"]);
    // Load shop-url
    $app->view()->setData("shop", ["domain" => $domain, "basepath" => $basepath]);

    if (empty($_SESSION['parameters']["c_config_shop_language"])) {
        $_SESSION["parameters"]["c_config_shop_language"] = $translationService->translate('locale');
    }
    if (empty($_SESSION["parameters"]["c_config_shop_currency"])) {
        $_SESSION["parameters"]["c_config_shop_currency"] = $translationService->translate('currency');
    }
    if (empty($_SESSION["parameters"]["c_config_admin_language"])) {
        $_SESSION["parameters"]["c_config_admin_language"] = $translationService->translate('locale');
    }

    $app->view()->setData("parameters", $_SESSION["parameters"]);
    $app->render("/configuration.php", []);
})->name("configuration")->via('GET', 'POST');

$app->map('/finalize/', function () use ($app, $container) {
    /** @var ThemeService $themeService */
    $themeService = $container->offsetGet('theme.service');
    $themeService->activateResponsiveTheme();

    $app->redirect($app->urlFor('finish'));
})->name("finalize")->via('GET', 'POST');

$app->map('/finish/', function () use ($app, $menuHelper, $container) {
    $menuHelper->setCurrent('finish');

    $domain   = $_SERVER["HTTP_HOST"];
    $basepath = str_replace("/recovery/install/index.php", "", $_SERVER["SCRIPT_NAME"]);

    /** @var \Shopware\Recovery\Common\SystemLocker $systemLocker */
    $systemLocker = $container->offsetGet('system.locker');
    $systemLocker();

    $app->render(
        "finish.php",
        ["shop" => ["domain" => $domain, "basepath" => $basepath]]
    );
})->name("finish")->via('GET', 'POST');

$app->map('/database-import/importDatabase', function () use ($app, $container) {
    $response = $app->response();
    $request  = $app->request();
    $response->header('Content-Type', 'application/json');
    $response->status(200);

    /** @var $db \PDO */
    $db = $container->offsetGet('db');

    /** @var $dump \Shopware\Recovery\Common\DumpIterator */
    $dump = $container->offsetGet('database.dump_iterator');

    $offset     = (int) $request->get('offset', 0);
    $totalCount = (int) $request->get('totalCount', 0);

    if ($offset == 0) {
        $totalCount = $dump->count();
    }

    // how many queries should be executed per http request?
    $batchSize = 100;

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
                'query'        => $sql,
                'success'      => false,
                'offset'       => $offset,
                'errorMsg'     => $e->getMessage(),
            ];

            $response->body(json_encode($data));

            return;
        }

        $dump->next();
    }

    $data = [
        'valid'      => $dump->valid(),
        'offset'     => $dump->key(),
        'totalCount' => $totalCount,
        'success'    => true,
    ];

    $response->body(json_encode($data));
})->via('GET', 'POST')->name('applyMigrations');

$app->map('/database-import/importSnippets', function () use ($app, $container) {
    $response = $app->response();
    $response->header('Content-Type', 'application/json');
    $response->status(200);

    /** @var $dump \Shopware\Recovery\Common\DumpIterator */
    $dump = $container->offsetGet('database.snippet_dump_iterator');
    $offset     = $app->request()->get('offset');
    $totalCount = (int) $app->request()->get('totalCount', 0);

    if ($offset == 0) {
        $totalCount = $dump->count();
    }

    /** @var $conn \PDO */
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
                'query'        => $sql,
                'success'      => false,
                'offset'       => $offset,
                'errorMsg'     => $e->getMessage(),
            ];

            $response->body(json_encode($data));

            return;
        }
    }

    $data = [
        'valid'      => $dump->valid(),
        'offset'     => $dump->key(),
        'totalCount' => $totalCount,
        'success'    => true,
    ];

    $response->body(json_encode($data));

    return;
})->via('GET', 'POST')->name('applySnippets');

$app->post('/check-database-connection', function () use ($container, $app) {
    $request  = $app->request();
    $response = $app->response();

    $connectionInfo = new DatabaseConnectionInformation([
        'username'     => $request->post('c_database_user'),
        'hostname'     => $request->post('c_database_host'),
        'port'         => $request->post('c_database_port'),
        'password'     => $request->post('c_database_password'),
        'socket'       => $request->post('c_database_socket'),
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

    // init db in container
    $container->offsetSet('db', $connection);

    /** @var $databaseService DatabaseService */
    $databaseService = $container->offsetGet('database.service');
    $databaseNames   = $databaseService->getAvailableDatabaseNames();

    $result = [];
    foreach ($databaseNames as $databaseName) {
        $result[] = [
            'value'   => $databaseName,
            'display' => $databaseName,
        ];
    }

    $response->header('Content-Type', 'application/json');
    $response->status(200);
    $response->body(json_encode($result));
})->name('database');

return $app;
