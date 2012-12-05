<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 5358 
 */
class Shopware_RegressionTests_Ticket5358 extends Enlight_Components_Test_TestCase
{
    /**
     * Test case method
     */
	public function testAdodb()
	{
		$sql = 'UPDATE `s_articles` SET `active`=1 WHERE `id` = 0';
		$result = Shopware()->Adodb()->Execute($sql);
		$this->assertTrue($result);
	}
	
	/**
     * Test case method
     */
	public function testAdodb2()
	{
		$sql = '
			SELECT * FROM `s_articles` WHERE `id` = 0
		';
		$result = Shopware()->Adodb()->Execute($sql);
		$this->assertInstanceOf('Enlight_Components_Adodb_Statement', $result);
	}
}