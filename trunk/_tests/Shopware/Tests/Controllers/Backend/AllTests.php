<?php
/**
 * Test suite
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Tests
 */
class Shopware_Tests_Controllers_Backend_AllTests
{
	/**
	 * Returns test suite
	 *
	 * @return PHPUnit_Framework_TestSuite
	 */
	public static function suite()
	{
		$suite = new Enlight_Components_Test_TestSuite('Shopware Controllers Backend');

        $suite->addTestFiles(glob(dirname(__FILE__) . '/*Test.php'));

		return $suite;
	}
}