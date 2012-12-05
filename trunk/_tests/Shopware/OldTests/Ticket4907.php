<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 4907
 */
class Shopware_RegressionTests_Ticket4907 extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * Test case method
     */
	public function testBasket()
	{
		$this->dispatch('/');
		
		Shopware()->System()->sUSERGROUPDATA['tax'] = 0;
		$insertId = Shopware()->Modules()->Basket()->sAddArticle('SW2001_6969');
		$basket = Shopware()->Modules()->Basket()->sGetBasket();
		Shopware()->Modules()->Basket()->sDeleteArticle($insertId);
		
		$this->assertEquals($basket['AmountWithTaxNumeric'], str_replace(',', '.', $basket['AmountWithTax']));
		$this->assertGreaterThan($basket['AmountNumeric'], $basket['AmountWithTaxNumeric']);
	}
}