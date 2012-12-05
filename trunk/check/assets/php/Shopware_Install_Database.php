<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License and of our
 * proprietary license can be found at and
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
 * @category   Shopware
 * @package    Shopware_Components
 * @subpackage Check
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Shopware Check System
 *
 * todo@all: Documentation
 * <code>
 * $list = new Shopware_Components_Check_System();
 * $data = $list->toArray();
 * </code>
 */
class Shopware_Install_Database
{
    /**
     * @var PDO
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

    public function __construct(array $databaseParameters){
        $this->configFile = dirname(__FILE__)."/../../../config.php";
        $this->database_parameters = $databaseParameters;
    }

    public function setError($error)
    {
        $this->error = $error;
    }

    public function getError()
    {
        return $this->error;
    }

    protected $error;

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
            $this->database = new PDO("mysql:$connectionString", $user, $password);
            $this->database->exec("SET CHARACTER SET utf8");
            $this->database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $this->setError("Database-Error!: " . $e->getMessage() . "<br/>");
            return false;
        }

        try {
            $sql = "SELECT VERSION()";
            $result = $this->database->query($sql)->fetchColumn(0);
            if(version_compare($result, '5.1.0', '<')) {
                $this->setError("Database-Error!: Your database server is running MySQL $result, but Shopware 4 requires at least MySQL 5.1.0.<br/>");
                return false;
            }
        } catch(PDOException $e) { }

        try {
            $sql = "SHOW VARIABLES LIKE 'have_innodb';";
            $result = $this->database->query($sql)->fetchColumn(1);
            if($result != 'YES') {
                $this->setError("Database-Error!: The InnoDB storage engine is disabeld. Please enable for Shopware 4.<br/>");
                return false;
            }
        } catch(PDOException $e) { }

        // 5.1.0

        return true;
    }

    public function getDatabase(){
        return $this->database;
    }


}
