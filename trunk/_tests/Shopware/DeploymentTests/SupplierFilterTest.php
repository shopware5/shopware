<?php
    /**
     * Test Supplier Filter in Category
     * And More Articles Of This Supplier (Detailpage)
     *
     * @link http://www.shopware.de
     * @copyright Copyright (c) 2012, shopware AG
     * @author Sebastian Kloepper
     * @package Shopware
     * @subpackage IntegrationTests
     */
class Shopware_IntegrationTests_Frontend_SupplierFilterTest extends Enlight_Components_Test_Selenium_TestCase
{
    /**
     * Supplier Filter Test
     */
    public function testSupplierFilter()
    {
        $this->open("shopware.php");
        $this->click("css=#mainNavigation a[title=\"Food + Wine\"]");
        $this->waitForElementPresent("css=.supplier");
        $this->click("css=.supplier a[title=\"Kellerei Habgut\"]");
        $this->waitForElementPresent("css=#supplierfilter");
        $this->click("css=#supplierfilter .bt_allsupplier");
        $this->waitForElementPresent("css=.supplier a[title=\"Kellerei Habgut\"]");
        $this->click("css=.supplier a[title=\"Kellerei Habgut\"]");
        $this->waitForElementPresent("css=#supplierfilter .bt_allsupplier");
        $this->click("css=.artbox.first a.title");
        $this->waitForElementPresent("css=#detailbox_middle");
        $this->click("css=.ico.link");
        $this->waitForElementPresent("css=.artbox");
        $this->click("css=.artbox");
        $this->waitForElementPresent("css=#detailbox_middle");
    }
}