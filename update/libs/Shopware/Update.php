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
 * @category   Shopware_Update
 * @package    Shopware_Update
 * @subpackage Shopware_Update
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Shopware Update
 */
class Shopware_Update extends Slim
{
    const VERSION = '1.0.0';
    const UPDATE_VERSION = '4.0.4';

    public function initDbConfig()
    {
        $DB_HOST = null; $DB_USER = null; $DB_PASSWORD = null; $DB_DATABASE = null;
        $config = include 'config.php';
        $config = isset($config['db']) ? $config['db'] : array();

        if(isset($DB_HOST)) {
            if(strpos($DB_HOST, ':')) {
                list($host, $port) = explode(':', $DB_HOST);
            } else {
                $host = $DB_HOST;
            }
            $config = array(
                'username' => $DB_USER,
                'password' => $DB_PASSWORD,
                'dbname' => $DB_DATABASE,
                'host' => $host
            );
            if(isset($port)) {
                if(is_numeric($port)) {
                    $config['port'] = $port;
                } else {
                    $config['unix_socket'] = $port;
                }
            }
        }
        return $config;
    }

    public function initDb()
    {
        $config = $this->initDbConfig();

        $dsn = array();
        if(isset($config['host'])) {
            $dsn[] = 'host=' . $config['host'];
        }
        if(isset($config['port'])) {
            $dsn[] = 'port=' . $config['port'];
        }
        if(isset($config['unix_socket'])) {
            $dsn[] = 'unix_socket=' . $config['unix_socket'];
        }
        if(isset($config['dbname'])) {
            $dsn[] = 'dbname=' . $config['dbname'];
        }
        $dsn = 'mysql:' . implode(';', $dsn);
        $db = new PDO(
            $dsn,
            isset($config['username']) ? $config['username'] : null,
            isset($config['password']) ? $config['password'] : null
        );
        $db->exec("SET NAMES 'utf8'; SET FOREIGN_KEY_CHECKS = 0;");
        return $db;
    }

    public function initSource()
    {
        $config = include 'config.php';
        $config = isset($config['db']) ? $config['db'] : array();
        $config = array_merge(array('host' => '', 'port' => '', 'password' => ''), $config);
        $db = new PDO(
            "mysql:host={$config['host']};port={$config['port']};dbname=shopware_clean",
            $config['username'], $config['password']
        );
        $db->exec("SET NAMES 'utf8' COLLATE 'utf8_unicode_ci'; SET FOREIGN_KEY_CHECKS = 0;");
        return $db;
    }

    public function initCurrentVersion()
    {
        $db = $this->config('db');
        $sql = "SELECT value FROM s_core_config WHERE name = 'sVERSION'";
        $query = $db->query($sql);
        $version = $query ? $query->fetchColumn(0) : self::UPDATE_VERSION;
        return $version;
    }

    public function initTranslation()
    {
        return include 'assets/translation/' . $this->config('language') . '.php';
    }

