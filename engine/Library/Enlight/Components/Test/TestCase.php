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
 * Basic class for each specified test case.
 *
 * The Enlight_Components_Test_TestCase is the basic class for all specified test cases.
 * The enlight test case basic class extends PHPUnit_Framework_TestCase and sets the database link automatically.
 *
 * @category   Enlight
 * @package    Enlight_Test
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
abstract class Enlight_Components_Test_TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Extensions_Database_ITester The IDatabaseTester for this testCase
     */
    protected $databaseTester;

    /**
     * Gets the IDatabaseTester for this testCase. If the IDatabaseTester is
     * not set yet, this method calls newDatabaseTester() to obtain a new
     * instance.
     *
     * @return Enlight_Components_Test_Database_DefaultTester
     */
    protected function getDatabaseTester()
    {
        if ($this->databaseTester === null) {
            $this->databaseTester = $this->newDatabaseTester();
        }

        return $this->databaseTester;
    }

    /**
     * Creates a IDatabaseTester for this testCase.
     *
     * @return Enlight_Components_Test_Database_DefaultTester
     */
    protected function newDatabaseTester()
    {
        return new Enlight_Components_Test_Database_DefaultTester();
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     */
    protected function setUp()
    {
        parent::setUp();

        // Clear entitymanager to prevent weird 'model shop not persisted' errors.
        Shopware()->Models()->clear();

        $this->databaseTester = null;
        if (method_exists($this, 'getSetUpOperation')) {
            $this->getDatabaseTester()->setSetUpOperation($this->getSetUpOperation());
        }
        if (method_exists($this, 'getDataSet')) {
            $this->getDatabaseTester()->setDataSet($this->getDataSet());
        }
        if ($this->databaseTester !== null) {
            $this->getDatabaseTester()->onSetUp();
        }
    }

    /**
     * Performs operation returned by getSetUpOperation().
     */
    protected function tearDown()
    {
        if ($this->databaseTester !== null) {
            if (method_exists($this, 'getTearDownOperation')) {
                $this->getDatabaseTester()->setTearDownOperation($this->getTearDownOperation());
            }
            if (method_exists($this, 'getDataSet')) {
                $this->getDatabaseTester()->setDataSet($this->getDataSet());
            }
            $this->getDatabaseTester()->onTearDown();
        }

        $this->databaseTester = null;

        set_time_limit(0);
        ini_restore('memory_limit');
    }

    /**
     * Creates a new XMLDataSet with the given $xmlFile. (absolute path.)
     *
     * @param string $xmlFile
     * @return PHPUnit_Extensions_Database_DataSet_XmlDataSet
     */
    protected function createXMLDataSet($xmlFile)
    {
        return new PHPUnit_Extensions_Database_DataSet_XmlDataSet($xmlFile);
    }
}
