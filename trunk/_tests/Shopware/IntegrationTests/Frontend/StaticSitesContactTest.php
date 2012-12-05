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
class Shopware_IntegrationTests_Frontend_StaticSitesContactTest extends Enlight_Components_Test_Selenium_TestCase
{

    public function testStaticSitesContact()
    {
        //  open start page
        $this->open("shopware.php");
//        sleep(2);
        $this->waitForElementPresent("css=ul[id=servicenav]");
        $this->waitForElementPresent("css=div.footer_menu");

        //  check "contactform"
        $this->waitForElementPresent("css=a[href$='shopware.php?sViewport=ticket&sFid=5']");
        $this->click("css=a[href$='shopware.php?sViewport=ticket&sFid=5']");
        $this->waitForElementPresent("css=input[type=submit].button-right");
        $this->waitForElementPresent("css=div.captcha");
        $this->waitForElementPresent("css=form[id=support]");

    }
}