<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 5148
 */
class Shopware_RegressionTests_Ticket5148 extends Enlight_Components_Test_Plugin_TestCase
{    
    /**
	 * Test case method
	 */
	public function testStartDispatch()
    {
    	$front = Shopware()->Front();
    	$eventArgs = $this->createEventArgs()
    	  ->set('subject', $front);
    	$plugin = Shopware()->Plugins()->Core()->Debug();
    	$plugin->Config()->AllowIP = '127.0.0.1';
    	$plugin->onStartDispatch($eventArgs);
    }
}