<?php
/**
 *  Copyright 2010 KLARNA AB. All rights reserved.
 *
 *  Redistribution and use in source and binary forms, with or without modification, are
 *  permitted provided that the following conditions are met:
 *
 *     1. Redistributions of source code must retain the above copyright notice, this list of
 *        conditions and the following disclaimer.
 *
 *     2. Redistributions in binary form must reproduce the above copyright notice, this list
 *        of conditions and the following disclaimer in the documentation and/or other materials
 *        provided with the distribution.
 *
 *  THIS SOFTWARE IS PROVIDED BY KLARNA AB "AS IS" AND ANY EXPRESS OR IMPLIED
 *  WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND
 *  FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL KLARNA AB OR
 *  CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 *  CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 *  SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 *  ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 *  NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
 *  ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 *  The views and conclusions contained in the software and documentation are those of the
 *  authors and should not be interpreted as representing official policies, either expressed
 *  or implied, of KLARNA AB.
 *
 * @package KlarnaAPI
 */

/**
 * Include the {@link PCStorage} interface.
 */
require_once('storage.intf.php');

/**
 * SQL storage class for KlarnaPClass
 *
 * This class is an PDO implementation of the PCStorage interface.<br>
 *
 * Config field pcURI needs to match:<br>
 * user:passwd@addr:port/dbName.dbTable (assumes MySQL)<br>
 * Port can be omitted.<br>
 *
 * The {@link http://www.php.net/manual/en/ref.pdo-mysql.connection.php PDO DSN} format is also available:<br>
 * user:passwd@pdo:dsn/dbName.dbTable<br>
 * Where dsn can be: mysql:host=localhost;dbname=testdb
 *
 * <b>Acceptable characters</b>:<br>
 * Username: [A-Za-z0-9_]<br>
 * Password: [A-Za-z0-9_]<br>
 * Address:  [A-Za-z0-9_.]<br>
 * Port:     [0-9]<br>
 * DB name:  [A-Za-z0-9_]<br>
 * DB table: [A-Za-z0-9_]<br>
 *
 * To allow for more special characters, and to avoid having<br>
 * a regular expression that is too hard to understand, you can<br>
 * use an associative array:<br>
 * <code>
 * array(
 *   "user" => "myuser",
 *   "passwd" => "mypass",
 *   "dsn" => "localhost",
 *   "db" => "mydatabase",
 *   "table" => "mytable"
 * );
 * </code>
 *
 * @package   KlarnaAPI
 * @version   2.1.2
 * @since     2011-09-13
 * @link      http://integration.klarna.com/
 * @copyright Copyright (c) 2010 Klarna AB (http://klarna.com)
 */
class SQLStorage extends PCStorage {

    /**
     * Database name.
     *
     * @ignore Do not show in PHPDoc.
     * @var string
     */
    protected $dbName;

    /**
     * Database table.
     *
     * @ignore Do not show in PHPDoc.
     * @var string
     */
    protected $dbTable;

    /**
     * Database address.
     *
     * @ignore Do not show in PHPDoc.
     * @var string
     */
    protected $addr;

    /**
     * PDO DSN notation.
     *
     * @ignore Do not show in PHPDoc.
     * @var string
     */
    protected $dsn;

    /**
     * Database username.
     *
     * @ignore Do not show in PHPDoc.
     * @var string
     */
    protected $user;

    /**
     * Database password.
     *
     * @ignore Do not show in PHPDoc.
     * @var string
     */
    protected $passwd;

    /**
     * PDO DB link resource.
     *
     * @ignore Do not show in PHPDoc.
     * @var PDO
     */
    protected $pdo;

    /**
     * Class constructor
     * @ignore Does nothing.
     */
    public function __construct() {
    }

    /**
     * Class destructor
     * @ignore Does nothing.
     */
    public function __destruct() {
    }


