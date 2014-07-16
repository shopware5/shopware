<?php
use Shopware\Recovery\Common\Utils;

$app = new \Slim\Slim(array(
    'templates.path'  => __DIR__ . '/../templates',
    'debug'           => false, // set debug to false so custom error handler is used
));
$app->contentType('text/html; charset=utf-8');



if (!isset($_SESSION)) {
    $sessionPath = str_replace('index.php','',$app->request()->getScriptName());
    session_cache_limiter(false);
    session_set_cookie_params(600, $sessionPath);
    session_start();
}

if (!isset($_SESSION["parameters"])) {
    $_SESSION["parameters"] = array();
}




/**
 * Load language file
 */
$allowedLanguages = array("de", "en");
$selectedLanguage = "de";
if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $selectedLanguage = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    $selectedLanguage = substr($selectedLanguage[0], 0, 2);
}
if (empty($selectedLanguage) || !in_array($selectedLanguage, $allowedLanguages)) {
    $selectedLanguage = "de";
}

if (isset($_POST["language"]) && in_array($_POST["language"], $allowedLanguages)) {
    $selectedLanguage = $_POST["language"];
    unset($_SESSION["parameters"]["c_config_shop_language"]);
    unset($_SESSION["parameters"]["c_config_shop_currency"]);
    unset($_SESSION["parameters"]["c_config_admin_language"]);
    $_SESSION["language"] = $selectedLanguage;
} elseif (isset($_SESSION["language"]) && in_array($_SESSION["language"], $allowedLanguages)) {
    $selectedLanguage = $_SESSION["language"];
} else {
    $_SESSION["language"] = $selectedLanguage;
}

$language = require __DIR__ . "/../data/lang/$selectedLanguage.php";

// Save post parameters starting with "c_" to session
$params = $app->request()->params();
foreach ($params as $key => $value) {
    if (strpos($key,"c_") !== false) {
        $_SESSION["parameters"][$key] = $value;
    }
}

// Initiate database object
$databaseParameters = array(
    "user"     => isset($_SESSION["parameters"]["c_database_user"])     ? $_SESSION["parameters"]["c_database_user"] : "",
    "password" => isset($_SESSION["parameters"]["c_database_password"]) ? $_SESSION["parameters"]["c_database_password"] : "",
    "host"     => isset($_SESSION["parameters"]["c_database_host"])     ? $_SESSION["parameters"]["c_database_host"] : "",
    "port"     => isset($_SESSION["parameters"]["c_database_port"])     ? $_SESSION["parameters"]["c_database_port"] : "",
    "socket"   => isset($_SESSION["parameters"]["c_database_socket"])   ? $_SESSION["parameters"]["c_database_socket"] : "",
    "database" => isset($_SESSION["parameters"]["c_database_schema"])   ? $_SESSION["parameters"]["c_database_schema"] : "",
);

$app->config("install.database.parameters", $databaseParameters);

$app->container->singleton('install.database', function () use ($app) {
    return new Shopware\Recovery\Install\Database($app->config("install.database.parameters"), SW_PATH);
});

$app->container->singleton('install.license', function () use ($app) {
    /** @var \Shopware\Recovery\Install\Database $database */
    $database = $app->container->get('install.database');
    if (!$database->setDatabase()) {
        throw new \Exception($database->getError());
    }
    $pdo = $database->getDatabase();

    $license = new \Shopware\Recovery\Install\License($pdo);

    return $license;
});

$app->config('install.configuration', new \Shopware\Recovery\Install\Configuration());
$app->config('install.requirements', new \Shopware\Recovery\Install\Requirements(__DIR__ . '/../data/System.xml'));
$app->config('install.requirementsPath', new \Shopware\Recovery\Install\RequirementsPath(SW_PATH, __DIR__ . '/../data/Path.xml'));
$app->config('install.language', $selectedLanguage);

// Set global variables
$app->view()->setData("selectedLanguage", $selectedLanguage);
$app->view()->setData("language", $language);
$app->view()->setData("baseUrl", Utils::getBaseUrl($app));
$app->view()->setData("app", $app);
$app->view()->setData("error", false);
$app->view()->setData("parameters", $_SESSION["parameters"]);

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

// Step 1: Select language
$app->map('/', function () use ($app) {
    $app->render("/step1.php",array());
})->via('GET','POST')->name("step1");

