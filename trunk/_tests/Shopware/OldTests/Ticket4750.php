<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 4750
 */
class Shopware_RegressionTests_Ticket4750 extends Enlight_Components_Test_Controller_TestCase
{    
    /**
	 * Test case method
	 */
	public function testCacheTemplate()
    {
    	$url = 'http://'.
    		Shopware()->Config()->BasePath.
    		'/engine/backend/php/sCacheTemplate.php'.
    		'?file=/templates/_default/frontend/_resources/images/logo.jpg';
    	$content = file_get_contents($url);
    	$this->assertNotEmpty($content);
    }
}