    /**
     * Splits the URI for the following formats:<br>
     * user:passwd@addr/dbName.dbTable (assumes MySQL)<br>
     * user:password@pdo:dsn/dbName.dbTable<br>
     *
     * To allow for more special characters, and to avoid having<br>
     * a regular expression that is too hard to understand, you can<br>
     * use an associative array:<br>
     * <code>
     * array(
     *   "user" => "myuser",
     *   "passwd" => "mypass",
     *   "dsn" => "localhost",
     *   "db" => "mydatabase",
     *   "table" => "mytable"
     * );
     * </code>
     *
     * @ignore Do not show in PHPDoc.
     * @param  string|array $uri Specified URI to database and table.
     * @throws Exception
     * @return void
     */
    protected function splitURI($uri) {
        /* If you want to have some characters that would make the
            regexp too complex, you can use an array as input instead. */
        if(is_array($uri)) {
            $this->user = $uri['user'];
            $this->passwd = $uri['passwd'];
            $this->dsn = $uri['dsn'];
            $this->dbName = $uri['db'];
            $this->dbTable = $uri['table'];

            return array(
                $uri,
                $this->user,
                $this->passwd,
                $this->dsn,
                $this->dbName,
                $this->dbTable
            );
        }

        $arr = null;
        if(preg_match('/^([\w-]+):([\w-]+)@pdo:([\w.,:;\/ \\\t=\(\){}\*-]+)\/([\w-]+).([\w-]+)$/', $uri, $arr) === 1) {
            /*
             * [0] => user:password@pdo:dsn/dbName.dbTable
             * [1] => user
             * [2] => passwd
             * [3] => dsn
             * [4] => dbName
             * [5] => dbTable
             */
            if(count($arr) != 6) {
                throw new Exception('URI is invalid! Missing field or invalid characters used!');
            }

            $this->user = $arr[1];
            $this->passwd = $arr[2];
            $this->dsn = $arr[3];
            $this->dbName = $arr[4];
            $this->dbTable = $arr[5];
        } else if(preg_match('/^([\w-]+):([\w-]+)@([\w\.-]+|[\w\.-]+:[\d]+|[\w\.-]+:[\w\.\/-]+|:[\w\.\/-]+)\/([\w-]+).([\w-]+)$/', $uri, $arr) === 1) {
            //user:pass@127.0.0.1:3306/dbName.dbTable
            //user:pass@localhost:/tmp/mysql.sock/dbName.dbTable
            /*
             * [0] => user:passwd@addr/dbName.dbTable
             * [1] => user
             * [2] => passwd
             * [3] => addr
             * [4] => dbName
             * [5] => dbTable
             */
            if(count($arr) != 6) {
                throw new Exception('URI is invalid! Missing field or invalid characters used!');
            }

            $this->user = $arr[1];
            $this->passwd = $arr[2];
            $this->addr = $arr[3];
            $this->port = 3306;
            if(preg_match('/^([0-9.]+(:([0-9]+))?)$/', $this->addr, $tmp) === 1) {
                if(isset($tmp[3])) {
                    $this->port = $tmp[3];
                }
            }
            $this->dbName = $arr[4];
            $this->dbTable = $arr[5];
            $this->dsn = "mysql:host={$this->addr};port={$this->port};"; //dbname={$this->dbName}";
        } else {
            throw new Exception('URI to SQL is not valid! ( user:passwd@addr/dbName.dbTable )');
        }

        return $arr;
    }

    /**
     * Grabs the PDO connection to the database, specified by the URI.
     *
     * @ignore Do not show in PHPDoc.
     * @param  string  $uri
     * @return void
     * @throws Exception
     */
    protected function getConnection($uri) {
        if($this->pdo) {
            return; //Already have a connection
        }

        $this->splitURI($uri);

        try {
            $this->pdo = new PDO($this->dsn, $this->user, $this->passwd);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception('Failed to connect to database!');
        }
    }

