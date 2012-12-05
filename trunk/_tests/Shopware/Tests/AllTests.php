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
class Shopware_Tests_AllTests
{
	/**
	 * Returns test suite
	 *
	 * @return PHPUnit_Framework_TestSuite
	 */
	public static function suite()
	{
		$suite = new Enlight_Components_Test_TestSuite('Shopware Test');

		$suite->addTest(Shopware_Tests_Components_AllTests::suite());
		$suite->addTest(Shopware_Tests_Controllers_AllTests::suite());
		$suite->addTest(Shopware_Tests_Models_AllTests::suite());
		$suite->addTest(Shopware_Tests_Modules_AllTests::suite());
		$suite->addTest(Shopware_Tests_Plugins_AllTests::suite());

		return $suite;
	}
}