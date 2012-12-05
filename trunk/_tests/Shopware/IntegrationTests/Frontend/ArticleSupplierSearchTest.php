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
class Shopware_IntegrationTests_Frontend_ArticleSupplierSearchTest extends Enlight_Components_Test_Selenium_TestCase
{

    public function testArticleSupplierSearch()
    {
        $this->open("lifestyle-wohnen/6/karaffe");
        $this->waitForElementPresent("css=a[href$='sViewport=search&sSearch=2&sSearchMode=supplier&sSearchText=menu']");
        $this->click("css=a[href$='sViewport=search&sSearch=2&sSearchMode=supplier&sSearchText=menu']");
        $this->waitForElementPresent("css=a[title='Bade Salz Wellness']");
        $this->waitForElementPresent("css=a[title='PowerCare']");
    }
}