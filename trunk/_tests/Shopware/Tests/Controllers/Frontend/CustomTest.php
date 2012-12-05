<?php
/**
 * Test case
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage Tests
 */
class Shopware_Tests_Controllers_Frontend_CustomTest extends Enlight_Components_Test_Controller_TestCase
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
	public function testIndex()
	{
        //TODO - Activate after Smarty-Update
        return;

		$this->dispatch('/custom?sCustom=2');

		$this->assertNotNull($this->View()->sContent, 'Content');
		$this->assertNotNull($this->View()->sCustomPage, 'CustomPage');
		$this->assertNotNull($this->View()->sBreadcrumb, 'Breadcrumb');

		$this->assertEquals(200, $this->Response()->getHttpResponseCode());
	}

	/**
     * Test case method
     */
	public function testRedirect()
	{
        //TODO - Activate after Smarty-Update
        return;

		$this->dispatch('/custom?sCustom=1');

		$this->assertEquals(301, $this->Response()->getHttpResponseCode());

		$this->assertArrayHasKey(1, $this->Response()->getHeaders());
	}

	/**
     * Test case method
     *
     * @ticket 4912
     */
	public function testTemplate()
	{
        //TODO - Activate after Smarty-Update
        return;

		$this->dispatch('/custom?sCustom=3');

		$this->assertNotNull($this->View()->sContent, 'Content');
		$this->assertNotNull($this->View()->sCustomPage, 'CustomPage');
		$this->assertNotNull($this->View()->sBreadcrumb, 'Breadcrumb');

		$this->assertEquals('Hello world !!!', $this->View()->sContent);
		$this->assertEquals($this->Response()->getHttpResponseCode(), 200);
	}

	/**
     * Test case method
     */
	public function testTemplateNotFound()
	{
        //TODO - Activate after Smarty-Update
        return;

		$this->dispatch('/custom?sCustom=2');

		$this->assertNotNull($this->View()->sContent, 'Content');
		$this->assertNotNull($this->View()->sCustomPage, 'CustomPage');
		$this->assertNotNull($this->View()->sBreadcrumb, 'Breadcrumb');

		$this->assertNull($this->View()->sContainerRight);
		$this->assertEquals($this->Response()->getHttpResponseCode(), 200);
	}

	/**
     * Test case method
     */
	public function testAjax()
	{
        //TODO - Activate after Smarty-Update
        return;

		$this->Request()->setHeader('X-REQUESTED-WITH', 'XMLHttpRequest');

		$this->dispatch('/custom?sCustom=2');

		$this->assertNotNull($this->View()->sContent, 'Content');
		$this->assertNotNull($this->View()->sCustomPage, 'CustomPage');
		$this->assertNotNull($this->View()->sBreadcrumb, 'Breadcrumb');

		$this->assertEquals(200, $this->Response()->getHttpResponseCode());
	}
}