<?php
use Slim\Slim;

if (!defined("installer")) {
    exit;
}

require SW_PATH . '/engine/Library/Slim/Slim.php';
require 'assets/php/Shopware_Install_Requirements.php';
require 'assets/php/Shopware_Install_Requirements_Path.php';
require 'assets/php/Shopware_Install_Database.php';
require 'assets/php/Shopware_Install_License.php';
require 'assets/php/Shopware_Install_Configuration.php';

\Slim\Slim::registerAutoloader();

// Initiate slim
$app = new \Slim\Slim();

//$app->add(new Slim_Middleware_SessionCookie());

if (!isset($_SESSION)) {
    session_start();
}
if (!isset($_SESSION["parameters"])) {
    $_SESSION["parameters"] = array();
}

/**
 * Load language file
 */
$allowedLanguages = array("de", "en");
$selectedLanguage = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
$selectedLanguage = substr($selectedLanguage[0], 0, 2);
if (empty($selectedLanguage) || !in_array($selectedLanguage, $allowedLanguages)) {
    $selectedLanguage = "de";
}
if (isset($_POST["language"]) && in_array($_POST["language"], $allowedLanguages)) {
    $selectedLanguage = $_POST["language"];
    $_SESSION["language"] = $selectedLanguage;
} elseif (isset($_SESSION["language"]) && in_array($_SESSION["language"], $allowedLanguages)) {
    $selectedLanguage = $_SESSION["language"];
} else {
    $_SESSION["language"] = $selectedLanguage;
}
$language = require("assets/lang/$selectedLanguage.php");

// Assign components
$app->config('install.requirements', new Shopware_Install_Requirements());
$app->config('install.requirementsPath', new Shopware_Install_Requirements_Path());
$app->config('install.language',$selectedLanguage);

$app->contentType('text/html; charset=utf-8');

// Save post - parameters
$params = $app->request()->params();

foreach ($params as $key => $value) {
    if (strpos($key,"c_")!==false) {
        $_SESSION["parameters"][$key] = $value;
    }
}

// Initiate database object
$databaseParameters = array(
    "user"     => isset($_SESSION["parameters"]["c_database_user"]) ? $_SESSION["parameters"]["c_database_user"] : "",
    "password" => isset($_SESSION["parameters"]["c_database_user"]) ? $_SESSION["parameters"]["c_database_password"] : "",
    "host"     => isset($_SESSION["parameters"]["c_database_user"]) ? $_SESSION["parameters"]["c_database_host"] : "",
    "port"     => isset($_SESSION["parameters"]["c_database_user"]) ? $_SESSION["parameters"]["c_database_port"] : "",
    "socket"   => isset($_SESSION["parameters"]["c_database_user"]) ? $_SESSION["parameters"]["c_database_socket"] : "",
    "database" => isset($_SESSION["parameters"]["c_database_user"]) ? $_SESSION["parameters"]["c_database_schema"] : "",
);
$app->config("install.database.parameters",$databaseParameters);
$app->config('install.database',new Shopware_Install_Database($databaseParameters));
$app->config('install.license',new Shopware_Install_License());
$configObj = new Shopware_Install_Configuration();
$app->config('install.configuration',$configObj);

// Set global variables
$app->view()->setData("selectedLanguage",$selectedLanguage);
$app->view()->setData("language",$language);
$app->view()->setData("baseURL", str_replace('index.php', '', $_SERVER["PHP_SELF"]));
$app->view()->setData("app",$app);
$app->view()->setData("error",false);
$app->view()->setData("parameters", $_SESSION["parameters"]);
$basepath = $configObj->getShopDomain();
$app->view()->setData("basepath","http://".$basepath["domain"].$basepath["basepath"]);

// Step 1: Select language
$app->map('/', function () {
    $app = Slim::getInstance();
    $app->render("/header.php",array("tab"=>"start"));
    $app->render("/step1.php",array());
    $app->render("/footer.php");
})->via('GET','POST')->name("step1");

// Step 2: Check system requirements
$app->map('/step2/', function () {
    $app = Slim::getInstance();
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
        $app->view()->setData("error",true);
    }
    if ($app->view()->getData("error") == false && $app->request()->post("action")) {
        // No errors and submitted form - proceed with next-step
        $app->redirect($app->urlFor("step3"));
    }
    $app->render("/header.php",array(
        "tab"=>"system","systemCheckResults"=>$systemCheckResults,"systemCheckResultsWritePermissions"=>$systemCheckPathResults));
    $app->render("/step2.php",array());
    $app->render("/footer.php");
})->name("step2")->via('GET','POST');

// Step 3: Enter database
$app->map('/step3/', function () {
    $app = Slim::getInstance();
    if ($app->request()->post("action")) {
        // Check form
        $getParams = $app->config("install.database.parameters");

        if (empty($getParams["user"]) || empty($getParams["host"]) || empty($getParams["port"]) || empty($getParams["database"])) {
            $app->view()->setData("error","Please fill in all fields");
        } else {
            // Check if database account is reachable
            $dbObj = $app->config("install.database");
            $dbObj->setDatabase();

            if ($dbObj->getError()) {
                $app->view()->setData("error",$dbObj->getError());
            } else {
                $dbObj->writeConfig();
                if ($dbObj->getError()) {
                    $app->view()->setData("error",$dbObj->getError());
                } else {
                    // Redirect to step 4 - (everything seems to be okay)
                    $app->redirect($app->urlFor("step4"));
                }
            }
        }
    }
    // Assign form parameters back
    $app->render("/header.php",array("tab"=>"database"));
    $app->render("/step3.php",array());
    $app->render("/footer.php");
})->name("step3")->via('GET','POST');

