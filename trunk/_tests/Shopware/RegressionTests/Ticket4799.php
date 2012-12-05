<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 4799
 * @group sth
 */
class Shopware_RegressionTests_Ticket4799 extends Enlight_Components_Test_Plugin_TestCase
{    
	/**
     * Returns the test dataset
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
    	return $this->createXMLDataSet(Shopware()->TestPath('DataSets_Checkout').'Finish.xml');
    }
    
    /**
     * Test case method
     */
	public function testCheckoutFinishLog()
	{
		$this->Request()
			->setMethod('POST')
			->setPost('email', 'hl@shopware.de')
			->setPost('password', 'shopware');
		$this->dispatch('/account/login');	
		$this->assertTrue($this->Response()->isRedirect());
		$this->reset();
		$a = include(Shopware()->TestPath('DataSets_Checkout').'Variables.php');
        Shopware()->Session()->sOrderVariables = $a;
		Shopware()->Session()->sUserId = $a["sUserData"]["billingaddress"]["userID"];
        $this->dispatch('/checkout/finish?sUniqueID=bf5505a1180a9e3c39fbf396cb7c53cb');
		
		$this->assertContains('20001', $this->Response()->getBody());
		$this->assertContains('84d03011369c135f0ba48cdb37c63c12', $this->Response()->getBody());
	}
}