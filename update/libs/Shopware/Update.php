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
            'storeLink' => 'http://store.shopware.de/shopware.php/sViewport,search?sSearch=',
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
            ),
            'chmodPaths' => array(
                'cache/database/',
                'cache/templates/',
                'engine/Library/Mpdf/tmp/',
                'engine/Library/Mpdf/ttfontdata/',
                'engine/Shopware/Models/Attribute/',
                'engine/Shopware/Proxies/',
                'engine/Shopware/Plugins/Community/',
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
                'customs' => $app->getCustomList(),
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
                'templates' => $templates,
                'plugins' => $app->getPluginList(false),
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

    public function databaseAction()
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
            case 1: $action = 'cache'; break;
            case 2: $action = 'download'; break;
            case 3: $action = 'unpack'; break;
            case 4: $action = 'move'; break;
            case 5: $action = 'config'; break;
            case 6: $action = 'media'; break;
            case 7: $action = 'category'; break;
            default: $action = 'notFound'; break;
        }
        $method = 'progress' . ucfirst($action);
        if(method_exists($this, $method)) {
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
            if(!empty($result['success']) && $next < 8) {
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

        while (list($position, $entry) = $source->each()) {
            $name = $entry->getName();
            $targetName = $sourceDir . $name;
            $result = true;

            $match = false;
            foreach($updatePaths as $testPath) {
                if(strpos($name, $testPath) === 0) {
                    $match = true;
                    break;
                }
            }
            if(!$match) {
                continue;
            }

            if ($entry->isDir()) {
                if (!file_exists($targetName)) {
                    $result = mkdir($targetName);
                }
            } else {
                $result = file_put_contents($targetName, $entry->getContents()) !== false;
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
        $targetDir = realpath('../') . DIRECTORY_SEPARATOR;
        $updatePaths = $this->config('updatePaths');

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
            -2 => array('images/banner/', 'media/image/'),
            -2 => array('images/cms/', 'media/image/'),
            -10 => array('files/downloads/', 'media/unknown/'),
            -12 => array('images/supplier/', 'media/image/'),
        );
        $baseDir = $this->config('targetDir');

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
        $configFile = $this->config('targetDir') . 'config.php';
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
     * @param   bool $backup
     * @return  array
     */
    public function getCustomList($backup = false)
    {
        $plugins = $this->getPluginList($backup);
        $modules = $this->getModuleList($backup);
        $payments = $this->getPaymentList($backup);
        $connectors = $this->getConnectorList($backup);
        $customs = array_merge($plugins, $modules, $connectors, $payments);

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
                $customs[$name]['updateVersion'] = $product['attributes']['version'];
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
        $baseDir = $backup ? $backupDir : $targetDir;
        $baseDir .= 'engine/Shopware/Plugins/';
        /** @var $db PDO */
        $db = $this->config('db');
        $table = $backup ? 'backup_s_core_plugins' : 's_core_plugins';

        $sql = "
            SELECT
              p.name, p.id, p.label, p.description, p.installation_date as installed,
              p.active, p.autor as author, p.version, p.link,
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
            if(file_exists($pluginFile)) {
                $plugin['compatibility'] = $this->doCompatibilityCheck($pluginFile);
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
            'sMAILCAMPAIGNS'    => array('name' => 'SwagNewsletter'),
            'sTICKET'           => array('name' => 'SwagTicketSystem'),
            'sBUNDLE'           => array('name' => 'SwagBundle'),
            'sLIVE'             => array('name' => 'SwagLiveshopping'),
            'sLANGUAGEPACK'     => array('name' => 'SwagMultiShop'),
            'sPRICESEARCH'      => array('name' => 'SwagProductExport', 'label' => 'Produkt-Exporte', 'version' => 'default'),
            'sARTICLECONF'      => array('name' => 'SwagConfigurator', 'label' => 'Artikel Konfigurator', 'version' => 'default')
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
                if(!isset($module['link'])) {
                    $module['link'] = $this->config('storeLink') . urlencode($module['name']);
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
                'active' => true,
                'link' => $this->config('storeLink') . urlencode($connector)
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
            $result[$module['name']] = array_merge($module, array(
                'label' => 'Zahlungsart: ' . $module['label'],
                'source' => 'Payment',
                'link' => $this->config('storeLink') . urlencode($module['label'])
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
                      . 'Referer: http://' . $this->request()->getUrl() . "\r\n",
            'content' => $data
        ));
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if(!preg_match('#<_search_result>(.+?)</_search_result>#', $result, $match)) {
            return null;
        }
        $match = json_decode(htmlspecialchars_decode($match[1]), true);
        if($match === null) {
            return null;
        }
        foreach($match as $key => $data) {
            if(strpos($key, '_') === 0) {
                $match[substr($key, 1)] = json_decode($data, true);
                unset($match[$key]);
            }
        }
        return $match;
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
