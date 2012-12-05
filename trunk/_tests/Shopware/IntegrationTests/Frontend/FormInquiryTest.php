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
class Shopware_IntegrationTests_Frontend_FromInquiryTest extends Enlight_Components_Test_Selenium_TestCase
{

    public function testFromInquiry()
    {

        $this->open("fragen-zum-artikel?sOrdernumber=SW2001_5409");

        $this->waitForElementPresent("css=input[type=submit].button-right");
        $this->click("css=input[type=submit].button-right");

        $this->waitForElementPresent("css=div.error");
        $this->waitForElementPresent("css=select[id=anrede].instyle_error");
        $this->waitForElementPresent("css=input[id=vorname].instyle_error");
        $this->waitForElementPresent("css=input[id=nachname].instyle_error");
        $this->waitForElementPresent("css=input[id=email].instyle_error");
        $this->waitForElementPresent("css=textarea[id=inquiry].instyle_error");
        $this->waitForElementPresent("css=input[name=sCaptcha].instyle_error");

        $this->select("css=select[id=anrede].instyle_error", "value=Herr");
        $this->type("css=input[id=vorname].instyle_error", "Max");
        $this->type("css=input[id=nachname].instyle_error", "Muster");
        $this->type("css=input[id=email].instyle_error", "te@shopware.de");
        $this->type("css=textarea[id=inquiry].instyle_error", "test");

        $this->click("css=input[type=submit].button-right");

        $this->waitForElementNotPresent("css=select[id=anrede].instyle_error");
        $this->waitForElementNotPresent("css=input[id=vorname].instyle_error");
        $this->waitForElementNotPresent("css=input[id=nachname].instyle_error");
        $this->waitForElementNotPresent("css=input[id=email].instyle_error");
        $this->waitForElementNotPresent("css=textarea[id=inquiry].instyle_error");


    }
}