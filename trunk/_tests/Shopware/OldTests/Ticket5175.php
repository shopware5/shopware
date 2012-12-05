<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 5175
 */
class Shopware_RegressionTests_Ticket5175 extends Enlight_Components_Test_Plugin_TestCase
{        
    /**
     * Test case method
     */
	public function testTemplate()
	{
		$this->Request()
			->setHttpHost(Shopware()->Config()->Host)
			->setBasePath(str_replace(Shopware()->Config()->Host, '', Shopware()->Config()->BasePath))
			->setBaseUrl($this->Request()->getBasePath().'/shopware.php');
		$this->dispatch('backend/index');
		
		$link = $this->Response()->getHeader('Location');
		$this->assertLinkExists($link);
	}
}