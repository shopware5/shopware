<?php
/**
 * Test case
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2011, shopware AG
 * @author Heiner Lohaus
 * @package Shopware
 * @subpackage RegressionTests
 * @ticket 4709
 */
class Shopware_RegressionTests_Ticket4709 extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * Test case method
     */
    public function testGetAffectedSuppliers()
    {
        $this->dispatch('/');
        $suppliers = Shopware()->Modules()->Articles()->sGetAffectedSuppliers(
            Shopware()->Config()->BlogCategory
        );

        $this->assertNotNull($suppliers);
    }
}
