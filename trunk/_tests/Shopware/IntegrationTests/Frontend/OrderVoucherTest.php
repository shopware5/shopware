<?php
/**
 *
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2012, shopware AG
 * @author Patrick Schücker
 * @package Shopware
 * @subpackage IntegrationTests
 */
class Shopware_IntegrationTests_Frontend_OrderVoucherTest extends Enlight_Components_Test_Selenium_TestCase
{

    public function testOrderVoucher()
    {

        //  add article "Karaffe" to basket
        $this->open("lifestyle-wohnen/6/karaffe");
        $this->waitForElementPresent("css=input[id=basketButton]");
        $this->click("css=input[id=basketButton]");
        $this->waitForElementPresent("css=div.ajax_add_article_container");
        $this->waitForElementPresent("css=a.modal_close");
        $this->click("css=a.modal_close");

        //  open basket
        $this->waitForElementPresent("css=a[href$='checkout/cart']");
        $this->click("css=a[href$='checkout/cart']");

        //  check basket
        $this->waitForElementPresent("css=img[alt=Karaffe]");

        //  add voucher
        $this->waitForElementPresent("css=input[id=basket_add_voucher]");
        $this->type("css=input[id=basket_add_voucher]", "absolut");
        $this->click("css=div.vouchers input[value=Hinzufügen]");
        $this->waitForTextPresent("Gutschein");
    	sleep(1);

        //  add second voucher - check for error
        $this->waitForElementPresent("css=input[id=basket_add_voucher]");
        $this->type("css=input[id=basket_add_voucher]", "absolut");
        $this->click("css=div.vouchers input[value=Hinzufügen]");
        $this->waitForElementPresent("css=div.error");
		$this->waitForTextPresent("Pro Bestellung kann nur ein Gutschein eingelöst werden");
		sleep(1);

        //  delete voucher
        $this->click("css=div.voucher a.del");

        //  add non exsist voucher - check for error
        $this->waitForElementPresent("css=input[id=basket_add_voucher]");
        $this->type("css=input[id=basket_add_voucher]", "__666__");
        $this->click("css=div.vouchers input[value=Hinzufügen]");
        $this->waitForElementPresent("css=div.error");
        $this->waitForTextPresent("Gutschein konnte nicht gefunden werden");
    }
}