    /**
     * Initializes the DB, if the database or table is missing.
     *
     * @ignore Do not show in PHPDoc.
     * @return void
     * @throws Exception
     */
    protected function initDB() {
        try {
            $this->pdo->exec("CREATE DATABASE `{$this->dbName}`");
        } catch (PDOException $e) {
            //SQLite does not support this...
            //throw new Exception('Database non-existant, failed to create it!');
        }

        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS `{$this->dbName}`.`{$this->dbTable}` (
                `eid` int(10) NOT NULL,
                `id` int(10) NOT NULL,
                `type` int(4) NOT NULL,
                `description` varchar(255) NOT NULL,
                `months` int(11) NOT NULL,
                `interestrate` decimal(11,2) NOT NULL,
                `invoicefee` decimal(11,2) NOT NULL,
                `startfee` decimal(11,2) NOT NULL,
                `minamount` decimal(11,2) NOT NULL,
                `country` int(11) NOT NULL,
                `expire` int(11) NOT NULL
            );
SQL;
        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            throw new Exception('Table non-existant, failed to create it!');
        }
    }

    /**
     * Connects to the DB and checks if DB and table exists.
     *
     * @ignore Do not show in PHPDoc.
     * @throws Exception
     * @return void
     */
    protected function connect($uri) {
        $this->getConnection($uri);

        $this->initDB();
    }

    /**
     * @see PCStorage::load()
     */
    public function load($uri) {
        try {
            $this->connect($uri);

            $this->loadPClasses();
        }
        catch(Exception $e) {
            throw new KlarnaException("Error in " . __METHOD__ . ": " .$e->getMessage());
        }
    }

    /**
     * Loads the PClasses.
	 *
	 * @ignore Do not show in PHPDoc.
	 * @return void
     * @throws Exception
     */
    protected function loadPClasses() {
        try {
            $sth = $this->pdo->prepare(
                "SELECT * FROM `{$this->dbName}`.`{$this->dbTable}`",
                array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY)
            );
            $sth->execute();

            while ($row = $sth->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT)) {
                $this->addPClass(new KlarnaPClass($row));
            }

            $sth->closeCursor();
            $sth = null;
        } catch (PDOException $e) {
            throw new Exception('Could not fetch PClasses from database!');
        }
    }

    /**
     * @see PCStorage::save()
     */
    public function save($uri) {
        try {
            $this->connect($uri);

            //Only attempt to savePClasses if there are any.
            if(is_array($this->pclasses) && count($this->pclasses) > 0) {
                $this->savePClasses();
            }
        } catch(Exception $e) {
            throw new KlarnaException("Error in " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * Saves the PClasses.
     *
     * @ignore Do not show in PHPDoc.
     * @return void
     * @throws Exception
     */
    protected function savePClasses() {
        //Insert PClass SQL statement.
        $sql = <<<SQL
            INSERT INTO `{$this->dbName}`.`{$this->dbTable}`
                (`eid`, `id`, `type`, `description`, `months`, `interestrate`,
       	         `invoicefee`, `startfee`, `minamount`, `country`, `expire`)
           	VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
SQL;

        foreach($this->pclasses as $pclasses) {
            foreach($pclasses as $pclass) {
                try {
                    //Remove the pclass if it exists.
                    $sth = $this->pdo->prepare("DELETE FROM `{$this->dbName}`.`{$this->dbTable}` WHERE `id` = ? AND `eid` = ?");
                    $sth->execute(array(
                        $pclass->getId(), $pclass->getEid())
                    );

                    $sth->closeCursor();
                    $sth = null;
                } catch(PDOException $e) {
                    //Fail silently, we don't care if the removal failed.
                }

                try {
                    //Attempt to insert the PClass into the DB.
                    $sth = $this->pdo->prepare($sql);
                    $sth->execute(array(
                        $pclass->getEid(), $pclass->getId(), $pclass->getType(), $pclass->getDescription(),
                        $pclass->getMonths(), $pclass->getInterestRate(), $pclass->getInvoiceFee(),
                        $pclass->getStartFee(), $pclass->getMinAmount(), $pclass->getCountry(), $pclass->getExpire()
                    ));

                    $sth->closeCursor();
                    $sth = null;
                } catch(PDOException $e) {
                    throw new Exception('Failed to insert PClass into database!');
                }
            }
        }
    }

    /**
     * @see PCStorage::clear()
     */
    public function clear($uri) {
        try {
            $this->connect($uri);
            unset($this->pclasses);
            $this->clearTable();
        }
        catch(Exception $e) {
            throw new KlarnaException("Error in " . __METHOD__ . ": " . $e->getMessage());
        }
    }

    /**
     * Drops the database table, to clear the PClasses.
     *
     * @ignore Do not show in PHPDoc.
     * @return void
     * @throws Exception
     */
    protected function clearTable() {
        try {
            $this->pdo->exec("DELETE FROM `{$this->dbName}`.`{$this->dbTable}`");
        } catch (PDOException $e) {
            throw new Exception('Could not clear the database!');
        }
    }
}
