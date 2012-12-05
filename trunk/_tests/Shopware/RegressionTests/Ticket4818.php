<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 4818
 */
class Shopware_RegressionTests_Ticket4818 extends Enlight_Components_Test_Plugin_TestCase
{       
    /**
     * Test case method
     */
	public function testCompileId()
	{
        //todo@hl: this don't works. Fix it please!
        $this->markTestIncomplete("Shopware_RegressionTests_Ticket4818 marked as incomplete!");
        return;

		$this->dispatch('/');
		
		$this->assertEquals('templates_orange_de_DE_1', $this->View()->Engine()->getCompileId());
	}
}