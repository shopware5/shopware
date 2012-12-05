<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 4595
 */
class Shopware_RegressionTests_Ticket4595 extends Enlight_Components_Test_Controller_TestCase
{    
    /**
	 * Test case method
	 */
	public function testDetailCanonicalTag()
    {
    	$this->dispatch('/detail?sArticle=16&sCategory=1118');
    	
    	$this->assertTag(array(
    		'tag' => 'link',
    		'attributes' => array(
    			'rel' => 'canonical',
    			'href' => $this->Front()->Router()->assemble(array(
    				'sArticle' => 16
    			))
    		)
    	), $this->Response()->getBody());
    }
}