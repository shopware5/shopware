<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 5196
 */
class Shopware_RegressionTests_Ticket5196 extends Enlight_Components_Test_TestCase
{    
    /**
	 * Test case method
	 */
	public function testArrayCollectionGet()
    {
    	$collection = new Enlight_Collection_ArrayCollection(array(
    		'key_one'=>'wert1',
    		'key_two'=>'wert2',
    	));
    	$this->assertEquals('wert1', $collection->key_one);
    	$this->assertEquals('wert1', $collection->getKeyOne());
    	$this->assertEquals('wert1', $collection->get('key_one'));
    }
    
    /**
	 * Test case method
	 */
	public function testArrayCollectionSet()
    {
    	$collection = new Enlight_Collection_ArrayCollection();
    	
    	$collection->setKeyOne('wert123');
    	$this->assertEquals('wert123', $collection->getKeyOne());
    	
    	$collection->key_one = 'wert145';
    	$this->assertEquals('wert145', $collection->key_one);
    }
}