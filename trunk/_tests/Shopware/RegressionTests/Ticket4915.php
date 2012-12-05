<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 4915
 */
class Shopware_RegressionTests_Ticket4915 extends Enlight_Components_Test_Plugin_TestCase
{    
	/**
     * Returns the test dataset
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
    	return $this->createXMLDataSet(Shopware()->TestPath('DataSets_Newsletter').'Lock.xml');
    }
    
    /**
     * Test case method
     */
	public function testNewsletterLock()
	{
		$this->Front()->setParam('noViewRenderer', false);
		Shopware()->Config()->MailCampaignsPerCall = 1;
		
		$this->dispatch('/backend/newsletter/cron');
		$this->assertRegExp('#[0-9]+ Recipients fetched#', $this->Response()->getBody());
		$this->reset();
		
		$this->dispatch('/backend/newsletter/cron');
		$this->assertRegExp('#Wait [0-9]+ seconds ...#', $this->Response()->getBody());
		$this->reset();
	}
}