// Step 2: Check system requirements
$app->map('/step2/', function () use ($app) {
    // Check system requirements
    $shopwareSystemCheck = $app->config('install.requirements');
    $systemCheckResults = $shopwareSystemCheck->toArray();
    if ($shopwareSystemCheck->getFatalError() == true) {
        $app->view()->setData("error", true);
    }
    // Check file & directory permissions
    $shopwareSystemCheckPath = $app->config("install.requirementsPath");
    $systemCheckPathResults = $shopwareSystemCheckPath->toArray();
    if ($shopwareSystemCheckPath->getFatalError() == true) {
        $app->view()->setData('error', true);
    }

    if ($app->view()->getData("error") == false && $app->request()->post("action")) {
        // No errors and submitted form - proceed with next-step
        $app->redirect($app->urlFor("step3"));
    }

    $app->render("/step2.php", array(
        "systemCheckResults"                 => $systemCheckResults,
        "systemCheckResultsWritePermissions" => $systemCheckPathResults
    ));
})->name("step2")->via('GET','POST');

// Step 3: Enter database
$app->map('/step3/', function () use ($app) {
    if (!$app->request()->post("action")) {
        $app->render('step3.php');

        return;
    }

    // Check form
    $getParams = $app->config("install.database.parameters");
    if (empty($getParams["user"]) || empty($getParams["host"]) || empty($getParams["port"]) || empty($getParams["database"])) {
        $app->render('step3.php',array("error" => "Please fill in all fields"));

        return;
    }

    // Check if database account is reachable
    $dbObj = $app->container->get("install.database");
    $dbObj->setDatabase();

    if ($dbObj->getError()) {
        $app->render('step3.php', array('error' => $dbObj->getError()));

        return;
    }

    $dbObj->writeConfig();
    if ($dbObj->getError()) {
        $app->render('step3.php', array('error' => $dbObj->getError()));

        return;
    }

    // Redirect to step 4 - (everything seems to be okay)
    $app->redirect($app->urlFor("step4"));
})->name("step3")->via('GET','POST');

// Step 4: Import database
$app->map('/step4/', function () use ($app) {
    if ($app->request()->post('action')) {
        $app->redirect($app->urlFor('step5'));
    }

    // Check form
    $getParams = $app->config('install.database.parameters');
    if (empty($getParams["user"]) || empty($getParams["host"]) || empty($getParams["port"]) || empty($getParams["database"])) {
        $app->render('step3.php',array("error" => "Please fill in all fields"));

        return;
    }

    /** @var \Shopware\Recovery\Install\Database $dbObj */
    $dbObj = $app->container->get("install.database");
    $dbObj->setDatabase();
    if ($dbObj->getError()) {
        $app->view()->setData('error', $dbObj->getError());
    }

    $app->render("step4.php",array());
})->name("step4")->via('GET','POST');

// Step 5: Enter license
$app->map('/step5/', function () use ($app, $language) {
    /** @var \Shopware\Recovery\Install\Database $dbObj */
    $dbObj = $app->container->get('install.database');
    $dbObj->setDatabase();
    if ($dbObj->getError()) {
        $app->render('step5.php', array('error' => $dbObj->getError()));
        return;
    }

    /** @var \Shopware\Recovery\Install\License $licenseObj */
    $licenseObj = $app->container->get('install.license');

    if ($app->request()->post("action")) {
        if ($app->request()->post("c_edition") == "ce") {
            // If ce-edition continue with installation
            $app->redirect($app->urlFor("step6"));
            return;
        }

        // If PE/EE/EEC check license
        if (!$app->request()->post("c_license")) {
            $app->view()->setData("error", $language['step5_license_error']);
        } else {
            // Do license check
            $licenseObj->evaluateLicense(
                $app->request()->post("c_license"),
                $_SERVER["HTTP_HOST"],
                strtoupper($app->request()->post("c_edition"))
            );

            if ($licenseObj->getError()) {
                $app->view()->setData("error",$licenseObj->getError());
            } else {
                // Proceed with next step
                $app->redirect($app->urlFor("step6"));
            }
        }

    }

    if (empty($_SESSION["parameters"]["c_edition"])) {
        $_SESSION["parameters"]["c_edition"] = "ce";
    }
    if (empty($_SESSION["parameters"]["c_license"])) {
        $_SESSION["parameters"]["c_license"] = "";
    }

    $app->view()->setData("parameters", $_SESSION["parameters"]);
    $app->render("/step5.php",array());
})->name("step5")->via('GET','POST');

