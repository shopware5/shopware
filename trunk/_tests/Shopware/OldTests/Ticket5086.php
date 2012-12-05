<?php
/**
 * Test case
 * 
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 5086
 */
class Shopware_RegressionTests_Ticket5086 extends Enlight_Components_Test_Plugin_TestCase
{
    /**
     * Test case method
     */
	public function testTemplatePreview()
	{

		$this->Request()->setPost('username', 'demo')->setPost('password', 'demo');
		$this->dispatch('/backend/auth/login');
		$this->assertContains('"success":true', $this->Response()->getBody());
		$this->reset();
		
		Shopware()->Plugins()->Backend()->Modules()->enableInclude();

		$this->dispatch('/engine/backend/modules/templatespreview/skeleton.php?template=orange');
		$this->assertContains('shopware.php?sTpl=orange', $this->Response()->getBody());
		$this->reset();
	}
}