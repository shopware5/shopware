<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 4596
 */
class Shopware_RegressionTests_Ticket4596 extends Enlight_Components_Test_Controller_TestCase
{   
	/**
	 * Set up test case
	 *
	 */
	public function setUp()
	{
		parent::setUp();
		
		$sql = "
			INSERT IGNORE INTO `s_core_rewrite_urls` (`org_path`, `path`, `main`, `subshopID`) VALUES
			('sViewport=detail&sArticle=59', 'test', 0, 1);
		";
		Shopware()->Db()->query($sql);
	}
	
    /**
	 * Test case method
	 */
	public function testRewriteUrl()
    {
    	$this->Request()->setCookie('test', true);
    	$this->dispatch('/test');
    	
    	$this->assertTrue($this->Response()->isRedirect());
    	$this->assertEquals(301, $this->Response()->getHttpResponseCode());
    }
    
    /**
	 * Test case method
	 */
	public function testRewriteUrl2()
    {
    	$this->Request()->setCookie('test', true);
    	$this->Request()->setParam('RewriteOld', true);
    	$this->dispatch('/detail?sArticle=59');
    	
    	$this->assertTrue($this->Response()->isRedirect());
    	$this->assertEquals(301, $this->Response()->getHttpResponseCode());
    }
}