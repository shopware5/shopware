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
class Shopware_IntegrationTests_Frontend_StaticSitesPrivacyTest extends Enlight_Components_Test_Selenium_TestCase
{

    public function testStaticSitesPrivacy()
    {
        //  open start page
        $this->open("shopware.php");
//        sleep(2);
        $this->waitForElementPresent("css=ul[id=servicenav]");
        $this->waitForElementPresent("css=div.footer_menu");

        //  check "privacy"
        $this->waitForElementPresent("css=a[href$='/datenschutz']");
        $this->click("css=a[href$='/datenschutz']");
        $this->waitForElementPresent("css=div[id=center].custom");
        $this->waitForTextPresent("Datenschutz");


    }
}