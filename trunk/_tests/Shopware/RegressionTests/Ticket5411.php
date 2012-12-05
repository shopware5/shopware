<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 5411
 */
class Shopware_RegressionTests_Ticket5411 extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * Test case method
     */
	public function testViewTruncate()
	{
        //todo@hl: this don't works. Fix it please!
        $this->markTestIncomplete("Shopware_RegressionTests_Ticket5411 marked as incomplete!");
        return;

		$this->dispatch('/');
		
		$result = $this->View()->fetch('eval:{"Schmuckbaum äöäüö äöäüöääü öää öä öä öä ö äööö ä&euro;e  &euro;&euro; e"|truncate:47}');
		
		$this->assertNotEquals('...', $result);
	}
}