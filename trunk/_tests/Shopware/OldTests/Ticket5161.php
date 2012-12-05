<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 5161
 */
class Shopware_RegressionTests_Ticket5161 extends Enlight_Components_Test_Plugin_TestCase
{    
    /**
	 * Test case method
	 */
	public function testFormatAmountCent()
    {
    	Shopware()->Loader()->loadFile(Shopware()->OldPath().'engine/connectors/payment.class.php');
    	
    	$this->assertEquals(29490, sPayment::formatAmountCent(294.90));
    }
}