<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 4796
 */
class Shopware_RegressionTests_Ticket4796 extends Enlight_Components_Test_Controller_TestCase
{    
    /**
	 * Test case method
	 */
	public function testBotCheck()
    {
        //todo@hl: this don't works. Fix it please!
        $this->markTestIncomplete("Shopware_RegressionTests_Ticket4796 marked as incomplete!");
        return;

    	$this->Request()
    		->setHeader('USER_AGENT', 'googlebot');
    	
    	$this->dispatch('/');
    	
    	$this->assertTrue(Shopware()->Session()->Bot);
    }
    
    /**
	 * Test case method
	 */
	public function testBotCheckCase()
    {
        //todo@hl: this don't works. Fix it please!
        $this->markTestIncomplete("Shopware_RegressionTests_Ticket4709 marked as incomplete!");
        return;

    	$this->Request()
    		->setHeader('USER_AGENT', 'GoogleBot');
    	
    	$this->dispatch('/');
    	
    	$this->assertTrue(Shopware()->Session()->Bot);
    }
}