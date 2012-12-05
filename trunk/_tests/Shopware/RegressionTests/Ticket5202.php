<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 5202
 */
class Shopware_RegressionTests_Ticket5202 extends Enlight_Components_Test_Plugin_TestCase
{    
    /**
	 *  Test case method
	 */
	public function testAddIncludePath()
    {
    	$old = Enlight_Loader::addIncludePath('.');
    	$new = Enlight_Loader::explodeIncludePath();
    	$last = array_pop($new);
    	
    	Enlight_Loader::setIncludePath($old);
    	
    	$this->assertEquals('.', $last);
    }
    
    /**
	 *  Test case method
	 */
	public function testAddIncludePath2()
    {
    	$old = Enlight_Loader::addIncludePath('.',  Enlight_Loader::POSITION_PREPEND);
    	$new = Enlight_Loader::explodeIncludePath();
    	$first = array_shift($new);
    	
    	Enlight_Loader::setIncludePath($old);
    	
    	$this->assertEquals('.', $first);
    }
    
    /**
	 *  Test case method
	 */
	public function testAddIncludePath3()
    {
    	$old = Enlight_Loader::addIncludePath('.', Enlight_Loader::POSITION_REMOVE);
    	$new = Enlight_Loader::explodeIncludePath();
    	$found = array_search('.', $new);
    	
    	Enlight_Loader::setIncludePath($old);
    	
    	$this->assertFalse($found);
    }
}