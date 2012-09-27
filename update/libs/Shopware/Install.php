<?php
class Shopware_Install extends Slim
{
    const VERSION = '1.0.0';
    const UPDATE_VERSION = '4.0.3';

    public function initDb()
    {
        $config = include 'config.php';
        $config = isset($config['db']) ? $config['db'] : array();
        $config = array_merge(array('host' => '', 'port' => '', 'password' => ''), $config);
        $db = new PDO(
            "mysql:host={$config['host']};port={$config['port']};dbname=shopware_demo",
            $config['username'], $config['password']
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

    public function __construct($userSettings = array())
    {
        parent::__construct($userSettings);

        spl_autoload_register(array('Shopware_Install', 'autoload'));

        $this->contentType('Content-type: text/html; charset=utf-8');

        $this->config('db', $this->initDb());
        $this->config('source', $this->initSource());

        $this->view()->appendData(array(
            'app' => $this,
            'translation' => include 'assets/translation/de.php',
            'language' => 'de'
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

        $this->get('/update', function () use ($app) {
            $app->render('update.php', array(
                'app' => $app
            ));
        })->via('GET', 'POST')->name('update');

        $this->get('/database', function () use ($app) {
            $app->render('database.php', array(
                'action' => 'database',
                'app' => $app,
                'error' => false
            ));
        })->via('GET', 'POST')->name('database');

        $this->get('/backupDatabase', array($this, 'backupDatabaseAction'))
             ->via('GET', 'POST')->name('backupDatabase');

        $this->get('/updateDatabase', array($this, 'updateDatabaseAction'))
            ->via('GET', 'POST')->name('updateDatabase');

        //Shopware_Components_DbExport_Mysql
    }

    public function createUpdateAction()
    {
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
        );
        $backupTables = array(
            's_core_config',
            's_articles',
            's_categories',
            's_core_plugin_configs',
            's_core_plugin_elements',
            's_user_billingaddress',
            's_user_shippingaddress',
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
        echo "SET NAMES 'utf8' COLLATE 'utf8_unicode_ci';\n";
        echo "SET FOREIGN_KEY_CHECKS = 0;\n";
        echo "ALTER DATABASE DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;\n\n";
        $export = new Shopware_Components_DbDiff_Mysql(
            $this->config('source'),
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
                'message' => 'Backup dir "update/backup" is not writable!',
                'success' => false,
            ));
            return;
        }
        $skipTables = array(
            's_search_index',
            's_search_keywords',
            's_core_log',
            's_core_sessions'
        );
        if(($file = $this->request()->post('file')) === null) {
            $file = 'backup/database_' . date('Y-m-d_H-i-s') . '.php';
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
            'message' => 'test',
            'success' => false,
            'file' => $file
        ));
    }

    public function updateDatabaseAction()
    {
        /** @var $db PDO */
        $db = $this->config('db');
        $deltas = glob('deltas/*-*.sql');
        natsort($deltas);
        foreach($deltas as $delta) {
            $import = new Shopware_Components_DbImport_Sql($delta);
            foreach($import as $key => $query) {
                if($db->exec($query) === false) {
                    echo json_encode(array(
                        'message' => 'Query [' . $key . '] in delta "' . $delta. '" could not be executed successfully.',
                        'query' => $query,
                        'success' => false
                    ));
                    return;
                }
            }
        }
        echo json_encode(array(
            'success' => true
        ));
    }

    public static function autoload($class)
    {
        if (strpos($class, 'Shopware') !== 0) {
            return;
        }
        $file = dirname(__FILE__) . '/' . str_replace('_', DIRECTORY_SEPARATOR, substr($class, 9)) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
}
