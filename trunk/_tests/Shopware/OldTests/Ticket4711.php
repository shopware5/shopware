<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 4711
 */
class Shopware_RegressionTests_Ticket4711 extends Enlight_Components_Test_Plugin_TestCase
{    
	/**
	 * Set up test case
	 */
	public function setUp()
	{
		parent::setUp();
		
		$sql = "
			UPDATE `s_export` 
			SET `active` = '1', `hash` = '0805bbb934287228edb5374083b81416'
			WHERE `id` =1;
		";
		Shopware()->Db()->query($sql);
	}
    
    /**
     * Test case method
     */
	public function testExport()
	{
		Shopware()->Config()->HostOriginal = Shopware()->Config()->Host;
		ob_start();
		$this->dispatch('/backend/export/index/export.txt?feedID=1&hash=0805bbb934287228edb5374083b81416');
		$content = ob_get_clean();
				
		$this->assertNotEmpty($content);
	}
}