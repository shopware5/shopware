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
class Shopware_Tests_Controllers_Frontend_IndexTest extends Enlight_Components_Test_Controller_TestCase
{
	/**
     * Test case method
     */
	public function testIndex()
	{
		$this->dispatch('/');

		$this->assertNotNull($this->View()->sCharts, 'Charts');
		$this->assertNotNull($this->View()->sCategoryContent, 'CategoryContent');

		$this->assertEquals($this->Response()->getHttpResponseCode(), 200);
	}

	/**
     * Test case method
     */
	public function testIndex2()
	{
		$this->dispatch('/');

		$this->assertNotNull($this->View()->sCharts, 'Charts');
		$this->assertNotNull($this->View()->sCategoryContent, 'CategoryContent');

		$this->assertEquals($this->Response()->getHttpResponseCode(), 200);
	}

	/**
     * Test case method
     */
	public function testNotFound()
	{
		$this->Front()->setParam('useDefaultControllerAlways', true);

		$this->dispatch('/not_found');

		$this->assertNotNull($this->View()->sCharts, 'Charts');
		$this->assertNotNull($this->View()->sCategoryContent, 'CategoryContent');

		$this->assertEquals($this->Request()->getControllerName(), 'index');
		$this->assertEquals($this->Request()->getActionName(), 'index');

		$this->assertEquals($this->Response()->getHttpResponseCode(), 404);
	}

	/**
     * Test case method
     */
	public function testNotFound2()
	{
		$e = null;
		try {
			$this->dispatch('/not_found');
		} catch (Exception $e) { }

		$this->assertInstanceOf('Enlight_Controller_Exception', $e);
	}

	/**
     * Test case method
     */
	public function testNotFound3()
	{
		$this->dispatch('/index/not_found');

		$this->assertNotNull($this->View()->sCharts, 'Charts');
		$this->assertNotNull($this->View()->sCategoryContent, 'CategoryContent');

		$this->assertEquals($this->Request()->getControllerName(), 'index');
		$this->assertEquals($this->Request()->getActionName(), 'index');

		$this->assertEquals($this->Response()->getHttpResponseCode(), 404);
	}
}