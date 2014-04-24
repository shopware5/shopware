<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

namespace Shopware\Recovery\Install;

/**
 * @category  Shopware
 * @package   Shopware\Recovery\Update
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Database
{
    /**
     * @var \PDO
     */
    protected $database;

    /**
     * @var array
     */
    protected $database_parameters = array();

    /**
     * @var string
     */
    protected $configFile;

    /**
     * @var string
     */
    protected $error;

    /**
     * @param array $databaseParameters
     * @param string $basePath
     */
    public function __construct(array $databaseParameters, $basePath)
    {
        $this->configFile = rtrim($basePath, '/') . '/config.php';
        $this->database_parameters = $databaseParameters;
    }

    /**
     * @param $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return bool
     */
    public function setDatabase()
    {
        $host     = $this->database_parameters["host"];
        $database = $this->database_parameters["database"];
        $user     = $this->database_parameters["user"];
        $password = trim($this->database_parameters["password"]);
        $port     = trim($this->database_parameters["port"]);
        $socket   = trim($this->database_parameters["socket"]);

        $connectionSettings = array(
            "host=$host",
            "dbname=$database",
        );

        if (!empty($socket)) {
            $connectionSettings[] = "unix_socket=$socket";
        }

        if (!empty($port)) {
            $connectionSettings[] = "port=$port";
        }

        $connectionString = implode(';', $connectionSettings);
        try {
            $this->database = new \PDO("mysql:$connectionString", $user, $password, array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"));
            $this->database->exec("SET CHARACTER SET utf8");
            $this->database->exec("SET @@session.sql_mode = ''");
            $this->database->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            $this->setError("Database-Error!: " . $e->getMessage() . "<br/>");

            return false;
        }

        try {
            $sql = "SELECT VERSION()";
            $result = $this->database->query($sql)->fetchColumn(0);
            if (version_compare($result, '5.1.0', '<')) {
                $this->setError("Database-Error!: Your database server is running MySQL $result, but Shopware 4 requires at least MySQL 5.1.0.<br/>");

                return false;
            }
        } catch (\PDOException $e) { }

        try {
            $hasEngineSupport = $this->hasStorageEngine('InnoDB');
            if (!$hasEngineSupport) {
                $this->setError("Database-Error!: The MySQL storage engine InnoDB not found. Please consult your hosting provider to solve this problem.<br/>");

                return false;
            }
        } catch (\PDOException $e) { }

        try {
            $sql = "SELECT @@SESSION.sql_mode;";
            $result = $this->database->query($sql)->fetchColumn(0);

            if (strpos($result, 'STRICT_TRANS_TABLES') !== false || strpos($result, 'STRICT_ALL_TABLES') !== false) {
                $this->setError("Database-Error!: The MySQL strict mode is active. Please consult your hosting provider to solve this problem.<br/>");

                return false;
            }
        } catch (\PDOException $e) { }

        return true;
    }

    /**
     * @return \PDO
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @return bool
     */
    public function writeConfig()
    {
        $databaseConfigFile = $this->configFile;

        if (!file_exists($databaseConfigFile) || !is_writable($databaseConfigFile)) {
            $this->setError("Shopware config file $databaseConfigFile not found or not writeable");

            return false;
        }

        $config = array(
            'db' => array()
        );

        $mapping = array(
            'user'     => 'username',
            'database' => 'dbname',
            'socket'   => 'unix_socket',
        );

        foreach ($this->database_parameters as $key => $parameter) {
            if ($key == "port" && empty($parameter)) continue;
            if ($key == "socket" && empty($parameter)) continue;

            if (isset($mapping[$key])) {
                $key = $mapping[$key];
            }

            $config['db'][$key] = trim($parameter);
        }
        try {
            $template = '<?php return ' . var_export($config, true) . ';';
            if (!file_put_contents($databaseConfigFile, $template)) {
                $this->setError("Could not write config");

                return false;
            }
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function importDumpEn()
    {
        $dump = file_get_contents(__DIR__ . '/../data/sql/en.sql');
        $dump = explode("\r\n",$dump);

        foreach ($dump as $line) {
            if (empty($line)) continue;
            try {
                $this->getDatabase()->query($line);
            } catch (\PDOException $e) {
                $this->setError("Database-Error!: " . $e->getMessage() . "<br/>");

                return false;
            }
        }

        return true;
    }

    /**
     * Is given MySQL storage engine available?
     *
     * @param  string $engineName
     * @return bool
     */
    public function hasStorageEngine($engineName)
    {
        $sql = 'SHOW ENGINES;';
        $allEngines = $this->database->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($allEngines as $engine) {
            if ($engine['Engine'] == $engineName) {
                $support = $engine['Support'];

                return $support == 'DEFAULT' || $support == 'YES';
            }
        }

        return false;
    }
}
