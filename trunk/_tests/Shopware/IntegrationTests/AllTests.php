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
class Shopware_IntegrationTests_AllTests
{
	/**
	 * Adds to a suite for shopware integration.
	 * 
	 * @return PHPUnit_Framework_TestSuite
	 */
	public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Shopware Integrations');

		$suite->addTest(Shopware_IntegrationTests_Frontend_AllTests::suite());
		$suite->addTest(Shopware_IntegrationTests_Backend_AllTests::suite());

        return $suite;
    }
}