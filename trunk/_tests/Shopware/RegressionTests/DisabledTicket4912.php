<?php
/**
 * Test case
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 4912
 */
class Shopware_RegressionTests_Ticket4912 extends Enlight_Components_Test_Plugin_TestCase
{
	/**
     * Returns the test dataset
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return $this->createXMLDataSet(Shopware()->TestPath('DataSets_Cms').'Static.xml');
    }

    /**
     * Test case method
     */
	public function disabledTestTemplate()
	{
		$this->Front()->setParam('noViewRenderer', false);

		$this->dispatch('/custom?sCustom=3');

		$this->assertNotNull($this->View()->sContent, 'Content');
		$this->assertNotNull($this->View()->sCustomPage, 'CustomPage');

		$this->assertEquals('Hello world !!!', $this->View()->sContent);
		$this->assertEquals($this->Response()->getHttpResponseCode(), 200);
	}
}