// Step 4: Import database
$app->map('/step4/', function () {
    $app = Slim::getInstance();
    if ($app->request()->post("action") && !$app->request()->post("c_skip")) {
        // Check if required fields are suited
        $getParams = $app->config("install.database.parameters");

        if (empty($getParams["user"]) || empty($getParams["password"]) || empty($getParams["host"]) || empty($getParams["port"]) || empty($getParams["database"])) {
            $app->view()->setData("error","Database connection parameters missing. Cookies enabled?");
        } else {
            // Check if database account is reachable
            $dbObj = $app->config("install.database");
            $dbObj->setDatabase();
            if ($dbObj->getError()) {
                $app->view()->setData("error",$dbObj->getError());
            } else {
                // Trying to import database
                $dbObj->importDump();
                $dbObj->importDumpSnippets();
                // If en-user import en.sql
                if ($app->config("install.language")!="de") {

                    $dbObj->importDumpEn();
                }
                if ($dbObj->getError()) {
                    $app->view()->setData("error",$dbObj->getError());
                } else {
                    $app->redirect($app->urlFor("step5"));
                }
            }
        }
    } elseif ($app->request()->post("c_skip")) {
        $app->redirect($app->urlFor("step5"));
    }

    $app->render("/header.php",array("tab"=>"database_import"));
    $app->render("/step4.php",array());
    $app->render("/footer.php");
})->name("step4")->via('GET','POST');

// Step 5: Enter license
$app->map('/step5/', function () {
    $app = Slim::getInstance();
    $app->render("/header.php",array("tab"=>"licence"));
    if ($app->request()->post("action")) {
        if ($app->request()->post("c_edition")!="ce") {
            // If PE/EE/EEC check license
            if (!$app->request()->post("c_license")) {
                $app->view()->setData("error","It is required that you enter a proper license key to install shopware pe/ee/ec");
            } else {
                $licenseObj = $app->config("install.license");
                $dbObj = $app->config("install.database");
                $dbObj->setDatabase();
                if ($dbObj->getError()) {
                    $app->view()->setData("error",$dbObj->getError());
                } else {
                    // Assign database connection to license object
                    $licenseObj->setDatabase($dbObj->getDatabase());
                    // Do license check
                    $licenseObj->evaluateLicense($app->request()->post("c_license"),$_SERVER["HTTP_HOST"],strtoupper($app->request()->post("c_edition")));
                    if ($licenseObj->getError()) {
                        $app->view()->setData("error",$licenseObj->getError());
                    } else {
                        // Proceed with next step
                        $app->redirect($app->urlFor("step6"));
                    }
                }
            }
        } else {
            // If ce-edition continue with installation
            $app->redirect($app->urlFor("step6"));
        }
    }
    if (empty($_SESSION["parameters"]["c_edition"])) {
        $_SESSION["parameters"]["c_edition"] = "ce";
    }
    if (empty($_SESSION["parameters"]["c_license"])) {
        $_SESSION["parameters"]["c_license"] = "";
    }
    $app->view()->setData("parameters",$_SESSION["parameters"]);
    $app->render("/step5.php",array());
    $app->render("/footer.php");
})->name("step5")->via('GET','POST');

// Step 6: Configure
$app->map('/step6/', function () {
    $app = Slim::getInstance();
    $configObj = $app->config("install.configuration");
    $dbObj = $app->config("install.database");
    $dbObj->setDatabase();
    if ($dbObj->getError()) {
        $app->view()->setData("error",$dbObj->getError());
    } else {
        $configObj->setDatabase($dbObj->getDatabase());
    }
    if (!$dbObj->getError()) {
        if ($app->request()->post("action")) {
            // First create or update admin-user
            if (!$configObj->createAdmin($_SESSION["parameters"])) {
                $app->view()->setData("error",$configObj->getError());
            } else {
                if (!$configObj->updateConfig($_SESSION["parameters"])) {
                    $app->view()->setData("error",$configObj->getError());
                } else {
                    if (!$configObj->updateShop($_SESSION["parameters"])) {
                        $app->view()->setData("error",$configObj->getError());
                    } else {
                        $app->redirect($app->urlFor("step7"));
                    }
                }
            }
        }
        // Load default currencies
        $currencies = $configObj->getCurrencies();
        if (!$currencies) {
            $app->view()->setData("error",$configObj->getError());
        } else {
            $app->view()->setData("currencies",$currencies);
        }

        // Load shop-url
        $app->view()->setData("shop",$configObj->getShopDomain());
    }
    if (empty($_SESSION["parameters"]["c_config_shop_language"])) {
        $_SESSION["parameters"]["c_config_shop_language"] = "de_DE";
    }
    if (empty($_SESSION["parameters"]["c_config_shop_currency"])) {
        $_SESSION["parameters"]["c_config_shop_currency"] = "de_DE";
    }
    if (empty($_SESSION["parameters"]["c_config_admin_language"])) {
        $_SESSION["parameters"]["c_config_admin_language"] = "de_DE";
    }
    $app->view()->setData("parameters",$_SESSION["parameters"]);
    $app->render("/header.php",array("tab"=>"configuration"));
    $app->render("/step6.php",array());
    $app->render("/footer.php");
})->name("step6")->via('GET','POST');

// Step 7: Finish
$app->map('/step7/', function () {
    $app = Slim::getInstance();
    $app->render("/header.php",array("tab"=>"done"));
    $configObj = $app->config("install.configuration");
    $app->render("/step7.php",array("shop"=>$configObj->getShopDomain()));
    $app->render("/footer.php");
})->name("step7")->via('GET','POST');


$app->run();
