<?php
spl_autoload_register(array('Shopware_Install', 'autoload'));

class Shopware_Install extends Slim
{
    const VERSION = '1.0.0';

    public function initDb()
    {
        $config = include 'config.php';
        $config = isset($config['db']) ? $config['db'] : array();
        $config = array_merge(array('host' => '', 'port' => '', 'password' => ''), $config);
        $db = new PDO(
            "mysql:host={$config['host']};port={$config['port']};dbname=shopware_356",
            $config['username'], $config['password']
        );
        $db->exec("SET NAMES 'utf8';");
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
        $db->exec("SET NAMES 'utf8';");
        return $db;
    }

    public function __construct($userSettings = array())
    {
        parent::__construct($userSettings);
        $this->add(new Slim_Middleware_SessionCookie());


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
            echo "SET NAMES 'utf8';\n";
            echo "SET FOREIGN_KEY_CHECKS = 0;\n";
            echo "ALTER DATABASE DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;\n\n";
            $export = new Shopware_Components_DbDiff_Mysql(
                $this->config('source'),
                $this->config('db')
            );
            $tables = $export->listTables();
            foreach($tables as $table) {
                echo $export->getTableUpdate($table);
            }
        })->via('GET', 'POST')->name('backup');

        $this->get('/backup', function () use ($app) {
            $skipTables = array(
                's_articles_translations',
                's_search_index',
                's_search_keywords',
                's_core_log',
                's_core_sessions'
            );

            echo "SET NAMES 'utf8';\n";
            echo "SET FOREIGN_KEY_CHECKS = 0;\n\n";
            $export = new Shopware_Components_DbExport_Mysql(
                $this->config('db')
            );
            $tables = $export->listTables();
            foreach($tables as $table) {
                $export->setTable($table);
                foreach($export as $line) {
                    echo $line;
                    if(in_array($table, $skipTables)) {
                        break;
                    }
                }
            }
        })->via('GET', 'POST')->name('backup');

        //Shopware_Components_DbExport_Mysql
    }

    public static function autoload( $class ) {
        if ( strpos($class, 'Shopware') !== 0 ) {
            return;
        }
        $file = dirname(__FILE__) . '/' . str_replace('_', DIRECTORY_SEPARATOR, substr($class, 9)) . '.php';
        if ( file_exists($file) ) {
            require $file;
        }
    }
}
