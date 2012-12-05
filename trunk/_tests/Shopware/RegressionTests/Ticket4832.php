<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 4832
 */
class Shopware_RegressionTests_Ticket4832 extends Enlight_Components_Test_Controller_TestCase
{   	
    /**
	 * Test rfi input filter
	 */
	public function testRfiInputFilter()
    {
        //todo@hl: this don't works. Fix it please!
        $this->markTestIncomplete("Shopware_RegressionTests_Ticket4832 marked as incomplete!");
        return;

    	$this->Request()->setPost('action', '../../config.php');
    	
    	$this->dispatch('/');
    	
    	$this->assertEmpty($this->Request()->getPost('action'));
    }
}