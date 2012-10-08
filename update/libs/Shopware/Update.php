<?php
class Shopware_Update extends Slim
{
    const VERSION = '1.0.0';
    const UPDATE_VERSION = '4.0.3';

    public function initDb()
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
        $db->exec("SET NAMES 'utf8' COLLATE 'utf8_unicode_ci'; SET FOREIGN_KEY_CHECKS = 0;");
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
        $version = $query ? $query->fetchColumn(0) : '4.0.3';
        return $version;
    }

    public function initTranslation()
    {
        return include 'assets/translation/' . $this->config('language') . '.php';
    }

    public function initPath()
    {
        return realpath('../') . DIRECTORY_SEPARATOR;
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
            'package' => 'install',
            'format' => 'zip',
            'sourceDir' => realpath('.') . DIRECTORY_SEPARATOR . 'source' . DIRECTORY_SEPARATOR,
            'backupDir' => realpath('backup/') . DIRECTORY_SEPARATOR,
            'targetDir' => realpath('../') . DIRECTORY_SEPARATOR,
            'updatePaths' => array(
                'cache/',
                'engine/',
                'media/',
                'snippets/',
                'templates/',
                'Application.php',
                'shopware.php',
                '.htaccess'
            )
        ));

        $this->view()->appendData(array(
            'app' => $this,
            'translation' => $this->initTranslation(),
            'language' => $this->config('language')
        ));

        $app = $this;

        $this->hook('slim.before.router', function () use ($app) {
            if($app->config('auth') === null && $app->request()->getPathInfo() !== '/') {
                $this->redirect($this->urlFor('index'));
            }
        });

        $this->get('/', function () use ($app) {
            $app->render('index.php', array(
                'action' => 'index'
            ));
        })->via('GET')->name('index');

        $this->get('/', array($this, 'loginAction'))->via('POST')->name('login');

        $this->get('/system', function () use ($app) {
            $system = new Shopware_Components_Check_System();
            $app->render('system.php', array(
                'action' => 'system',
                'system' => $system,
                'error' => false
            ));
        })->via('GET', 'POST')->name('system');

        $this->get('/main', function () use ($app) {
            $app->render('main.php', array(
                'action' => 'main',
                'app' => $app,
                'error' => false
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
            $backupDir = $app->config('backupDir');
            $targetDir = $app->config('targetDir');
            /** @var $db PDO */
            $db = $app->config('db');

            $templates = glob($backupDir . 'templates/*', GLOB_ONLYDIR);
            $templates = array_map('basename', $templates);
            $targetTemplates = glob($targetDir . 'templates/*', GLOB_ONLYDIR);
            $targetTemplates = array_map('basename', $targetTemplates);
            $templates = array_diff($templates, $targetTemplates);

            $pluginDirs = array(
                'engine/Shopware/Plugins/Community/Backend/',
                'engine/Shopware/Plugins/Community/Core/',
                'engine/Shopware/Plugins/Default/Frontend/',
                'engine/Shopware/Plugins/Local/Backend/',
                'engine/Shopware/Plugins/Local/Core/',
                'engine/Shopware/Plugins/Local/Frontend/'
            );
            $pluginPaths = array();
            foreach($pluginDirs as $pluginDir) {
                $pluginPaths = array_merge($pluginPaths, glob($backupDir . $pluginDir . '*', GLOB_ONLYDIR));
            }
            $plugins = array();
            foreach($pluginPaths as $pluginPath) {
                $pluginFile = $pluginPath . '/Bootstrap.php';
                if(!file_exists($pluginFile)) {
                    continue;
                }
                $plugin = array();
                preg_match('#(\w+)/(\w+)/(\w+)$#', $pluginPath, $match);
                list(, $plugin['source'], $plugin['namespace'], $plugin['name']) = $match;
                $sql = '
                    SELECT
                      p.id, p.label, p.description, p.installation_date as installed,
                      p.active, p.autor as author, p.version, p.link,
                      p.source, p.namespace, p.name
                    FROM backup_s_core_plugins p
                    WHERE p.namespace = :namespace AND p.name = :name AND p.source = :source
                ';
                $query = $db->prepare($sql);
                $query->execute($plugin);
                $plugin = $query->fetch(PDO::FETCH_ASSOC);
                if($plugin === false) {
                    continue;
                }
                $pluginContent = file_get_contents($pluginFile);
                $tests = array(
                    'zend' => '<\?php @Zend;',
                    'ioncube' => '<\?php //0046a',
                    'license' => 'License\(\)->checkLicense',
                    'config' => 's_core_config[^_]|Config\(\)->Templates|Config\(\)->Snippets',
                    'utf8' => 'utf8_decode|utf8_encode',
                    'checkout_button' => 'frontend_checkout_confirm_agb',
                    'attribute' => 'ob_attr|od_attr|s_user_billingaddress.text1',
                    'bootstrap' => 'function getName|function getSource',
                    'db' => 'config\.php',
                    'global' => '\$_GET|\$_POST',
                    'this' => 'Enlight_Class::Instance',
                    'document_root' => '\$_SERVER\[.DOCUMENT_ROOT.\]',
                    'shop' => 'Shop\(\)->Locale\(\)|Shop\(\)->Config\(\)|Session\(\)->Shop|Shopware_Models_',
                    'api' =>'Shopware\(\)->Api',
                    'template_engine' => 'register_modifier'
                );
                foreach($tests as $name => $test) {
                    $tests[$name] = '?<' . $name . '>' . $test;
                }
                $tests = '#(' . implode(')|(', $tests) . ')#i';
                $plugin['compatibility'] = array();
                if(preg_match_all($tests, $pluginContent, $matches)) {
                    foreach($matches as $name => $match) {
                        if(!is_int($name)) {
                            $match = array_diff($match, array(''));
                            if(count($match) > 0) {
                                $plugin['compatibility'][] = $name;
                            }
                        }
                    }
                }
                $plugins[] = $plugin;
            }

            $connectors = glob($backupDir . 'engine/connectors/*', GLOB_ONLYDIR);
            $connectors = array_map('basename', $connectors);
            $connectors = array_diff($connectors, array(
                'api', 'clickandbuy', 'export', 'ipayment',
                'moneybookers', 'paypalexpress', 'saferpay', 'sofort'
            ));

            $sql = "
                SELECT `value`
                FROM `backup_s_core_config`
                WHERE `name` = 'sCONFIGCUSTOMFIELDS'
                OR (
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
            $fields = $query->fetchColumn();
            if($fields !== false) {
                $fields = explode(',', $fields);
            }

            $sql = '
                SELECT name, label
                FROM s_core_engine_elements
                WHERE variantable = 1
            ';
            $query = $db->query($sql);
            $targetFields = $query->fetchAll(PDO::FETCH_KEY_PAIR);
            $targetFields['weight'] = 'Gewicht';
            $targetFields['supplierNumber'] = 'Herstellernummer';

            $app->render('custom.php', array(
                'action' => 'custom',
                'app' => $app,
                'templates' => $templates,
                'plugins' => $plugins,
                'connectors' => $connectors,
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
            's_articles_groups_value'
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
            'message' => 'Datenbank-Backup wurde erfolgreich durchgeführt.',
            'success' => true,
            'file' => $file
        ));
    }

    public function updateDatabaseAction()
    {
        set_time_limit(0);
        /** @var $db PDO */
        $db = $this->config('db');
        $version = $this->config('currentVersion');
        if(!file_exists("deltas/$version.sql")) {
            echo json_encode(array(
                'message' => "Das Datenbank-Update unterstützt die Shopware-Version $version nicht.",
                'success' => true
            ));
            return;
        }
        $deltas = glob('deltas/*-*.sql'); natsort($deltas);
        array_unshift($deltas, "deltas/$version.sql");
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
        echo json_encode(array(
            'message' => 'Das Datenbank-Update wurde erfolgreich durchgeführt.',
            'success' => true
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
            'message' => 'Datenbank wurde erfolgreich wiederhergestellt.',
            'success' => true
        ));
    }

    public function downloadDatabaseAction()
    {
        $backup = 'backup/database.php';
        $fp = fopen($backup, 'r');
        $size = filesize($backup) - strlen(fgets($fp));
        $file = basename($backup, '.php') . '.sql';
        $this->response()->header('Content-Type', 'application/force-download');
        $this->response()->header('Content-Disposition', 'attachment; filename="' . $file . '";');
        $this->response()->header('Content-Length', $size);
        echo stream_get_contents($fp);
    }

    public function progressAction()
    {
        $next = (int)$this->request()->post('next') ?: 1;
        switch($next) {
            case 1: $method = 'download'; break;
            case 2: $method = 'unpack'; break;
            case 3: $method = 'move'; break;
            case 4: $method = 'config'; break;
            case 5: $method = 'media'; break;
            case 6: $method = 'category'; break;
            case 7: $method = 'cache'; break;
        }
        $method = 'progress' . ucfirst($method);
        if(method_exists($this, $method)) {
            $result = $this->$method();
            if(!isset($result['offset'])) {
                $next++;
            }
            if(!empty($result['success']) && $next < 7) {
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

        if(file_exists($sourceFile) || !file_exists($sourceDir)) {
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
            'header' => 'Referer: http://' .$this->request()->getUrl() . "\r\n"
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

        while (list($position, $entry) = $source->each()) {
            $name = $sourceDir . $entry->getName();
            $result = true;

            if ($entry->isDir()) {
                if (!file_exists($name)) {
                    $result = mkdir($name);
                }
            } else {
                $result = file_put_contents($name, $entry->getContents()) !== false;
            }

            if (!$result && (!is_dir($name) || !filesize($name))) {
                echo "//$name\n";
            }

            if (time() - $requestTime >= 20 || ($position + 1) % 1000 == 0) {
                return array(
                    'progress' => ($position + 1) . ' von ' . $count . ' Dateien entpackt.',
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
        $targetDir = realpath('../') . DIRECTORY_SEPARATOR;
        $updatePaths = $this->config('updatePaths');

        if(!file_exists($sourceDir)) {
            return array(
                'success' => true,
            );
        }

        foreach($updatePaths as $updatePath) {
            if(file_exists($targetDir . $updatePath)) {
                rename($targetDir . $updatePath, $backupDir . $updatePath);
            }
            if(file_exists($sourceDir . $updatePath)) {
                rename($sourceDir . $updatePath, $targetDir . $updatePath);
            }
        }

        @unlink($sourceDir);

        return array(
            'message' => 'Die Update-Dateien wurden erfolgreich verschoben.',
            'success' => true
        );
    }

    public function progressMedia()
    {
        /** @var $db PDO */
        $db = $this->config('db');
        $dirs = array(
            -1 => array('images/articles/', 'media/image/'),
            -2 => array('images/banner/', 'media/banner/'),
            -2 => array('images/cms/', 'media/banner/'),
            -10 => array('files/downloads/', 'media/unknown/'),
            -12 => array('images/supplier/', 'media/image/'),
        );
        $baseDir = $this->initPath();

        $testDirs = array();
        foreach($dirs as $dir) {
            if(file_exists($baseDir. $dir[0]) && !is_writable($baseDir. $dir[0])) {
                $testDirs[] = $dir[0];
            }
            if(!file_exists($baseDir . $dir[1]) || !is_writable($baseDir. $dir[1])) {
                $testDirs[] = $dir[1];
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

        foreach($dirs as $albumId => $dir) {
            $sql = '
                SELECT `thumbnail_size`
                FROM `s_media_album_settings`
                WHERE `albumID` = :albumId
                AND `create_thumbnails` =1
            ';
            $query = $db->prepare($sql);
            $query->execute(array(':albumId' => $albumId));
            $thumbs = $query->fetchColumn(0);
            if($thumbs !== false) {
                $thumbs = explode(';', $thumbs);
            }
            if(!file_exists($baseDir . $dir[0])) {
                continue;
            }
            $iterator = new DirectoryIterator($baseDir . $dir[0]);
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
                rename($baseDir . $dir[0] . $name, $baseDir . $dir[1] . $newName);
            }
            @rmdir($baseDir . $dir[0]);
        }
        return array(
            'message' => 'Die Artikel-Bilder wurden erfolgreich übernommen.',
            'next' => 'cache',
            'success' => true
        );
    }

    public function progressConfig()
    {
        $DB_HOST = null; $DB_USER = null; $DB_PASSWORD = null; $DB_DATABASE = null;
        $configFile = $this->initPath() . 'config.php';
        $config = include $configFile;

        if($DB_HOST !== null && !is_array($config)) {
            if(!is_writable($configFile)) {
                $msg = 'Die Konfiguration kann nicht übernommen werden.<br>' .
                       'Die Datei "config.php" ist nicht beschreibar.';
                return array(
                    'message' => $msg,
                    'success' => false
                );
            }
            if(strpos($DB_HOST, ':') !== false) {
                list($host, $port) = explode(':', $DB_HOST);
            } else {
                $host = $DB_HOST;
            }
            $config = array(
                'db' => array(
                    'username' => $DB_USER,
                    'password' => $DB_PASSWORD,
                    'dbname' => $DB_DATABASE,
                    'host' => $host
                ),
            );
            if(isset($port)) {
                if(is_numeric($port)) {
                    $config['db']['port'] = $port;
                } else {
                    $config['db']['unix_socket'] = $port;
                }
            }
            $template = '<?php return ' . var_export($config, true) . ';';
            file_put_contents($configFile, $template);
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

        $sql = 'UPDATE s_categories c SET c.left = 0, c.right = 0, c.level = 0';
        $db->exec($sql);
        $sql = 'UPDATE s_categories c SET c.left = 1, c.right = 2 WHERE c.id = 1';
        $db->exec($sql);

        $categoryIds = array(1);
        while(list(, $categoryId) = each($categoryIds)) {
            $sql = 'SELECT c.right, c.level FROM s_categories c WHERE c.id = :categoryId';
            $query = $db->prepare($sql);
            $query->execute(array('categoryId' => $categoryId));
            list($right, $level) = $query->fetch(PDO::FETCH_NUM);

            $sql = 'SELECT c.id FROM s_categories c WHERE c.parent = :categoryId';
            $query = $db->prepare($sql);
            $query->execute(array('categoryId' => $categoryId));
            $childrenIds = $query->fetchAll(PDO::FETCH_COLUMN);

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
                $categoryIds[] = $childrenId;
            }
        }

        $sql = 'DELETE FROM s_categories c WHERE c.left = 0';
        $db->exec($sql);

        return array(
            'message' => 'Der Kategoriebaum wurde erfolgreich erstellt.',
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
        $path = $this->initPath();
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
