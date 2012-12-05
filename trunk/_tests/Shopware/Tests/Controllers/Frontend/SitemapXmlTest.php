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
class Shopware_Tests_Controllers_Frontend_SitemapXmlTest extends Enlight_Components_Test_Controller_TestCase
{
	/**
     * Test case method
     */
	public function testIndex()
	{
		ob_start();
		$this->dispatch('/SitemapXml');
		$content = ob_get_clean();

		$this->assertEquals(200, $this->Response()->getHttpResponseCode());
	}

	/**
	 * Test case method
	 *
	 * @ticket 4559
	 */
	public function testCount()
	{
		ob_start();
		$this->dispatch('/SitemapXml');
		$content = ob_get_clean();

		$this->assertSelectCount('url', array('>=' => 40), $content);
	}
}