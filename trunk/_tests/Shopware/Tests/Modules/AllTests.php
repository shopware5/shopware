<?php
/**
 * Test suite
 */
class Shopware_Tests_Modules_AllTests
{
	/**
	 * Returns test suite
	 *
	 * @return PHPUnit_Framework_TestSuite
	 */
	public static function suite()
	{
		$suite = new Enlight_Components_Test_TestSuite('Shopware Modules');

		$suite->addTest(Shopware_Tests_Modules_Articles_AllTests::suite());

		return $suite;
	}
}