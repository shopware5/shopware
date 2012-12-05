<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 4845
 */
class Shopware_RegressionTests_Ticket4845 extends Enlight_Components_Test_Controller_TestCase
{    
    /**
	 * Test case method
	 */
	public function testLinkPlugin()
    {
        //todo@hl: this don't works. Fix it please!
        $this->markTestIncomplete("Shopware_RegressionTests_Ticket4845 marked as incomplete!");
        return;

    	$this->dispatch('/');
    	
    	$this->assertNotEmpty(Shopware()->Config()->DefaultCustomerGroup);
    }
}