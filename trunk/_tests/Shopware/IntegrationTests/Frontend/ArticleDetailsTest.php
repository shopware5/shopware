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
class Shopware_IntegrationTests_Frontend_ArticleDetailsTest extends Enlight_Components_Test_Selenium_TestCase
{

    public function testArticleDetails()
    {
        $this->open("lifestyle-wohnen/6/karaffe");
        $this->waitForElementPresent("css=div.article_details_price");
        $this->waitForElementPresent("css=span.frontend_detail_tabs");
        $this->waitForElementPresent("css=a[href=#comments]");
        $this->waitForElementPresent("css=div.content");

    }
}