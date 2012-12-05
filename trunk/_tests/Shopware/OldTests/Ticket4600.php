<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 4600
 */
class Shopware_RegressionTests_Ticket4600 extends Enlight_Components_Test_Plugin_TestCase
{    
	/**
     * Returns the test dataset
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
    	return $this->createXMLDataSet(Shopware()->TestPath('DataSets_Newsletter').'Log.xml');
    }
    
    /**
     * Test case method
     */
	public function testNewsletterLog()
	{
		$this->Front()->setParam('noViewRenderer', false);
		
		$this->Request()->setPost('username', 'demo')->setPost('password', 'demo');
		$this->dispatch('/backend/auth/login');
		$this->assertContains('"success":true', $this->Response()->getBody());
		$this->reset();
		
		$this->dispatch('/backend/newsletter/view/id/1');
		if(!preg_match('#<img src="([^"]+/logo.jpg)" />#msi', $this->Response()->getBody(), $match)) {
			$this->fail();
		}
		$this->assertLinkExists($match[1]);
	}
}