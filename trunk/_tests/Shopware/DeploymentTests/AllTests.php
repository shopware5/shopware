<?php
/**
 * Selenium Test Case
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Components_Test
 */
class Shopware_DeploymentTests_AllTests
{
    /**
     * Returns test suite
     *
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $suite = new Enlight_Components_Test_TestSuite('Shopware Integration Frontend');

        $suite->addTestFiles(glob(dirname(__FILE__) . '/*Test.php'));

        return $suite;
    }
}