// Step 6: Configure
$app->map('/step6/', function () use ($app, $language) {
    /** @var \Shopware\Recovery\Install\Database $dbObj */
    $dbObj = $app->container->get("install.database");
    $dbObj->setDatabase();
    if ($dbObj->getError()) {
        $app->view()->setData("error",$dbObj->getError());
    } else {
        /** @var Shopware\Recovery\Install\Configuration $configObj */
        $configObj = $app->config("install.configuration");
        $configObj->setDatabase($dbObj->getDatabase());

        if ($app->request()->post("action")) {
            $hasErrors = false;
            try {
                $configObj->createAdmin($_SESSION["parameters"]);
                $configObj->updateConfig($_SESSION["parameters"]);
                $configObj->updateShop($_SESSION["parameters"]);
            } catch (\Exception $e) {
                $hasErrors = true;
                $app->view()->setData("error", $e->getMessage());
            }
            if (!$hasErrors) {
                $app->redirect($app->urlFor("step7"));
            }
        }

        try {
            // Load default currencies
            $currencies = $configObj->getCurrencies();
            $app->view()->setData('currencies', $currencies);
        } catch (\Exception $e) {
            $app->view()->setData('error', $e->getMessage());
        }

        // Load shop-url
        $app->view()->setData("shop", $configObj->getShopDomain());
    }

    if (empty($_SESSION['parameters']["c_config_shop_language"])) {
        $_SESSION["parameters"]["c_config_shop_language"] = $language['locale'];
    }
    if (empty($_SESSION["parameters"]["c_config_shop_currency"])) {
        $_SESSION["parameters"]["c_config_shop_currency"] = $language['locale'];
    }
    if (empty($_SESSION["parameters"]["c_config_admin_language"])) {
        $_SESSION["parameters"]["c_config_admin_language"] = $language['locale'];
    }

    $app->view()->setData("parameters", $_SESSION["parameters"]);
    $app->render("/step6.php",array());
})->name("step6")->via('GET','POST');

// Step 7: Finish
$app->map('/step7/', function () use ($app) {
    /** @var \Shopware\Recovery\Install\Configuration $configObj */
    $configObj = $app->config("install.configuration");
    $app->render("step7.php", array("shop" => $configObj->getShopDomain()));
})->name("step7")->via('GET','POST');

$app->map('/step4/importDatabase', function () use ($app) {
    $response = $app->response();
    $response['Content-Type'] = 'application/json';
    $response->status(200);

    $dumpFile   = __DIR__ . '/../data/sql/sw4_clean.sql';
    $dump       = new \Shopware\Recovery\Common\Dump($dumpFile);
    $offset     = $app->request()->get('offset');
    $totalCount = (int) $app->request()->get('totalCount', 0);

    if ($offset == 0) {
        $totalCount = $dump->count();
    }

    $batchSize = 100;

    /** @var \Shopware\Recovery\Install\Database $dbObj */
    $dbObj = $app->container->get("install.database");
    $dbObj->setDatabase();

    $db = $dbObj->getDatabase();

    if ($dbObj->getError()) {
        throw new \Exception("database eror");
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
            $data = array(
                'query'        => $sql,
                'success'      => false,
                'offset'       => $offset,
                'errorMsg'     => $e->getMessage(),
            );

            $response->body(json_encode($data));

            return;
        }

        $dump->next();
    }

    // If end-user import en.sql
    if (!$dump->valid() && $app->config("install.language") != "de") {
        $dbObj->importDumpEn();
    }

    $data = array(
        'valid'      => $dump->valid(),
        'offset'     => $dump->key(),
        'totalCount' => $totalCount,
        'success'    => true,
    );

    $response->body(json_encode($data));
})->via('GET', 'POST')->name('applyMigrations');

$app->map('/step4/importSnippets', function () use ($app) {
    $response = $app->response();
    $response['Content-Type'] = 'application/json';
    $response->status(200);

    $dumpFile = __DIR__ . '/../data/sql/snippets.sql';
    if (!is_file($dumpFile)) {
        $response->body(json_encode(array(
            'valid'      => false,
            'offset'     => 0,
            'totalCount' => 0,
            'success'    => true,
        )));

        return;
    }

    $dump       = new \Shopware\Recovery\Common\Dump($dumpFile);
    $offset     = $app->request()->get('offset');
    $totalCount = (int) $app->request()->get('totalCount', 0);

    if ($offset == 0) {
        $totalCount = $dump->count();
    }

    /** @var \Shopware\Recovery\Install\Database $dbObj */
    $dbObj = $app->container->get("install.database");
    $dbObj->setDatabase();

    $conn = $dbObj->getDatabase();

    if ($dbObj->getError()) {
        throw new \Exception("database eror");
    }

    $preSql = '
       SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
       SET time_zone = "+00:00";
       SET @locale_de_DE = (SELECT id FROM s_core_locales WHERE locale = "de_DE");
       SET @locale_en_GB = (SELECT id FROM s_core_locales WHERE locale = "en_GB");
   ';

    $conn->query($preSql);
    $dump->seek($offset);

    $sql = array();
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
            $data = array(
                'query'        => $sql,
                'success'      => false,
                'offset'       => $offset,
                'errorMsg'     => $e->getMessage(),
            );

            $response->body(json_encode($data));

            return;
        }
    }

    $data = array(
        'valid'      => $dump->valid(),
        'offset'     => $dump->key(),
        'totalCount' => $totalCount,
        'success'    => true,
    );

    $response->body(json_encode($data));

    return;
})->via('GET', 'POST')->name('applySnippets');

return $app;
