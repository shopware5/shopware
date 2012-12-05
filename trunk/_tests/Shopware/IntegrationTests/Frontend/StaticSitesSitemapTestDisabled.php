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
class Shopware_IntegrationTests_Frontend_StaticSitesSitemapTest extends Enlight_Components_Test_Selenium_TestCase
{

    public function testStaticSitesSitemap()
    {
        //  open start page
         $this->open("shopware.php");
//        sleep(2);
        $this->waitForElementPresent("css=ul[id=servicenav]");
        $this->waitForElementPresent("css=div.footer_menu");

        //  check "sitemap"
        $this->waitForElementPresent("css=a[href$='/sitemap']");
        $this->click("css=a[href$='/sitemap']");
        $this->waitForElementPresent("css=div[id=center].^grid");
        $this->waitForTextPresent("Sitemap");

    }
}
