<?php
class Shopware_Install extends Slim
{
    const VERSION = '1.0.0';
    const UPDATE_VERSION = '4.0.3';

    public function initDb()
    {
        $config = include 'config.php';
        $config = isset($config['db']) ? $config['db'] : array();
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
        return include 'assets/translation/' . $this->config('language') .'.php';
    }

    public function __construct($userSettings = array())
    {
        parent::__construct($userSettings);

        spl_autoload_register(array('Shopware_Install', 'autoload'));

        $this->contentType('Content-type: text/html; charset=utf-8');

        $this->config('db', $this->initDb());
        $this->config('currentVersion', $this->initCurrentVersion());
        $this->config('updateVersion', self::UPDATE_VERSION);
        $this->config('language', 'de');

        $this->view()->appendData(array(
            'app' => $this,
            'translation' => $this->initTranslation(),
            'language' => $this->config('language')
        ));

        $app = $this;
        $this->get('/', function () use ($app) {
            $app->render('index.php', array(
                'action' => 'index'
            ));
        })->via('GET', 'POST')->name('index');

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
                'action' => 'database',
                'app' => $app,
                'error' => false
            ));
        })->via('GET', 'POST')->name('database');

        $this->get('/backupDatabase', array($this, 'backupDatabaseAction'))
             ->via('GET', 'POST')->name('backupDatabase');

        $this->get('/updateDatabase', array($this, 'updateDatabaseAction'))
            ->via('GET', 'POST')->name('updateDatabase');

        $this->get('/restoreDatabase', array($this, 'restoreDatabaseAction'))
            ->via('GET', 'POST')->name('restoreDatabase');

        $this->get('/downloadDatabase', array($this, 'downloadDatabaseAction'))
            ->via('GET', 'POST')->name('downloadDatabase');

        $this->get('/diffDatabase', array($this, 'diffDatabaseAction'))
            ->via('GET', 'POST')->name('diffDatabase');

        $this->get('/updateMedia', array($this, 'updateMediaAction'))
            ->via('GET', 'POST')->name('updateImage');

        $this->get('/finish', function () use ($app) {
            $app->render('finish.php', array(
                'action' => 'finish',
                'app' => $app
            ));
        })->via('GET', 'POST')->name('finish');
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
            's_plugin_coupons_codes'
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
            's_core_log' => array(
                'datum' => 'date'
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
        if(!is_writable('backup/')) {
            echo json_encode(array(
                'message' => 'Das Backup-Verzeichnis "update/backup" beschreibbar!',
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
            $file = 'backup/database.php';
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

    public function updateMediaAction()
    {
        /** @var $db PDO */
        $db = $this->config('db');
        $dirs = array(
            -1 => array('images/articles/', 'media/image/'),
            -2 => array('images/banner/', 'media/banner/'),
            -10 => array('files/downloads/', 'media/unknown/'),
            -12 => array('images/supplier/', 'media/image/'),
        );
        $baseDir = realpath('../') . DIRECTORY_SEPARATOR;

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
            echo json_encode(array(
                'message' => $msg,
                'success' => false
            ));
            return;
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
        echo json_encode(array(
            'message' => 'Artikel-Bilder wurder erfolgreich übernommen.',
            'success' => true
        ));
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
