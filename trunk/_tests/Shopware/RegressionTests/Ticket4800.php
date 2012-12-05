<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 4800
 */
class Shopware_RegressionTests_Ticket4800 extends Enlight_Components_Test_Controller_TestCase
{    
    /**
	 * Test case method
	 */
	public function testLinkPlugin()
    {
        //todo@hl: this don't works. Fix it please!
        $this->markTestIncomplete("Shopware_RegressionTests_Ticket4800 marked as incomplete!");
        return;


    	$this->dispatch('/');
    	
    	$linkExists = $this->Template()->fetch("string:{link file='frontend/_resources/styles/framework.css' fullPath}");
    	
    	$this->assertLinkExists($linkExists);
    	    	
    	$linkNotExists = $this->Template()->fetch("string:{link file='frontend/_resources/styles/not_exits.css' fullPath}");
    	
    	$this->assertLinkNotExists($linkNotExists);
    }
}