    public function session($key, $value = null)
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        if($value === null) {
            return isset($_SESSION[$key]) ? $_SESSION[$key]: null;
        } else {
            $_SESSION[$key] = $value;
        }
    }

    public function getBasePath()
    {
        static $basePath;
        if ($basePath === null) {
            $filename = (isset($_SERVER['SCRIPT_FILENAME']))
                ? basename($_SERVER['SCRIPT_FILENAME'])
                : '';
            $baseUrl = $this->request()->getRootUri();
            if (empty($baseUrl)) {
                $basePath = '';
                return $this;
            }
            if (basename($baseUrl) === $filename) {
                $basePath = dirname($baseUrl);
            } else {
                $basePath = $baseUrl;
            }
            if (substr(PHP_OS, 0, 3) === 'WIN') {
                $basePath = str_replace('\\', '/', $basePath);
            }
            $basePath = rtrim($basePath, '/');
        }
        return $basePath;
    }

    public function __construct($userSettings = array())
    {
        parent::__construct($userSettings);

        spl_autoload_register(array(__CLASS__, 'autoload'));

        $this->contentType('Content-type: text/html; charset=utf-8');

        $this->config('db', $this->initDb());
        $this->config('language', $this->session('language') ?: 'de');
        $this->config('auth', $this->session('auth'));

        $this->config(array(
            'currentVersion' => $this->initCurrentVersion(),
            'updateVersion' => self::UPDATE_VERSION,
            'channel' => 'http://files.shopware.de/download.php',
            'package' => 'install_' . self::UPDATE_VERSION,
            'format' => 'zip',
            'storeLink' => 'http://store.shopware.de/shopware.php/sViewport,search?sSearch=',
            'updateDir' => realpath('.') . DIRECTORY_SEPARATOR,
            'sourceDir' => realpath('.') . DIRECTORY_SEPARATOR . 'source' . DIRECTORY_SEPARATOR,
            'backupDir' => realpath('backup/') . DIRECTORY_SEPARATOR,
            'targetDir' => realpath('../') . DIRECTORY_SEPARATOR,
            'testPaths' => array(
                '',
                'images/',
                'templates/',
                'templates/_default/',
                'update/backup/',
                'update/source/',
                'config.php',
                'Application.php',
                'shopware.php',
                '.htaccess'
            ),
            'updateDirs' => array(
                '',
                'templates/',
            ),
            'updatePaths' => array(
                'cache/',
                'engine/',
                'media/',
                'snippets/',
                'templates/_default/',
                'templates/_emotion/',
                'templates/_emotion_local/',
                'templates/emotion_*',
                'templates/orange/',
                'templates/license.txt',
                'Application.php',
                'shopware.php',
                '.htaccess',
                '*.txt'
            ),
            'chmodPaths' => array(
                'cache/database/',
                'cache/templates/',
                'engine/Library/Mpdf/tmp/',
                'engine/Library/Mpdf/ttfontdata/',
                'engine/Shopware/Models/Attribute/',
                'engine/Shopware/Proxies/',
                'engine/Shopware/Plugins/Community/',
                'engine/Shopware/Plugins/Local/',
                'media/archive/',
                'media/image/',
                'media/image/thumbnail/',
                'media/music/',
                'media/pdf/',
                'media/unknown/',
                'media/video/',
                'media/temp/',
            )
        ));

        $this->view()->appendData(array(
            'app' => $this,
            'translation' => $this->initTranslation(),
            'language' => $this->config('language')
        ));

        $app = $this;

        $this->hook('slim.before.router', function () use ($app) {
            if($app->config('auth') === null
              && $app->request()->getPathInfo() !== '/'
              && $app->request()->getPathInfo() !== '/test') {
                $app->redirect($app->urlFor('index'));
            }
        });

        $this->get('/', function () use ($app) {
            $app->render('index.php', array(
                'action' => 'index'
            ));
        })->via('GET')->name('index');

        $this->get('/', array($this, 'loginAction'))->via('POST')->name('login');

        $this->get('/test', function () use ($app) {
            echo 'Hello';
        })->via('GET', 'POST')->name('test');

        $this->get('/system', function () use ($app) {
            $system = new Shopware_Components_Check_System();
            $app->render('system.php', array(
                'action' => 'system',
                'system' => $system,
                'customs' => $app->getCustomList(),
                'error' => false
            ));
        })->via('GET', 'POST')->name('system');

        $this->get('/main', function () use ($app) {
            $targetDir = $app->config('targetDir');
            $testDirs = $app->config('testPaths');
            $testDirs[] = 'update/backup/';
            $testDirs[] = 'update/source/';
            foreach($testDirs as $key => $testDir) {
                if(!file_exists($targetDir . $testDir) || is_writable($targetDir . $testDir)) {
                    unset($testDirs[$key]);
                }
            }
            $app->render('main.php', array(
                'action' => 'main',
                'app' => $app,
                'testDirs' => $testDirs
            ));
        })->via('GET', 'POST')->name('main');

        $this->get('/restore', function () use ($app) {
            $app->render('restore.php', array(
                'action' => 'restore',
                'app' => $app,
                'error' => false
            ));
        })->via('GET', 'POST')->name('restore');

        $this->get('/custom', function () use ($app) {
            /** @var $db PDO */
            $db = $app->config('db');

            $sql = "
                SELECT `value`
                FROM `backup_s_core_config`
                WHERE `name` = 'sCONFIGCUSTOMFIELDS'
                AND (
                  SELECT 1 FROM backup_s_articles_groups_value
                  WHERE gv_attr1 IS NOT NULL
                  OR gv_attr2 IS NOT NULL
                  OR gv_attr3 IS NOT NULL
                  OR gv_attr4 IS NOT NULL
                  OR gv_attr5 IS NOT NULL
                  LIMIT 1
                )
            ";
            $query = $db->query($sql);
            $fields = array();
            if($query !== false && ($result = $query->fetchColumn()) !== false) {
                $fields = explode(',', $result);
            }

            $sql = '
                SELECT name, label
                FROM s_core_engine_elements
                WHERE variantable = 1
            ';
            $query = $db->query($sql);
            $targetFields = array();
            if($query !== false) {
                $targetFields = $query->fetchAll(PDO::FETCH_KEY_PAIR);
                $targetFields['weight'] = 'Gewicht';
                $targetFields['supplierNumber'] = 'Herstellernummer';
            }

            $app->render('custom.php', array(
                'action' => 'custom',
                'app' => $app,
                'customs' => $app->getCustomList(),
                'fields' => $fields,
                'targetFields' => $targetFields
            ));
        })->via('GET', 'POST')->name('custom');

        $this->get('/main/:action', function ($action) use ($app) {
            $action .= 'Action';
            if(method_exists($app, $action)) {
                $app->$action();
            } else {
                $app->notFound();
            }
        })->via('GET', 'POST')->name('action');

        $this->get('/license', function () use ($app) {
            $app->render('license.php', array(
                'action' => 'license',
                'error' => null,
                'product' => 'CE',
                'license' => null,
                'app' => $app
            ));
        })->name('license');

        $this->post('/license', function () use ($app) {
            $request = $app->request();
            $error = null;
            $host = $app->request()->getHost();
            $product = $request->post('product');
            $license = $request->post('license');
            $result = $app->doLicenseCheck($host, $product, $license);
            if(empty($result['success'])) {
                $app->render('license.php', array(
                    'action' => 'license',
                    'error' => isset($result['error']) ? $result['error'] : null,
                    'message' => isset($result['message']) ? $result['message'] : null,
                    'product' => $product,
                    'license' => $license,
                    'host' => $host,
                    'app' => $app
                ));
            } else {
                if(!empty($result['info'])) {
                    $app->session('license', $result['info']);
                }
                $app->redirect($app->urlFor('main'));
            }
        });

        $this->get('/finish', function () use ($app) {
            $app->render('finish.php', array(
                'action' => 'finish',
                'app' => $app
            ));
        })->via('GET', 'POST')->name('finish');
    }

    public function loginAction()
    {
        $language = $this->request()->post('language');
        if(in_array($language, array('de', 'en'))) {
            $this->session('language', $language);
        }
        $username = $this->request()->post('username');
        if($username !== null) {
            $sql = '
                SELECT id, name, email, IF(lockeduntil > NOW(), lockeduntil, NULL) as locked
                FROM s_core_auth
                WHERE username = :username
                AND password = MD5(CONCAT(:secret, MD5(:password)))
                AND active = 1 AND admin = 1
            ';
            $db = $this->initDb();
            $query = $db->prepare($sql);
            $query->execute(array(
                'username' => $username,
                'secret' => 'A9ASD:_AD!_=%a8nx0asssblPlasS$',
                'password' => $this->request()->post('password')
            ));
            $auth = $query->fetch(PDO::FETCH_ASSOC);
            if($auth === false) {
                $loginError = 'notFound';
            } elseif($auth['locked'] !== null) {
                $loginError = $auth['locked'];
            } else {
                $loginError = null;
            }
            if($loginError !== null) {
                $this->flash('loginError', $loginError);
                $this->redirect($this->urlFor('index'));
            } else {
                $this->session('auth', $auth);
                $this->redirect($this->urlFor('system'));
            }
        }
        $this->redirect($this->urlFor('index'));
    }

    public function diffDatabaseAction()
    {
        $source = $this->initSource();
        $skipTables = array(
            's_articles_bundles',
            's_articles_bundles_articles',
            's_articles_bundles_prices',
            's_articles_bundles_stint',
            's_core_plugins_b2b_cgsettings',
            's_core_plugins_b2b_private',
            's_core_plugins_b2b_tpl_config',
            's_core_plugins_b2b_tpl_variables',
            's_articles_live',
            's_articles_live_prices',
            's_articles_live_shoprelations',
            's_articles_live_stint',
            's_ticket_support_history',
            's_ticket_support_status',
            's_ticket_support_types',
            's_plugin_coupons',
            's_plugin_coupons_codes',
            's_articles_groups_accessories',
            's_articles_groups_accessories_option'
        );
        $backupTables = array(
            's_core_config',
            's_articles',
            's_categories',
            's_core_plugin_configs',
            's_core_plugin_elements',
            's_user_billingaddress',
            's_user_shippingaddress',
            's_core_multilanguage',
            's_order',
            's_order_details',
            's_order_billingaddress',
            's_order_shippingaddress',
            's_order_basket',
            's_core_plugins',
            's_core_subscribes',
            's_core_menu',
            's_core_countries',
            's_core_snippets',
            's_filter_values',
            's_articles_groups',
            's_articles_groups_option',
            's_articles_groups_prices',
            's_articles_groups_settings',
            's_articles_groups_value',
            's_core_licences',
            's_core_paymentmeans'
        );
        $mapping = array(
            's_addon_premiums' => array(
                'articleID' => 'ordernumber_export'
            ),
            's_core_engine_elements' => array(
                'domvalue' => 'default',
                'domvtype' => 'type',
                'domdescription' => 'label',
                'databasefield' => 'name',
                'domclass' => 'layout',
                'availablebyvariants' => 'variantable',
                'multilanguage' => 'translatable'
            )
        );
        $this->contentType('Content-type: text/plain; charset=utf-8');
        echo "\xEF\xBB\xBF";
        echo "ALTER DATABASE DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;\n\n";
        $export = new Shopware_Components_DbDiff_Mysql(
            $source,
            $this->config('db')
        );
        $tables = $export->listTables();
        foreach($tables as $table) {
            if(in_array($table, $skipTables)) {
                continue;
            }
            echo $export->getTableUpdate($table, array(
                'backup' => in_array($table, $backupTables),
                'mapping' => isset($mapping[$table]) ? $mapping[$table] : null
            ));
        }
    }

    public function backupDatabaseAction()
    {
        $backupDir = $this->config('backupDir');
        if(!is_writable($backupDir)) {
            echo json_encode(array(
                'message' => 'Das Backup-Verzeichnis "update/backup" ist nicht beschreibbar!',
                'success' => false,
            ));
            return;
        }

        set_time_limit(0);
        $skipTables = array(
            's_search_index',
            's_search_keywords',
            's_core_log',
            's_core_sessions'
        );
        if(($file = $this->request()->post('file')) === null) {
            $file = $backupDir . 'database.php';
        }
        $export = new Shopware_Components_DbExport_Mysql(
            $this->config('db')
        );

        if(($tables = $this->request()->post('tables')) === null) {
            $tables = $export->listTables();
        }
        if(!file_exists($file)) {
            $fp = fopen($file, 'wb');
            fwrite($fp, "/*<?php return; __halt_compiler(); ?>*/\n");
            fwrite($fp, "SET NAMES 'utf8';\n");
            fwrite($fp, "SET FOREIGN_KEY_CHECKS = 0;\n\n");
        } else {
            $fp = fopen($file, 'ab');
        }

        foreach($tables as $table) {
            $export->setTable($table);
            foreach($export as $line) {
                fwrite($fp, $line);
                if(in_array($table, $skipTables)) {
                    break;
                }
            }
        }

        echo json_encode(array(
            'message' => 'Das Datenbank-Backup wurde erfolgreich durchgeführt.',
            'success' => true,
            'file' => $file
        ));
    }

    public function databaseAction()
    {
        set_time_limit(0);
        /** @var $db PDO */
        $db = $this->config('db');
        $offset = (int)$this->request()->post('offset') ?: 0;
        $version = $this->config('currentVersion');
        if($offset == 0 && $version != '3.5.6') {
            echo json_encode(array(
                'message' => "Das Datenbank-Update unterstützt die Shopware-Version $version nicht.",
                'success' => true
            ));
            return;
        }
        $deltas = glob('deltas/' . $offset . '-*.sql');
        if(empty($deltas)) {
            echo json_encode(array(
                'message' => 'Das Datenbank-Update wurde erfolgreich durchgeführt.',
                'success' => true
            ));
            return;
        }
        natsort($deltas);
        foreach($deltas as $delta) {
            $import = new Shopware_Components_DbImport_Sql($delta);
            foreach($import as $query) {
                if(!empty($query) && $db->exec($query) === false) {
                    $errorInfo = $db->errorInfo();
                    $msg = 'Das Datenbank-Update konnte nicht abgeschlossen werden. <br>' .
                           'Ein Fehler beim Import der Datei " ' . $delta . '" ist aufgetreten. <br>' .
                           '['. $errorInfo[0] . '] ' . $errorInfo[2];
                    echo json_encode(array(
                        'message' => $msg,
                        'query' => $query,
                        'success' => false
                    ));
                    return;
                }
            }
        }
        $offset++;
        echo json_encode(array(
            'success' => true,
            'progress' => round($offset / 20, 2),
            'message' => 'Datenbank-Update durchführen',
            'offset' => $offset
        ));
    }

    public function restoreDatabaseAction()
    {
        set_time_limit(0);
        /** @var $db PDO */
        $db = $this->config('db');
        $backup = 'backup/database.php';
        $import = new Shopware_Components_DbImport_Sql($backup);
        foreach($import as $query) {
            if($db->exec($query) === false) {
                $errorInfo = $db->errorInfo();
                $msg = 'Die Datenbank-Wiederherstellung konnte nicht abgeschlossen werden. <br>' .
                       'Ein Fehler beim Import des Backups ist aufgetreten. <br>' .
                       '['. $errorInfo[0] . '] ' . $errorInfo[2];
                echo json_encode(array(
                    'message' => $msg,
                    'query' => $query,
                    'success' => false
                ));
                return;
            }
        }
        echo json_encode(array(
            'message' => 'Die Datenbank wurde erfolgreich wiederhergestellt.',
            'success' => true
        ));
    }

    public function downloadDatabaseAction()
    {
        ob_end_clean();
        set_time_limit(0);
        $backup = 'backup/database.php';
        $handle = fopen($backup, 'r');
        $size = filesize($backup) - strlen(fgets($handle));
        $file = basename($backup, '.php') . '.sql';
        header('Content-Type: application/force-download', true);
        header('Content-Disposition: attachment; filename="' . $file . '";');
        header('Content-Length: ' . $size);
        $this->response()->write(ob_get_clean());
        while (!feof($handle)) {
            echo fread($handle, 8192); flush();
        }
        fclose($handle);
        exit();
    }

    public function updateFieldsAction()
    {
        /** @var $db PDO */
        $db = $this->config('db');
        $fields = $this->request()->post('field');
        foreach($fields as $sourceField => $targetField) {
            if(empty($targetField)) {
                continue;
            }
            $targetField = preg_replace('#[^a-z0-9]#i', '', (string)$targetField);
            $targetTable = in_array($targetField, array('weight', 'supplierNumber')) ? 'd' : 't';
            $targetField = $targetTable . '.' . $targetField;
            $sourceField = 's.gv_attr' . (1 + $sourceField);
            $sql = "
                UPDATE backup_s_articles_groups_value s, s_articles_details d, s_articles_attributes t
                SET $targetField = $sourceField
                WHERE d.ordernumber = s.ordernumber
                AND t.articledetailsID = d.id
            ";
            $db->exec($sql);
        }
        echo json_encode(array(
            'message' => 'Die Konfigurator-Felder wurden erfolgreich übernommen.',
            'success' => true
        ));
    }

    public function updatePluginsAction()
    {
        $targetDir = $this->config('targetDir');
        $backupDir = $this->config('backupDir');

        /** @var $db PDO */
        $db = $this->config('db');
        $plugins = $this->request()->post('plugin');
        $plugins = array_map('intval', $plugins);
        $plugins = implode(', ', $plugins);
        $sql = "
            SELECT p.id, p.name, p.source, p.namespace, p.label
            FROM backup_s_core_plugins p
            WHERE p.id IN ($plugins)
        ";
        $query = $db->query($sql);
        if($query === false) {
            return;
        }
        $plugins = $query->fetchAll(PDO::FETCH_ASSOC);

        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            foreach($plugins as $plugin) {
                $pluginPath = array(
                    'engine',
                    'Shopware',
                    'Plugins',
                    $plugin['source'],
                    $plugin['namespace'],
                    $plugin['name'],
                    ''
                );
                $pluginPath = implode(DIRECTORY_SEPARATOR, $pluginPath);
                if(file_exists($backupDir . $pluginPath)) {
                    rename($backupDir . $pluginPath, $targetDir. $pluginPath);
                }
                $sql = "
                    INSERT IGNORE INTO s_core_plugins (
                      namespace, name, label, source, description, description_long,
                      active, added, installation_date, author, copyright, license,
                      version, support, link
                    )
                    SELECT
                      b.namespace, b.name, b.label, b.source, b.description, b.description_long,
                      b.active, b.added, b.installation_date, b.autor as author, b.copyright, b.license,
                      b.version, b.support, b.link
                    FROM backup_s_core_plugins b
                    WHERE b.id = :id
                ";
                $db->prepare($sql)->execute(array('id' => $plugin['id']));
                $sql = "
                    SELECT p.id
                    FROM backup_s_core_plugins b, s_core_plugins p
                    WHERE b.name = p.name AND b.namespace = p.namespace
                    AND b.id = :id
                ";
                $query = $db->prepare($sql);
                $query->execute(array('id' => $plugin['id']));
                $newId = $query->fetchColumn();
                if(empty($newId)) {
                    continue;
                }
                $sql = "
                    INSERT IGNORE INTO s_core_menu (
                      `parent`, `hyperlink`, `name`, `onclick`, `class`, `position`, `active`, `pluginID`
                    )
                    SELECT `parent`, `hyperlink`, `name`, `onclick`, `class`, `position`, `active`, :newId
                    FROM backup_s_core_menu WHERE pluginID = :id
                ";
                $db->prepare($sql)->execute(array('id' => $plugin['id'], 'newId' => $newId));
                $sql = "
                    INSERT IGNORE INTO s_core_subscribes (
                      `subscribe`, `type`, `listener`, `pluginID`, `position`
                    )
                    SELECT `subscribe`, `type`, `listener`, :newId, `position`
                    FROM backup_s_core_subscribes WHERE pluginID = :id
                ";
                $db->prepare($sql)->execute(array('id' => $plugin['id'], 'newId' => $newId));
                $sql = "
                    INSERT IGNORE INTO s_core_config_forms (name, label, description, plugin_id)
                    SELECT p.name, p.label, IF(p.description='', NULL, p.description) as description, :newId
                    FROM  backup_s_core_plugins p, backup_s_core_plugin_elements pc
                    WHERE pc.pluginID = p.id
                    AND p.id = :id
                    LIMIT 1
                ";
                $db->prepare($sql)->execute(array('id' => $plugin['id'], 'newId' => $newId));
                $sql = "
                    INSERT IGNORE INTO `s_core_config_elements` (
                      `form_id`, `name`, `value`, `label`, `description`,
                      `type`, `required`, `position`, `scope`,
                      `filters`, `validators`, `options`
                    )
                    SELECT
                      f.id as form_id, e.name,
                      IF(e.value='', NULL, e.value) as `value`,
                      e.label,
                      IF(e.description='', NULL, e.description) as `description`,
                      e.type, e.required, e.order, e.scope,
                      e.filters, e.validators,
                      IF(e.options IN ('', 'Array'), NULL, e.options) as `options`
                    FROM  backup_s_core_plugin_elements e, s_core_config_forms f
                    WHERE f.plugin_id = :newId
                    AND e.pluginID = :id
                ";
                $db->prepare($sql)->execute(array('id' => $plugin['id'], 'newId' => $newId));
            }
        } catch(Exception $e) {
            echo json_encode(array(
                'message' => "Ein Fehler bei der Übernahme des Plugins \"{$plugin['label']}\" ist aufgetreten: <br>"
                           . $e->getMessage(),
                'success' => false
            ));
            return;
        }

        echo json_encode(array(
            'message' => 'Die Plugins wurden erfolgreich übernommen.',
            'success' => true
        ));
    }

    public function progressAction()
    {
        $next = (int)$this->request()->post('next') ?: 1;
        switch($next) {
            case 1: $action = 'cache'; break;
            case 2: $action = 'config'; break;
            case 3: $action = 'category'; break;
            case 4: $action = 'other'; break;
            case 5: $action = 'mapping'; break;
            case 6: $action = 'download'; break;
            case 7: $action = 'unpack'; break;
            case 8: $action = 'move'; break;
            case 9: $action = 'media'; break;
            case 10: $action = 'license'; break;
            case 11: $action = 'cleanup'; break;
            default: $action = 'notFound'; break;
        }
        $method = 'progress' . ucfirst($action);
        if(method_exists($this, $method)) {
            set_time_limit(0);
            try {
                $result = $this->$method();
            } catch(Exception $e) {
                $result = array(
                    'message' => 'Ein Fehler im Update-Schritt "' . $action .'" ist aufgetreten: <br> '
                               . $e->getMessage(),
                    'success' => false
                );
            }
            if(!isset($result['offset'])) {
                $next++;
            }
            if(!empty($result['success']) && $next < 12) {
                $result['next'] = $next;
            }
            echo json_encode($result);
        } else {
            $this->notFound();
        }
    }

    public function progressDownload()
    {
        $requestTime = time();
        $offset = (int)$this->request()->post('offset') ?: 0;
        $package = $this->config('package');
        $format = $this->config('format');
        $sourceDir = $this->config('sourceDir');
        $sourceFile = $sourceDir . $package . '.' . $format;
        $size = 39161 * 1028;

        if((!$offset && file_exists($sourceFile))
          || !file_exists($sourceDir)
          || file_exists($sourceDir . 'shopware.php')) {
            return array(
                'success' => true,
            );
        }
        if(!is_writable($sourceDir)) {
            return array(
                'message' => 'Das Update-Verzeichnis "update/source/" ist nicht beschreibbar!',
                'success' => false,
            );
        }

        $url = $this->config('channel') .
               '?package=' . urlencode($package) .
               '&format=' . urlencode($format);
        if (!empty($offset)) {
            $url .= '&offset=' . (int)$offset;
        }

        $options = array('http' => array(
            'method' => 'GET',
            'user_agent' => $this->request()->getUserAgent(),
            'header' => 'Referer: ' .$this->request()->getUrl() . "\r\n"
        ));
        $context = stream_context_create($options);

        $source = @fopen($url, 'r', false, $context);
        if (!$source) {
            return array(
                'message' => 'Der Download der Update-Package ist fehlgeschlagen.',
                'success' => false
            );
        }

        if (!empty($offset)) {
            $target = @fopen($sourceFile, 'ab');
        } else {
            $target = @fopen($sourceFile, 'wb+');
        }

        while (!feof($source)) {
            fwrite($target, fread($source, 8192));
            if (time() - $requestTime >= 10) {
                $offset += ftell($source);
                return array(
                    'success' => true,
                    'progress' => round($offset / $size, 2),
                    'message' => 'Update-Package herunterladen',
                    'offset' => $offset
                );
            }
        }

        return array(
            'message' => 'Das Update-Package wurde erfolgreich heruntergeladen.',
            'success' => true
        );
    }

    public function progressUnpack()
    {
        $requestTime = time();
        $offset = (int)$this->request()->post('offset') ?: 0;
        $package = $this->config('package');
        $format = $this->config('format');
        $sourceDir = $this->config('sourceDir');
        $sourceFile = $sourceDir . $package . '.' . $format;
        $updatePaths = $this->config('updatePaths');
        $updateDirs = $this->config('updateDirs');
        $chmodPaths = $this->config('chmodPaths');

        if(!file_exists($sourceFile)) {
            return array(
                'success' => true,
            );
        }
        if(!is_writable($sourceDir)) {
            return array(
                'message' => 'Das Update-Verzeichnis "update/source/" ist nicht beschreibbar!',
                'success' => false,
            );
        }

        try {
            if ($format == 'zip') {
                $source = new Shopware_Components_Archive_Zip($sourceFile);
            } else {
                $source = new Shopware_Components_Archive_Tar($sourceFile);
            }
            $count = $source->count();
            $source->seek($offset);
        } catch (Exception $e) {
            @unlink($sourceFile);
            return array(
                'message' => 'Das Update-Package konnte nicht geöffnet werden. <br>' .
                             $e->getMessage(),
                'success' => false
            );
        }

        umask(0);

        foreach($updateDirs as $updateDir) {
            if(!file_exists($sourceDir . $updateDir)) {
                mkdir($sourceDir . $updateDir, 0755);
            }
        }

        $testPattern = array();
        foreach($updatePaths as $testPath) {
            $testPath = preg_quote($testPath, '#');
            $testPath = str_replace('\*', '[^/]*', $testPath);
            $testPattern[] = $testPath;
        }
        $testPattern = '#^' . implode('|^', $testPattern) . '#';

        while (list($position, $entry) = $source->each()) {
            $name = $entry->getName();
            $targetName = $sourceDir . $name;
            $result = true;

            if(!preg_match($testPattern, $name)) {
                continue;
            }

            if ($entry->isDir()) {
                if (!file_exists($targetName)) {
                    mkdir($targetName, 0755);
                }
            } else {
                file_put_contents($targetName, $entry->getContents());
            }

            $match = false;
            foreach($chmodPaths as $testPath) {
                if(strpos($name, $testPath) === 0) {
                    $match = true;
                    break;
                }
            }
            if($match) {
                chmod($targetName, 0777);
            }

            if (!$result && (!is_dir($name) || !filesize($name))) {
                echo "//$name\n";
            }

            if (time() - $requestTime >= 20 || ($position + 1) % 1000 == 0) {
                return array(
                    'progress' => round(($position + 1) / $count, 2),
                    'message' => 'Dateien entpacken',
                    'success' => true,
                    'offset' => $position + 1
                );
            }
        }

        @unlink($sourceFile);
        return array(
            'message' => 'Das Update-Package wurde erfolgreich entpackt.',
            'success' => true
        );
    }

    public function progressMove()
    {
        $backupDir = $this->config('backupDir');
        $sourceDir = $this->config('sourceDir');
        $updateDir = $this->config('updateDir');
        $targetDir = $this->config('targetDir');
        $updatePaths = $this->config('updatePaths');
        $updateDirs = $this->config('updateDirs');
        $warning = null;

        if(!file_exists($sourceDir)) {
            return array(
                'success' => true,
            );
        }
        if(!is_writable($sourceDir)) {
            return array(
                'message' => 'Das Shopware-Verzeichnis ist nicht beschreibbar!<br>' .
                    'Bitte passen Sie die Schreibrechte vom Shopware-Verzeichnis an.',
                'success' => false,
            );
        }

        chdir($sourceDir);
        $realUpdatePaths = array();
        foreach($updatePaths as $updatePath) {
            if(strpos($updatePath, '*') !== false) {
                $paths = glob($updatePath, GLOB_MARK);
                if(!empty($paths)) {
                    $realUpdatePaths = array_merge($realUpdatePaths, $paths);
                }
            } else {
                $realUpdatePaths[] = $updatePath;
            }
        }
        chdir($updateDir);

        foreach($updateDirs as $updateDir) {
            if(!file_exists($backupDir. $updateDir)) {
                mkdir($backupDir. $updateDir, 0777, true);
            }
            if(!file_exists($targetDir. $updateDir)) {
                mkdir($targetDir. $updateDir, 0777, true);
            }
        }

        foreach($realUpdatePaths as $updatePath) {
            if(file_exists($sourceDir . $updatePath) && file_exists($targetDir . $updatePath)) {
                rename($targetDir . $updatePath, $backupDir . $updatePath);
            }

            if ($updatePath == 'shopware.php') {
                continue;
            }

            if(file_exists($sourceDir . $updatePath)) {
                rename($sourceDir . $updatePath, $targetDir . $updatePath);
            }

            if ($updatePath == '.htaccess') {
                $testUrl = $this->request()->getScheme() . '://' .
                    $this->request()->getHostWithPort() .
                    $this->urlFor('test');
                $test = @file_get_contents($testUrl);
                if (empty($test) || $test != 'Hello') {
                    $warning = 'Die .htaccess-Datei konnte nicht übernommen werden. <br>' .
                               'Bitte führen Sie das Update der Datei manuell durch. <br>' .
                               'Die neue .htaccess-Datei finden Sie unter ".htaccess-update".';
                    rename($targetDir . $updatePath, $targetDir . $updatePath . '-update');
                    rename($backupDir . $updatePath, $targetDir . $updatePath);
                }
            }
        }

        return array(
            'message' => 'Das Datei-Update wurde erfolgreich abgeschlossen.',
            'warning' => $warning,
            'success' => true
        );
    }

    public function progressMedia()
    {
        /** @var $db PDO */
        $db = $this->config('db');
        $dirs = array(
            array(-1  ,'images/articles/', 'media/image/'),
            array(-2  ,'images/banner/', 'media/image/'),
            array(-2  ,'images/cms/', 'media/image/'),
            array(-10 ,'files/downloads/', 'media/unknown/'),
            array(-12 ,'images/supplier/', 'media/image/'),
        );
        $baseDir = $this->config('targetDir');

        $testDirs = array();
        foreach($dirs as $dir) {
            if(file_exists($baseDir. $dir[1]) && !is_writable($baseDir. $dir[1])) {
                $testDirs[] = $dir[1];
            }
            if(!file_exists($baseDir . $dir[2]) || !is_writable($baseDir. $dir[2])) {
                $testDirs[] = $dir[2];
            }
        }
        if(!empty($testDirs)) {
            $msg = 'Bilder konnten nicht übernommen werden.<br>' .
                   'Folgende Verzeichnisse sind nicht beschreibar:<br><br>';
            $msg .= implode('<br>', $testDirs);
            return array(
                'message' => $msg,
                'success' => false
            );
        }

        $sql = "SELECT `value` FROM `backup_s_core_config` WHERE `name` = 'sIMAGESIZES'";
        $sizes = $db->query($sql)->fetchColumn();
        if(!empty($sizes)) {
            $sizes = explode(';', $sizes);
            $newSizes = array_fill(0, count($sizes), null);
            foreach($sizes as $size) {
                $size = explode(':', $size);
                if(count($size) < 3) {
                    continue;
                }
                $newSizes[$size[2]] = $size[0] . 'x' . $size[1];
            }
            $newSizes = implode(';', $newSizes);
            $sql = 'UPDATE s_media_album_settings SET thumbnail_size = :size WHERE albumID = :albumId';
            $query = $db->prepare($sql);
            $query->execute(array('albumId' => -1, 'size' => $newSizes));
        }

        foreach($dirs as $dir) {
            $sql = '
                SELECT `thumbnail_size`
                FROM `s_media_album_settings`
                WHERE `albumID` = :albumId
                AND `create_thumbnails` =1
            ';
            $query = $db->prepare($sql);
            $query->execute(array('albumId' => $dir[0]));
            $thumbs = $query->fetchColumn(0);
            if($thumbs !== false) {
                $thumbs = explode(';', $thumbs);
            }
            if(!file_exists($baseDir . $dir[1])) {
                continue;
            }
            $iterator = new DirectoryIterator($baseDir . $dir[1]);
            foreach ($iterator as $file) {
                if($file->isDot()) {
                    continue;
                }
                $name = (string)$file;
                $newName = $name;
                if(preg_match('#(.+_)([0-9])+(.[a-z]+)$#', $newName, $match)) {
                    if(!isset($thumbs[$match[2]])) {
                        continue;
                    }
                    $newName = 'thumbnail/' . $match[1] . $thumbs[$match[2]] . $match[3];
                }
                rename($baseDir . $dir[1] . $name, $baseDir . $dir[2] . $newName);
            }
            @rmdir($baseDir . $dir[1]);
        }
        return array(
            'message' => 'Die Artikel-Bilder wurden erfolgreich übernommen.',
            'success' => true
        );
    }

    public function progressMapping()
    {
        /** @var $db PDO */
        $db = $this->config('db');
        $sql = "
            SELECT o.id
            FROM s_article_configurator_set_group_relations s
            JOIN s_article_configurator_groups g
            ON g.id = s.group_id
            AND :group LIKE REPLACE(g.name, ' ', '')
            JOIN s_article_configurator_options o
            ON o.group_id = g.id
            AND :option LIKE REPLACE(o.name, ' ', '')
            WHERE s.set_id = :setId
        ";
        $optionQuery = $db->prepare($sql);
        $sql = "
            INSERT INTO s_article_img_mappings ( image_id ) VALUES ( :imageId )
        ";
        $mappingQuery = $db->prepare($sql);
        $sql = "
        	INSERT INTO s_article_img_mapping_rules ( mapping_id, option_id )
			VALUES ( :mappingId, :optionId );
		";
        $ruleQuery = $db->prepare($sql);
        $sql = "
            SELECT a.name, a.id as articleId, i.id as imageId, i.relations, s.id as setId
            FROM s_articles_img i
            JOIN s_articles a
            ON a.id = i.articleID
            JOIN s_article_configurator_sets s
            ON s.id = a.configurator_set_id
            WHERE i.articleID IS NOT NULL
            AND (i.relations LIKE '||{_%}'
            OR i.relations LIKE '&{_%}')
        ";
        $query = $db->query($sql);
        while($image = $query->fetch(PDO::FETCH_ASSOC)) {
            preg_match('#(.+){(.+)}#', $image['relations'], $match);
            $orRelation = $match[1] == '||';
            $relations = explode('/', $match[2]);
            $options = array();
            foreach($relations as $option) {
                list($group, $option) = explode(':', $option);
                $optionQuery->execute(array(
                    'group' => $group,
                    'option' => $option,
                    'setId' => $image['setId']
                ));
                $optionId = $optionQuery->fetchColumn();
                if($optionId !== false) {
                    $options[] = $optionId;
                }
            }
            if(empty($options)) {

            } elseif($orRelation) {
                foreach ($options as $optionId) {
                    $mappingQuery->execute(array(
                        'imageId' => $image['imageId']
                    ));
                    $mappingId = $db->lastInsertId();
                    $ruleQuery->execute(array(
                        'mappingId' => $mappingId,
                        'optionId' => $optionId
                    ));
                    $sql = "
			        	INSERT INTO s_articles_img ( parent_id, article_detail_id )
			        	SELECT :imageId, article_id
			        	FROM s_article_configurator_option_relations
			        	WHERE option_id = :optionId
			        ";
                    $imageQuery = $db->prepare($sql);
                    $imageQuery->execute(array(
                        'imageId' => $image['imageId'],
                        'optionId' => $optionId
                    ));
                }
            } else {
                $mappingQuery->execute(array(
                    'imageId' => $image['imageId']
                ));
                $mappingId = $db->lastInsertId();
                foreach ($options as $optionId) {
                    $ruleQuery->execute(array(
                        'mappingId' => $mappingId,
                        'optionId' => $optionId
                    ));
                }
                $params = array(
                    'imageId' => $image['imageId']
                );
                $sql = "
		        	INSERT INTO s_articles_img ( parent_id, article_detail_id )
		        	SELECT :imageId, d.id
		        	FROM s_articles_details d
		        ";
                foreach ($options as $i => $optionId) {
                    $sql .= "
            			JOIN s_article_configurator_option_relations r$i
			        	ON r$i.option_id = :option$i
            			AND r$i.article_id = d.id
            		";
                    $params['option' . $i] = $optionId;
                }
                $imageQuery = $db->prepare($sql);
                $imageQuery->execute($params);
            }

            $sql = "
	        	UPDATE s_articles_img
	        	SET relations = ''
	        	WHERE id = :imageId
	        ";
            $imageQuery = $db->prepare($sql);
            $imageQuery->execute(array(
                'imageId' => $image['imageId'],
            ));
        }
        return array(
            'message' => 'Das Bilder-Mapping wurde erfolgreich übernommen.',
            'success' => true
        );
    }

    public function progressCleanup()
    {
        $sourceDir = $this->config('sourceDir');
        $targetDir = $this->config('targetDir');
        $updateDirs = $this->config('updateDirs');

        if(file_exists($sourceDir . 'shopware.php')) {
            rename($sourceDir . 'shopware.php', $targetDir . 'shopware.php');
        }

        try {
            foreach(array_reverse($updateDirs) as $updateDir) {
                rmdir($sourceDir . $updateDir);
            }
        } catch(Exception $e) {
            return array(
                'message' => 'Das Update-Verzeichnis "update/source/" konnte nicht gelöscht werden.<br>' .
                    'Bitte löschen Sie das Verzeichnis manuell. Danach ist das Update abgeschloßen.',
                'success' => false,
            );
        }

        return array(
            'message' => 'Die Update-Dateien wurden erfolgreich gelöscht.',
            'success' => true
        );
    }

    public function progressConfig()
    {
        $backupDir = $this->config('backupDir');
        $targetDir = $this->config('targetDir');
        $configFile = 'config.php';
        $config = $this->initDbConfig();

        if(!is_writable($targetDir)) {
            $msg = 'Die Konfiguration kann nicht übernommen werden.<br>' .
                'Die Datei "config.php" ist nicht schreibbar.';
            return array(
                'message' => $msg,
                'success' => false
            );
        }

        if(!file_exists($backupDir . $configFile)) {
            if(file_exists($targetDir . $configFile)) {
                rename($targetDir . $configFile, $backupDir . $configFile);
            }
            $template = '<?php return ' . var_export(array(
                'db' => $config
            ), true) . ';';
            file_put_contents($targetDir . $configFile, $template);
        }

        return array(
            'message' => 'Die Konfiguration wurde erfolgreich übernommen.',
            'success' => true
        );
    }

    public function progressCategory()
    {
        /** @var $db PDO */
        $db = $this->config('db');

        $sql = "SELECT COUNT(*) FROM s_categories c WHERE c.left = 0 LIMIT 1";
        $count = $db->query($sql)->fetchColumn();
        if(empty($count)) {
            return array(
                'success' => true
            );
        }

        $sql = 'UPDATE s_categories c SET c.left = 0, c.right = 0, c.level = 0';
        $db->exec($sql);
        $sql = 'UPDATE s_categories c SET c.left = 1, c.right = 2 WHERE c.id = 1';
        $db->exec($sql);

        $categoryIds = array(1);
        while(($categoryId = array_shift($categoryIds)) !== null) {
            $sql = 'SELECT c.right, c.level FROM s_categories c WHERE c.id = :categoryId';
            $query = $db->prepare($sql);
            $query->execute(array('categoryId' => $categoryId));
            list($right, $level) = $query->fetch(PDO::FETCH_NUM);

            $sql = 'SELECT c.id FROM s_categories c WHERE c.parent = :categoryId';
            $query = $db->prepare($sql);
            $query->execute(array('categoryId' => $categoryId));
            $childrenIds = $query->fetchAll(PDO::FETCH_COLUMN);
            if(empty($childrenIds)) {
                continue;
            }

            foreach($childrenIds as $childrenId) {
                $sql = 'UPDATE s_categories c SET c.right = c.right + 2 WHERE c.right >= :right';
                $db->prepare($sql)->execute(array('right' => $right));
                $sql = 'UPDATE s_categories c SET c.left = c.left + 2 WHERE c.left > :right';
                $db->prepare($sql)->execute(array('right' => $right));

                $sql = '
                    UPDATE s_categories c
                    SET c.left = :right, c.right = :right + 1, c.level = :level + 1
                    WHERE c.id = :childrenId
                ';
                $db->prepare($sql)->execute(array(
                    'right' => $right, 'level' => $level,
                    'childrenId' => $childrenId
                ));

                $right += 2;
            }
            $categoryIds = array_merge($childrenIds, $categoryIds);
        }

        $sql = 'DELETE c FROM s_categories c WHERE c.left = 0';
        $db->exec($sql);

        return array(
            'message' => 'Der Kategoriebaum wurde erfolgreich erstellt.',
            'success' => true
        );
    }

    public function progressOther()
    {
        /** @var $db PDO */
        $db = $this->config('db');

        // Import translations
        $sql = "
            SELECT t.id, t.objectdata as value
            FROM s_core_translations t
        ";
        $query = $db->query($sql);
        $sql = 'UPDATE s_core_translations t SET t.objectdata = :value WHERE t.id = :id';
        $updateQuery = $db->prepare($sql);
        while(($row = $query->fetch(PDO::FETCH_ASSOC)) !== false) {
            if(@unserialize($row['value']) !== false) {
                continue;
            }
            $value = @unserialize(utf8_decode($row['value']));
            if(is_array($value)) {
                array_walk_recursive($value, function(&$input) {
                    if(is_string($input)) {
                        $input = utf8_encode($input);
                    }
                });
                $row['value'] = serialize($value);
                $updateQuery->execute($row);
            }
        }

        // Import router last update
        $sql = "SELECT `value` FROM `backup_s_core_config` WHERE `name` = 'sROUTERLASTUPDATE'";
        $lastUpdate = $db->query($sql)->fetchColumn();
        $lastUpdate = @unserialize($lastUpdate);
        if(!empty($lastUpdate)) {
            $sql = "SELECT `id` FROM `s_core_config_elements` WHERE `name` = 'routerlastupdate'";
            $elementId = $db->query($sql)->fetchColumn();
            $sql = "DELETE FROM `s_core_config_values` WHERE `element_id` = :elementId";
            $db->prepare($sql)->execute(array('elementId' => $elementId));
            $sql = '
              INSERT INTO `s_core_config_values` (`element_id`, `shop_id`, `value`)
              VALUES (:elementId, :shopId, :value);
            ';
            $updateQuery = $db->prepare($sql);
            foreach($lastUpdate as $shopId => $value) {
                $updateQuery->execute(array(
                    'elementId' => $elementId,
                    'shopId' => $shopId,
                    'value' => serialize($value)
                ));
            }
        }

        // Import page groups
        $sql = "SELECT `value` FROM `backup_s_core_config` WHERE `name` = 'sCMSPOSITIONS'";
        $groups = $db->query($sql)->fetchColumn();
        if(!empty($groups)) {
            $groups = explode(';', $groups);
            $sql = '
                SELECT 1 FROM `s_cms_static_groups` WHERE `key` = :key
            ';
            $selectQuery = $db->prepare($sql);
            $sql = '
               INSERT INTO `s_cms_static_groups` ( `name`, `key`, `active`)
               VALUES (:name, :key, 1);
            ';
            $insertQuery = $db->prepare($sql);
            foreach($groups as $group) {
                list($name, $key) = explode(':', $group);
                $key = trim($key);
                if(empty($key) || empty($name)) {
                    continue;
                }
                $selectQuery->execute(array('key' => $key));
                if($selectQuery->fetchColumn()) {
                    continue;
                }
                $insertQuery->execute(array(
                    'name' => $name,
                    'key' => $key
                ));
            }
        }

        return array(
            'message' => 'Die Übersetzungen wurden erfolgreich übernommen.',
            'success' => true
        );
    }

    public function progressLicense()
    {
        $info = $this->session('license');
        if(empty($info)) {
            return  array(
                'success' => true
            );
        }

        try {
            $this->doLicensePluginDownload();
            $this->doLicensePluginInstall($info);
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
        return array(
            'message' => 'Das Lizenz-Plugin wurde erfolgreich installiert.',
            'success' => true
        );
    }

    public function progressTable()
    {
        $db = $this->initDb();
        $sql = "SHOW TABLES LIKE 'backup_%';";
        $query = $db->query($sql);
        $tables = $query->fetchAll(PDO::FETCH_COLUMN, 0);
        foreach($tables as $table) {
            $sql = "DROP TABLE `$table`";
            $db->exec($sql);
        }

        return array(
            'success' => true
        );
    }

    public function progressCache()
    {
        $path = $this->config('targetDir');
        $dirs = array(
            'cache/database/',
            'cache/templates/',
            'engine/Shopware/Proxies/'
        );
        foreach($dirs as $dir) {
            $this->clearPath($path . $dir);
        }
        return array(
            'message' => 'Der Datei/Proxy-Cache wurde geleert.',
            'success' => true
        );
    }

    /**
     * @param $deletePath
     * @return bool
     */
    protected function clearPath($deletePath)
    {
        $dirIterator = new RecursiveDirectoryIterator($deletePath);
        $iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::CHILD_FIRST);
        $success = true;
        foreach ($iterator as $file) {
            $path = $file->getPathname();
            if ($file->isDir()) {
                if (!$iterator->isDot()) {
                    $success && @rmdir($path);
                }
            } else {
                $success = $success && @unlink($path);
            }
        }
        return $success;
    }

    /**
     * @return  array
     */
    public function getCustomList()
    {
        $backup = !file_exists($this->config('sourceDir'));
        $plugins = $this->getPluginList($backup);
        $modules = $this->getModuleList($backup);
        $payments = $this->getPaymentList($backup);
        $connectors = $this->getConnectorList($backup);
        $customs = array_merge($plugins, $modules, $connectors, $payments, $plugins);

        if(empty($customs)) {
            return $customs;
        }

        $method = "product";
        $query = array (
            'order' => array ('field' => 'a.datum', 'direction' => 'desc'),
            'criterion' =>
            array (
                //'version' => array(4000),
                'pluginName' => array_keys($customs),
            ),
        );
        $soreResults = $this->doStoreApiRequest($method, $query);
        if(!empty($soreResults['products'])) {
            foreach($soreResults['products'] as $product) {
                if(!isset($product['plugin_names'][0])) {
                    continue;
                }
                $name = $product['plugin_names'][0];
                $customs[$name]['author'] = $product['supplierName'];
                $customs[$name]['label'] = $product['name'];
                $customs[$name]['link'] = $product['attributes']['store_url'];
                if(!empty($product['attributes']['shopware_compatible'])
                    && strpos($product['attributes']['shopware_compatible'], '4.0.') !== false) {
                    $customs[$name]['updateVersion'] = $product['attributes']['version'];
                } else {
                    $customs[$name]['updateVersion'] = '';
                }
            }
        }
        foreach ($customs as $name => $custom) {
            if(in_array($name, array('Heidelpay', 'SwagButtonSolution', 'SwagLangLite'))) {
                unset($customs[$name]['id']);
                $customs[$name]['updateVersion'] = 'default';
            }
            if($custom['label'] == '[plugin_name]') {
                $customs[$name]['label'] = $name;
            }
            if(empty($custom['link']) || $custom['link'] == 'http://www.shopware.de/') {
                $customs[$name]['link'] = $this->config('storeLink') . urlencode($name);
            }
        }
        return $customs;
    }

    /**
     * @param   bool $backup
     * @return  array
     */
    public function getPluginList($backup = false)
    {
        $backupDir = $this->config('backupDir');
        $targetDir = $this->config('targetDir');
        $baseDir = 'engine/Shopware/Plugins/';
        /** @var $db PDO */
        $db = $this->config('db');
        $table = $backup ? 'backup_s_core_plugins' : 's_core_plugins';

        $sql = "
            SELECT
              p.name, p.id, p.label, p.description,
              p.active, p.version, p.link,
              p.source, p.namespace, p.name
            FROM $table p
            WHERE p.source IN ('Community', 'Local')
            ORDER BY p.source, p.label
        ";
        $query = $db->prepare($sql);
        $query->execute();
        if($query === false) {
            return array();
        }
        $plugins = $query->fetchAll(PDO::FETCH_ASSOC);

        $result = array();
        foreach($plugins as $plugin) {
            $pluginPath = "$baseDir{$plugin['source']}/{$plugin['namespace']}/{$plugin['name']}/";
            $pluginFile = $pluginPath . 'Bootstrap.php';
            if(file_exists($backupDir . $pluginFile)) {
                $plugin['compatibility'] = $this->doCompatibilityCheck($backupDir . $pluginFile);
            } elseif(file_exists($targetDir . $pluginFile)) {
                $plugin['compatibility'] = $this->doCompatibilityCheck($targetDir . $pluginFile);
            } else {
                continue;
            }
            $result[$plugin['name']] = $plugin;
        }
        return $result;
    }

    /**
     * @param   bool $backup
     * @return  array
     */
    public function getModuleList($backup = false)
    {
        $mapping = array(
            'sGROUPS'           => array('name' => 'SwagBusinessEssentials'),
            'sFUZZY'            => array('name' => 'SwagFuzzy'),
            'sMAILCAMPAIGNS'    => array('name' => 'SwagNewsletter', 'updateVersion' => ''),
            'sTICKET'           => array('name' => 'SwagTicketSystem', 'updateVersion' => ''),
            'sBUNDLE'           => array('name' => 'SwagBundle', 'updateVersion' => ''),
            'sLIVE'             => array('name' => 'SwagLiveshopping', 'updateVersion' => ''),
            'sLANGUAGEPACK'     => array('name' => 'SwagMultiShop'),
            'sPRICESEARCH'      => array('name' => 'SwagProductExport', 'label' => 'Produkt-Exporte', 'updateVersion' => 'default'),
            'sARTICLECONF'      => array('name' => 'SwagConfigurator', 'label' => 'Artikel Konfigurator', 'updateVersion' => 'default')
        );
        /** @var $db PDO */
        $db = $this->config('db');
        $table = $backup ? 'backup_s_core_licences' : 's_core_licences';
        $sql = "
            SELECT
              IF(l.module LIKE 'sLANGUAGEPACK%', 'sLANGUAGEPACK', l.module) as licence,
              MAX(IF(l.inactive=0, 1, 0)) as active,
              MIN(l.module LIKE '%-J%') as type
            FROM $table l
            GROUP BY licence
        ";
        $query = $db->prepare($sql);
        $query->execute();
        if($query !== false) {
            $modules = $query->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $modules = array();
        }
        $result = array();
        foreach($modules as $module) {
            if(isset($mapping[$module['licence']])) {
                $module = array_merge($module, $mapping[$module['licence']]);
                if(!isset($module['source'])) {
                    $module['source'] = 'Module';
                }
                if(!isset($module['label'])) {
                    $module['label'] = substr($module['name'], 4);
                }
                $result[$module['name']] = $module;
            }

        }
        return $result;
    }

    /**
     * @param   bool $backup
     * @return  array
     */
    public function getConnectorList($backup = false)
    {
        $backupDir = $this->config('backupDir');
        $targetDir = $this->config('targetDir');
        $baseDir = $backup ? $backupDir : $targetDir;
        $connectors = glob($baseDir . 'engine/connectors/*', GLOB_ONLYDIR);
        $connectors = array_map('basename', $connectors);
        $connectors = array_diff($connectors, array(
            'api', 'clickandbuy', 'export', 'ipayment',
            'moneybookers', 'paypalexpress', 'saferpay', 'sofort'
        ));
        $result = array();
        foreach($connectors as $connector) {
            $result[$connector] = array(
                'label' => 'Schnittstelle: ' . ucfirst($connector),
                'name' => $connector,
                'source' => 'Connector',
                'active' => true
            );
        }
        return $result;
    }

    /**
     * @param   bool $backup
     * @return  array
     */
    public function getPaymentList($backup = false)
    {
        $db = $this->config('db');
        $table = $backup ? 'backup_s_core_paymentmeans' : 's_core_paymentmeans';
        $sql = "
            SELECT p.name, p.description as label, p.active
            FROM $table p
            JOIN s_order o
            ON o.paymentID = p.id
            WHERE p.embediframe != ''
            GROUP BY p.id
            ORDER BY label
        ";
        $query = $db->prepare($sql);
        $query->execute();
        if($query == false) {
            return array();
        }
        $modules = $query->fetchAll(PDO::FETCH_ASSOC);
        $result = array();
        foreach($modules as $module) {
            if($module['name'] == 'paypalexpress') {
                $module['name'] = 'paypal';
            }
            if(in_array($module['name'], array('paypal', 'ipayment'))) {
                $module['updateVersion'] = $module['name'] == 'paypal' ? 'default' : null;
                $module['name'] = 'SwagPayment' . ucfirst($module['name']);
            } else {
                $module['link'] = $this->config('storeLink') . urlencode($module['label']);
            }
            $result[$module['name']] = array_merge($module, array(
                'label' => 'Zahlungsart: ' . $module['label'],
                'source' => 'Payment'
            ));
        }
        return $result;
    }

    /**
     * @param string $method
     * @param array $query
     * @return mixed
     */
    public function doStoreApiRequest($method, $query)
    {
        $url = 'http://store.shopware.de/storeApi';
        $data = array(
            'method' => 'call',
            'arg0' => 'GET',
            'arg1' => $method,
            'arg2' => json_encode($query),
            'rest' => 1
        );
        $data = http_build_query($data, '', '&');
        $options = array('http' => array(
            'method' => 'POST',
            'user_agent' => $this->request()->getUserAgent(),
            'header' => "Content-type: application/x-www-form-urlencoded\r\n"
                . "Content-Length: " . strlen($data) . "\r\n"
                . 'Referer: ' . $this->request()->getUrl() . "\r\n",
            'content' => $data
        ));
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if(($start = strpos($result, '<_search_result>')) === false) {
            return null;
        }
        $result = substr($result, $start + strlen('<_search_result>'));
        $result = substr($result, 0, strpos($result, '</_search_result>'));
        $result = json_decode(htmlspecialchars_decode($result), true);
        if($result === null) {
            return null;
        }
        foreach($result as $key => $data) {
            if(strpos($key, '_') === 0) {
                $result[substr($key, 1)] = json_decode($data, true);
                unset($result[$key]);
            }
        }
        return $result;
    }

    /**
     * @param $file
     * @return array
     */
    public function doCompatibilityCheck($file)
    {
        $pluginContent = file_get_contents($file);
        $tests = array(
            'zend' => '<\?php @Zend;',
            'ioncube' => '<\?php //0046a',
            'license' => 'License\(\)->checkLicense',
            'config' => 's_core_config[^_]|Config\(\)->Templates|Config\(\)->Snippets',
            'utf8' => 'utf8_decode|utf8_encode',
            'checkout_button' => 'frontend_checkout_confirm_agb',
            'attribute' => 'ob_attr|od_attr|s_user_billingaddress.text1|ac_attr',
            'bootstrap' => 'function getName|function getSource',
            'db' => 'config\.php',
            'global' => '\$_GET|\$_POST',
            'this' => 'Enlight_Class::Instance',
            'document_root' => '\$_SERVER\[.DOCUMENT_ROOT.\]',
            'shop' => 'Shop\(\)->Locale\(\)|Shop\(\)->Config\(\)|Session\(\)->Shop|Shopware_Models_',
            'api' =>'Shopware\(\)->Api',
            'template_engine' => 'register_modifier',
            'backend' => 'backend_index_javascript'
        );
        foreach($tests as $name => $test) {
            $tests[$name] = '?<' . $name . '>' . $test;
        }
        $tests = '#(' . implode(')|(', $tests) . ')#i';
        $compatibility = array();
        if(preg_match_all($tests, $pluginContent, $matches)) {
            foreach($matches as $name => $match) {
                if(!is_int($name)) {
                    $match = array_diff($match, array(''));
                    if(count($match) > 0) {
                        $compatibility[] = $name;
                    }
                }
            }
        }
        return $compatibility;
    }

    /**
     * @param $host
     * @param $product
     * @param $license
     * @return array
     */
    public function doLicenseCheck($host, $product, $license)
    {
        if($product == 'CE') {
            return array('success' => true);
        }
        if(empty($license)) {
            return array('success' => false, 'error' => 'EMPTY');
        }
        $url = 'http://store.shopware.de/downloads/check_license';
        $data = array(
            'license' => $license,
            'host' => $host,
            'product' => $product
        );
        $data = http_build_query($data, '', '&');
        $options = array('http' => array(
            'method' => 'POST',
            'user_agent' => $this->request()->getUserAgent(),
            'header' => "Content-type: application/x-www-form-urlencoded\r\n"
                . "Content-Length: " . strlen($data) . "\r\n"
                . 'Referer: ' . $this->request()->getUrl() . "\r\n",
            'content' => $data
        ));
        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        $response = json_decode($response, true);
        return $response;
    }

    /**
     * @throws Exception
     */
    public function doLicensePluginInstall($info)
    {
        /** @var $db PDO */
        $db = $this->config('db');

        $sql = "DELETE FROM s_core_licenses WHERE module = 'SwagCommercial'";
        $db->query($sql);

        $sql = "
            INSERT INTO s_core_licenses (
              module, host, label, license, version, type,
              source, added, creation, expiration, active
            ) VALUES (
                ?, ?, ?, ?, ?, ?,
                0, NOW(), NOW(), ?, 1
            )
        ";
        $query = $db->prepare($sql);
        $query->execute(array(
            $info['module'],
            $info['host'],
            isset($info['label']) ? $info['label'] : $info['module'],
            $info['license'],
            $info['version'],
            $info['type'],
            substr($info['expiration'], 0, 4) . '-' .
                substr($info['expiration'], 4, 2) . '-' .
                substr($info['expiration'], 6, 2)
        ));

        $sql = "
            INSERT IGNORE INTO s_core_plugins (
              namespace, name, label, source, active, added,
              installation_date, update_date, refresh_date,
              author, copyright, version,
              capability_update, capability_install, capability_enable
            ) VALUES (
              ?, ? ,?, ?, ?, NOW(),
              NOW(), NOW(), NOW(),
              ?, ?, ?, ?, ?, ?
            )
        ";
        $data = array(
            'Core',
            'SwagLicense',
            'Lizenz-Manager',
            'Community',
            1,
            'shopware AG',
            'Copyright © 2012, shopware AG',
            '1.0.2',
            1, 1, 1
        );
        $query = $db->prepare($sql);
        $query->execute($data);

        $sql = "SELECT id FROM s_core_plugins WHERE name = 'SwagLicense'";
        $pluginId = $db->query($sql)->fetchColumn();

        $sql = "
            INSERT IGNORE INTO `s_core_config_forms` (`id`, `parent_id`, `name`, `label`, `description`, `position`, `scope`, `plugin_id`) VALUES
            (NULL, 92, 'license', 'Lizenz-Manager', NULL, 0, 0, ?);
        ";
        $query = $db->prepare($sql);
        $query->execute(array(
            $pluginId
        ));
        $sql = "
            INSERT IGNORE INTO s_core_subscribes (subscribe, listener, pluginID)
            VALUES (?, ?, ?)
        ";
        $query = $db->prepare($sql);
        $query->execute(array(
            'Enlight_Bootstrap_InitResource_License',
            'onInitResourceLicense', $pluginId
        ));
        $query->execute(array(
            'Enlight_Controller_Action_PostDispatch_Backend_Index',
            'onPostDispatchBackendIndex', $pluginId
        ));
        $query->execute(array(
            'Enlight_Controller_Front_DispatchLoopStartup',
            'onDispatchLoopStartup', $pluginId
        ));
        $query->execute(array(
            'Enlight_Controller_Action_PostDispatch_Backend_Config',
            'onPostDispatchBackendConfig', $pluginId
        ));
        $query->execute(array(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_License',
            'onGetControllerPathBackend', $pluginId
        ));
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function doLicensePluginDownload()
    {
        $url = 'http://store.shopware.de/downloads/get_license_plugin/shopwareVersion/4000';

        $targetDir = $this->config('targetDir');
        $tempDir = $targetDir. 'media/temp/';
        $pluginFile = $tempDir . 'plugin' . md5($url) . '.zip';
        $pluginDir = $targetDir. 'engine/Shopware/Plugins/Community/';

        if (!file_exists($pluginDir) || !is_writable($pluginDir)) {
            throw new Exception('Plugin dir does not exists or is not writable.');
        }
        if (!file_exists($tempDir) || !is_writable($tempDir)) {
            throw new Exception('Temp dir does not exists or is not writable.');
        }

        $options = array('http' => array(
            'user_agent' => $this->request()->getUserAgent(),
            'header' => 'Referer: ' . $this->request()->getUrl() . "\r\n"
        ));
        $context = stream_context_create($options);
        $stream = fopen($url, 'rb', false, $context);

        file_put_contents($pluginFile, $stream);
        try {
            if (!class_exists('ZipArchive')) {
                throw new Exception('Zip extension not be found failure.');
            }
            $zip = new ZipArchive();
            if (!$zip->open($pluginFile)) {
                throw new Exception('Plugin file can not be open failure.');
            }
            $zip->open($pluginFile);
            if (!$zip->extractTo($pluginDir)) {
                throw new Exception('Plugin file can not be extract failure.');
            }
        } catch(Exception $e) {
            if(isset($zip)) {
                $zip->close();
            }
            if(file_exists($pluginFile)) {
                unlink($pluginFile);
            }
            throw $e;
        }
        unlink($pluginFile);
    }

    /**
     * @param $class
     */
    public static function autoload($class)
    {
        if (strpos($class, 'Shopware') !== 0) {
            return;
        }
        $file = dirname(__FILE__) . '/' .
                str_replace('_', DIRECTORY_SEPARATOR, substr($class, 9)) .
                '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
}
