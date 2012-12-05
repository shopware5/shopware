<?php
/**
 * Testing different tax configurations for vouchers
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author st.hamann
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 4708
 */
class Shopware_RegressionTests_Ticket4708 extends Enlight_Components_Test_Controller_TestCase
{
	/**
	 * Test basket basics
	 */
	public function testBasketBasics()
	{
		return;
		$this->dispatch('/');
		Shopware()->Modules()->Basket()->sAddArticle('tax19');
		$basket = Shopware()->Modules()->Basket()->sGetBasket();
		Shopware()->Modules()->Basket()->sDeleteBasket();
		$this->assertEquals($basket["content"][0]["price"],"10,00");
		$this->assertEquals($basket["content"][0]["netprice"],"8.4033613445");
	}

	/**
	 * Test basket basics net
	 */
	public function testBasketBasicsNet()
	{
		return;
		$this->dispatch('/');
		Shopware()->System()->sUSERGROUPDATA['tax'] = 0;
		Shopware()->Modules()->Basket()->sAddArticle('tax19');
		$basket = Shopware()->Modules()->Basket()->sGetBasket();
		Shopware()->Modules()->Basket()->sDeleteBasket();
		$this->assertEquals($basket["content"][0]["price"],"8,40");
		$this->assertEquals($basket["content"][0]["netprice"],"8.403");
	}
	
    /**
     * Test the old tax behaviour (global config)
	 * Needs a voucher with "testOld" as code
     */
	public function testVoucherOldTaxBehaviour()
	{
		return;
		$this->dispatch('/');

		$insertId = Shopware()->Modules()->Basket()->sAddArticle('tax19');

		Shopware()->Modules()->Basket()->sAddVoucher('testOld');
		
		$basket = Shopware()->Modules()->Basket()->sGetBasket();
		$voucher = $this->getVoucher($basket["content"]);
		$this->assertEquals(round(str_replace(",",".",$voucher["price"]),2),round($voucher["netprice"]/100*(100 + Shopware()->Config()->VoucherTax),2));
		Shopware()->Modules()->Basket()->sDeleteBasket();
	}

	 /**
     * Test the new dynamic tax behaviour for vouchers
	 * Needs a voucher with "testAuto" as code
     */
	public function testVoucherAutoTax()
	{
		return;
		$this->dispatch('/');

		$insertId = Shopware()->Modules()->Basket()->sAddArticle('tax19');
		$insertId = Shopware()->Modules()->Basket()->sAddArticle('tax7');

		Shopware()->Modules()->Basket()->sAddVoucher('testAuto');

		$basket = Shopware()->Modules()->Basket()->sGetBasket();
		$voucher = $this->getVoucher($basket["content"]);
		// Should use 19 %
		$this->assertEquals(round(str_replace(",",".",$voucher["price"]),2),round($voucher["netprice"]/100*(100 + 19),2));
		Shopware()->Modules()->Basket()->sDeleteBasket();

		$insertId = Shopware()->Modules()->Basket()->sAddArticle('tax7');

		Shopware()->Modules()->Basket()->sAddVoucher('testAuto');

		$basket = Shopware()->Modules()->Basket()->sGetBasket();
		$voucher = $this->getVoucher($basket["content"]);
		// Should use 7 %
		$this->assertEquals(round(str_replace(",",".",$voucher["price"]),2),round($voucher["netprice"]/100*(100 + 7),2));
		Shopware()->Modules()->Basket()->sDeleteBasket();
	}

	/**
     * Test no tax
	 * Needs a voucher with code testFree
     */
	public function testVoucherFreeTax()
	{
		return;
		$this->dispatch('/');
		Shopware()->Modules()->Basket()->sDeleteBasket();
		$insertId = Shopware()->Modules()->Basket()->sAddArticle('tax19');

		Shopware()->Modules()->Basket()->sAddVoucher('testFree');

		$basket = Shopware()->Modules()->Basket()->sGetBasket();

		$voucher = $this->getVoucher($basket["content"]);
	    $this->assertNotEquals($voucher, array());
		$this->assertEquals(round(str_replace(",",".",$voucher["price"]),2),round($voucher["netprice"],2));
		Shopware()->Modules()->Basket()->sDeleteBasket();
	}

	/**
     * Test fix tax rate high
	 * Needs a voucher with "testFix19" as code
     */
	public function testVoucherHighTax()
	{
		return;
		$this->dispatch('/');

		$insertId = Shopware()->Modules()->Basket()->sAddArticle('tax19');

		Shopware()->Modules()->Basket()->sAddVoucher('testFix19');

		$basket = Shopware()->Modules()->Basket()->sGetBasket();
		$voucher = $this->getVoucher($basket["content"]);
		$this->assertNotEquals($voucher, array());
		$this->assertEquals(round(str_replace(",",".",$voucher["price"]),2),round($voucher["netprice"]/100*(100 + 19),2));
		Shopware()->Modules()->Basket()->sDeleteBasket();
	}

	/**
     * Test fix tax rate low
	 * Needs a voucher with "testLow9" as code
     */
	public function testVoucherLowTax()
	{
		return;
		$this->dispatch('/');

		$insertId = Shopware()->Modules()->Basket()->sAddArticle('tax19');

		Shopware()->Modules()->Basket()->sAddVoucher('testFix7');

		$basket = Shopware()->Modules()->Basket()->sGetBasket();
		$voucher = $this->getVoucher($basket["content"]);
		$this->assertNotEquals($voucher, array());
		$this->assertEquals(round(str_replace(",",".",$voucher["price"]),2),round($voucher["netprice"]/100*(100 + 7),2));
		Shopware()->Modules()->Basket()->sDeleteBasket();
	}

	/**
	 * Get basket positions with modus = 2 (vouchers) from list
	 * @param array $content
	 * @return
	 */
	protected function getVoucher(array $content){
		foreach ($content as $row){
			if ($row["modus"] == 2) return $row;
		}
	}
}