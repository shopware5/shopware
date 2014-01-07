<?php
/**
 * Enlight
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://enlight.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Enlight
 * @package    Enlight_Test
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Grants an automatically access on the database, in test cases.
 *
 * The Enlight_Components_Test_Database_DefaultTester extends the PHPUnit_Extensions_Database_AbstractTester
 * with an automatically access on the database resource.
 *
 * @category   Enlight
 * @package    Enlight_Test
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Components_Test_Database_DefaultTester extends PHPUnit_Extensions_Database_AbstractTester
{
    /**
     * Instance of the database connection class. Can be set in the class constructor.
     * If no connection is set, the default connection is used.
     *
     * @var PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    protected $connection;

    /**
     * Creates a new default database tester using the given connection.
     *
     * @param PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection
     */
    public function __construct(PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection = null)
    {
        $this->connection = $connection;

        parent::__construct();
    }

    /**
     * Returns the test database connection.
     *
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection()
    {
        if ($this->connection === null) {
            $pdo = Enlight_Application::Instance()->Db()->getConnection();
            $this->connection = $this->createDefaultDBConnection($pdo);
        }
        return $this->connection;
    }

    /**
     * Creates a new DefaultDatabaseConnection using the given PDO connection
     * and database schema name.
     *
     * @param PDO    $connection
     * @param string $schema
     * @return PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection
     */
    protected function createDefaultDBConnection(PDO $connection, $schema = '')
    {
        return new PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection($connection, $schema);
    }

    /**
     * TestCases must call this method inside setUp().
     */
    public function onSetUp()
    {
        $this->getConnection()->getConnection()->exec('SET FOREIGN_KEY_CHECKS=0;');
        parent::onSetUp();
    }

    /**
     * TestCases must call this method inside tearDown().
     */
    public function onTearDown()
    {
        parent::onTearDown();
        $this->getConnection()->getConnection()->exec('SET FOREIGN_KEY_CHECKS=1;');
    }
}
