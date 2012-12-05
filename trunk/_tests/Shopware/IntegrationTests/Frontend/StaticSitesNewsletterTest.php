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
class Shopware_IntegrationTests_Frontend_StaticSitesNewsletterTest extends Enlight_Components_Test_Selenium_TestCase
{

    public function testStaticSitesNewsletter()
    {
        //  open start page
        $this->open("shopware.php");
//        sleep(2);
        $this->waitForElementPresent("css=ul[id=servicenav]");
        $this->waitForElementPresent("css=div.footer_menu");

       //  check "newsletter"
        $this->waitForElementPresent("css=a[href$='shopware.php?sViewport=newsletter']");
        $this->click("css=a[href$='shopware.php?sViewport=newsletter']");
        $this->waitForElementPresent("css=input[type=submit].button-right");
        $this->waitForElementPresent("css=form[id=letterForm]");

    }
}