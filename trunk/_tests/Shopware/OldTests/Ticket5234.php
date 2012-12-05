<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 5234
 */
class Shopware_RegressionTests_Ticket5234 extends Enlight_Components_Test_Plugin_TestCase
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
		$this->dispatch('/newsletter/detail?sID=1');
		if(!preg_match('#<iframe src="([^"]+)">#msi', $this->Response()->getBody(), $match)) {
			$this->fail();
		}
		$this->assertLinkExists($match[1]);
	}
}