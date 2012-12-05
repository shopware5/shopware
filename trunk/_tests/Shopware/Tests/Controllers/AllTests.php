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
class Shopware_Tests_Controllers_AllTests
{
	/**
	 * Returns test suite
	 *
	 * @return PHPUnit_Framework_TestSuite
	 */
	public static function suite()
	{
		$suite = new Enlight_Components_Test_TestSuite('Shopware Controllers');

		$suite->addTest(Shopware_Tests_Controllers_Backend_AllTests::suite());
        $suite->addTest(Shopware_Tests_Controllers_Frontend_AllTests::suite());

		return $suite;
	}
}