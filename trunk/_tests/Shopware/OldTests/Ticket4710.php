<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 4710
 */
class Shopware_RegressionTests_Ticket4710 extends Enlight_Components_Test_Controller_TestCase
{    
    /**
	 * Test case method
	 */
	public function testLinkPlugin()
    {
    	Shopware()->Shop()->Config()->RouterRemoveCategory = true;
    	Shopware()->Shop()->Config()->DontAttachSession = true;
    	
    	$this->dispatch('/detail?sArticle=16&sCategory=1118');
    	
    	$this->assertTag(array(
    		'tag' => 'a',
    		'attributes' => array(
    			'class' => 'article_next',
    			'href' => $this->Front()->Router()->assemble(array(
    				'sArticle' => 18
    			))
    		)
    	), $this->Response()->getBody());
    }
}