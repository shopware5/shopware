<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 4668
 */
class Shopware_RegressionTests_Ticket4668 extends Enlight_Components_Test_Plugin_TestCase
{    
    /**
	 * Test enlight loader check file
	 */
	public function testEnlightLoaderCheckFile()
    {
    	$this->assertTrue(Enlight_Loader::checkFile('H:\Apache Group\Apache\htdocs\shopware.php'));
    	$this->assertFalse(Enlight_Loader::checkFile('H:\Apache Group\Apache\htdocs\shopware.php'."\0"));
    }
}