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
class Shopware_IntegrationTests_Frontend_StaticSitesDisclaimerTest extends Enlight_Components_Test_Selenium_TestCase
{

    public function testStaticSitesDisclaimer()
    {
        //  open start page
        $this->open("shopware.php");
//        sleep(2);
        $this->waitForElementPresent("css=ul[id=servicenav]");
        $this->waitForElementPresent("css=div.footer_menu");

        //  check "disclaimer"
        $this->waitForElementPresent("css=a[href$='/widerrufsrecht']");
        $this->click("css=a[href$='/widerrufsrecht']");
        $this->waitForElementPresent("css=div[id=center].custom");
        $this->waitForTextPresent("Widerrufsrecht");

    }
}