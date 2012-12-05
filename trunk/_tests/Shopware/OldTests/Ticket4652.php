<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 4652
 */
class Shopware_RegressionTests_Ticket4652 extends Enlight_Components_Test_TestCase
{       
	/**
	 * Returns api import
	 *
	 * @return sShopwareImport
	 */
	public function ApiImport()
	{
		return Shopware()->Api()->Import();
	}
	
	/**
     * Tax article provider
     *
     * @return array
     */
    public function providerTaxArticle()
    {
        return array(
          array('SW2001', 7, 4),
          array('SW2001', 19, 1),
        );
    }
	
    /**
     * Test case method
     * 
     * @dataProvider providerTaxArticle
     */
	public function testChangeTax($ordernumber, $tax, $taxId)
	{
		$result = $this->ApiImport()->sArticle(array(
			'ordernumber' => $ordernumber,
			'tax' => $tax
		));
		
		$this->assertEquals($taxId, $result['taxID']);
	}
		
	/**
     * Test case method
     */
	public function testUpdateShippingAdress()
	{
		$result = $this->ApiImport()->sCustomer(array(
	      	'email' => 'hl@shopware.de',
	      	'shipping_firstname' => 'Heiner',
	      	'shipping_lastname' => 'Lohaus',
	      	'shipping_countryiso' => 'DE'
	    ));
	    
	    $this->assertNotEmpty($result['shippingaddressID']);
	}
	
	/**
     * Test case method
     */
	public function testDeleteArticle()
	{
        return; //todo@hl Fix insert article
		$result = $this->ApiImport()->sArticle(array(
			'ordernumber' => 'Test1234',
			'supplier' => 'shopware',
		));
		$this->assertNotEmpty($result);
		
		$result = $this->ApiImport()->sDeleteArticle(array(
			'ordernumber' => 'Test1234'
		));
		$this->assertTrue($result);
	}

	/**
     * Test case method
     */
	public function testUpdateCategory()
	{
		$result = $this->ApiImport()->sArticle(array(
			'description' => '  ',
		));
		$this->assertFalse($result);
	}
	
	/**
     * Test case method
     */
	public function testImportLongDescription()
	{
        return; //todo@hl Fix insert article

		$text = 'Langer Text: ';
		for($i=0; $i<10000; $i++) {
			$text .= ' '.md5(uniqid(mt_rand(), true));
		}
		$result = $this->ApiImport()->sArticle(array(
			'ordernumber' => 'Test1234',
			'description_long' => $text,
			'supplier' => 'shopware',
		));
		$this->assertNotEmpty($result);
		
		$result = $this->ApiImport()->sDeleteArticle(array(
			'ordernumber' => 'Test1234'
		));
		$this->assertTrue($result);
	}
	
	/**
     * Test case method
     */
	public function testImportArticle()
	{
        return; //todo@hl Fix insert article

		$result = $this->ApiImport()->sArticle(array(
			'ordernumber' => 'SW2001'
		));
		
		$this->assertEquals(1, $result['kind']);
	}
	
	/**
     * Test case method
     */
	public function testImportCrossSelling()
	{
		$result = $this->ApiImport()->sArticleCrossSelling(array(
			'ordernumber' => 'SW2038_4783'
		), array(
			'SW2041', 'SW2039', 'SW2040', 'SW2041'
		));
		
		$this->assertTrue($result);
	}
	
	/**
     * Test case method
     */
	public function testImportInstockActive()
	{
		$result = $this->ApiImport()->sArticle(array(
			'ordernumber' => 'SW2001',
			'active' => 0
		));
		$this->assertNotEmpty($result);
		
		$result = $this->ApiImport()->sArticleStock(array(
			'ordernumber' => 'SW2001',
			'active' => 1
		));
		$this->assertTrue($result);
		
		$sql = 'SELECT `active` FROM `s_articles_details`  WHERE `ordernumber`=?';
		$result = Shopware()->Db()->fetchOne($sql, array('SW2001'));
		$this->assertNotEmpty($result);
	}
}