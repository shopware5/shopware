<?php
/**
 * Test case for Listing Controller
 * Specially Feeds
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Temporary
 * @package Shopware
 * @subpackage Tests
 * @ticket 4624
 */
class Shopware_Tests_Controllers_Frontend_ListingTest extends Enlight_Components_Test_Controller_TestCase
{
	/**
	 * Test Atom - Feeds
	 * Call Category with Parameter sAtom=1 and parse Results
	 * Parse result through Zend_Feed and count feed items
	 *
	 * @ticket 4624
	 */
	public function testAtom()
	{
        //TODO - Activate after DOMDocument-Update
        return;

		$this->Front()->setParam('noViewRenderer', false);
		$this->dispatch('/Listing/index/?sCategory=1161&sAtom=1');
		$body = $this->Response()->getBody();
		$feed = new Zend_Feed_Atom(null,$body);
		$this->assertGreaterThan(1,$feed->count());
	}

	/**
	 * Test Rss-Feeds
	 * Call Category with Parameter sRss=1 and parse Results
	 * Parse result through Zend_Feed and count feed items
	 *
	 * @ticket 4624
	 */
	public function testRss()
	{
        //TODO - Activate after DOMDocument-Update
        return;

		$this->Front()->setParam('noViewRenderer', false);
		$this->dispatch('/Listing/index/?sCategory=1161&sRss=1');
		$body = $this->Response()->getBody();
		$feed = new Zend_Feed_Rss(null,$body);
		$this->assertGreaterThan(1, $feed->count());
	}
}