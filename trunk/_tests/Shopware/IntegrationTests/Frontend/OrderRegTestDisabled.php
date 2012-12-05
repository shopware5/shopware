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
class Shopware_IntegrationTests_Frontend_OrderRegTest extends Enlight_Components_Test_Selenium_TestCase
{

    public function testOrderReg()
    {

        $emailAddress = "test".uniqid()."@shopware.de";


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

        //  open new customer form
        $this->waitForElementPresent("css=span.frontend_checkout_actions");
        $this->click("css=span.frontend_checkout_actions");
        $this->waitForElementPresent("css=form.new_customer_form input[type=submit]");
        $this->click("css=form.new_customer_form input[type=submit]");

        //  input error check
        $this->waitForElementPresent("css=input[id=registerbutton]");
        $this->click("css=input[id=registerbutton]");
        $this->waitForElementPresent("css=div.error");
        $this->waitForElementPresent("css=input[id=firstname].instyle_error");
        $this->waitForElementPresent("css=input[id=lastname].instyle_error");
        $this->waitForElementPresent("css=input[id=register_personal_email].instyle_error");
        $this->waitForElementPresent("css=input[id=register_personal_password].instyle_error");
        $this->waitForElementPresent("css=input[id=register_personal_passwordConfirmation].instyle_error");
        $this->waitForElementPresent("css=input[id=phone].instyle_error");
        $this->waitForElementPresent("css=input[id=street].instyle_error");
        $this->waitForElementPresent("css=input[id=streetnumber].instyle_error");
        $this->waitForElementPresent("css=input[id=zipcode].instyle_error");
        $this->waitForElementPresent("css=input[id=city].instyle_error");

        //  input user data
        $this->type("css=input[id=firstname].instyle_error", "Max");
	    $this->type("css=input[id=lastname].instyle_error",	"Muster");
        $this->type("css=input[id=register_personal_email].instyle_error", $emailAddress);

        //  passwordcheck
        $this->type("css=input[id=register_personal_password].instyle_error", "123456");
        $this->type("css=input[id=register_personal_passwordConfirmation].instyle_error", "123456");
        $this->click("css=input[id=registerbutton]");
        $this->waitForTextPresent("Bitte wählen Sie ein Passwort welches aus mindestens 8 Zeichen besteht.");
        sleep(1);
        $this->type("css=input[id=register_personal_password].instyle_error", "12345678");
        $this->type("css=input[id=register_personal_passwordConfirmation].instyle_error", "1234567");
        $this->click("css=input[id=registerbutton]");
        $this->waitForTextPresent("Die Passwörter stimmen nicht überein.");
        $this->type("css=input[id=register_personal_password].instyle_error", "12345678");
        $this->type("css=input[id=register_personal_passwordConfirmation].instyle_error", "12345678");

        //  adress input
        $this->type("css=input[id=phone].instyle_error", "02555123456");
        $this->type("css=input[id=street].instyle_error", "Musterstraße");
        $this->type("css=input[id=streetnumber].instyle_error", "666");
        $this->type("css=input[id=zipcode].instyle_error", "48624");
        $this->type("css=input[id=city].instyle_error", "Schöppingen");

        //  register
        $this->click("css=input[id=registerbutton]");
    	$this->waitForElementPresent("css=div.actions input[id=basketButton]");
    	$this->click("css=div.actions input[id=basketButton]");

        //  checkout
        $this->waitForElementPresent("css=div.agb_confirm");
        $this->waitForElementPresent("css=div.agb_accept input[id=sAGB]");
        $this->click("css=div.agb_accept input[id=sAGB]");
        $this->click("css=div.actions input[id=basketButton]");
        $this->waitForElementPresent("css=span.frontend_checkout_finish");

        // ordernumber --> $orderNo
        $orderNo = $this->getText('css=div.orderdetails p.bold');
        $orderNoArray = explode(':',$orderNo);
        $orderNo = trim($orderNoArray[1]);
        unset($orderNoArray);

        //  logout
        $this->waitForElementPresent("css=div.right a.button-right");
    	$this->click("css=div.my_options a.account");
        $this->waitForElementPresent("css=div.inner_container span.frontend_account_index");
        $this->waitForElementPresent("css=a.logout");
        $this->click("css=a.logout");
        $this->waitForElementPresent("css=div.logout a.button-right span.frontend_account_ajax_logout");
        $this->click("css=div.logout a.button-right span.frontend_account_ajax_logout");

        //  login
        sleep(1);
        $this->waitForElementPresent("css=div.my_options a.account");
        $this->click("css=div.my_options a.account");
        sleep(1);
        $this->waitForElementPresent("css=input[id=email]");
        $this->waitForElementPresent("css=input[id=checkout_button]");
        $this->type("css=input[id=email]", $emailAddress);
        $this->type("css=input[id=ajax_login_password]", "12345678");
        $this->click("css=input[id=checkout_button]");

        //  check order overview
        $this->waitForElementPresent("css=a[href$='account/orders']");
        $this->click("css=a[href$='account/orders']");
	    $this->waitForElementPresent("css=a.orderdetails span.frontend_account_order_item");
        $this->click("css=a.orderdetails span.frontend_account_order_item");
        $this->waitForElementPresent("css=form[action$='/checkout/add_accessories']");
        $this->waitForTextPresent($orderNo);

        //      change billingadress
        $this->click("css=li a[href$='account']");
        $this->waitForElementPresent("css=li a[href$='account/billing']");
        $this->click("css=li a[href$='account/billing']");
        $this->waitForElementPresent("css=input[id=streetnumber]");
        $this->type("css=input[id=streetnumber]", "777");
        sleep(1);
        $this->click("css=input.button-right[type=submit]");
        $this->open("account");
        $this->refresh();
        $this->waitForTextPresent("777");

    }
}