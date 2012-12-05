<?php
/**
 * Test case
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 4611
 */
class Shopware_RegressionTests_Ticket4611 extends Enlight_Components_Test_Plugin_TestCase
{
    /**
     * Test case method
     */
	public function testListingRss()
	{
		$this->dispatch('/listing?sCategory=5&sRss=1');
		if(!preg_match('#<atom:link href="([^"]+)"#msi', $this->Response()->getBody(), $match)) {
			$this->fail();
		}
		$this->assertNotContains('sCoreId', $match[1]);
		$this->assertLinkExists($match[1]);
	}
}
