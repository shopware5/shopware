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
class Shopware_IntegrationTests_Frontend_ArticleVariantTest extends Enlight_Components_Test_Selenium_TestCase
{

    public function testArticleVariant()
    {
        $this->open("beispiele/konfigurator-artikel/143/varianten-artikel-eindimensional?c=1167");

        //  add article to basket without variant - check for error
        $this->waitForElementPresent("css=input[id=basketButton]");
        $this->click("css=input[id=basketButton]");
        $this->waitForElementPresent("css=div.error_container");
        $this->waitForElementPresent("css=a.modal_close");
        $this->click("css=a.modal_close");

        //  add article to basket with variant
        $this->waitForElementPresent("css=select.variant");
        $this->waitForElementPresent("css=select[id=sAdd]");
        $this->select("css=select[id=sAdd]", "label=rot");

        //  add article to basket
        $this->waitForElementPresent("css=input[id=basketButton]");
        $this->click("css=input[id=basketButton]");
        $this->waitForElementPresent("css=div.ajax_add_article_container");
        $this->waitForElementPresent("css=a.modal_close");
        $this->click("css=a.modal_close");

        //  open basket
        $this->waitForElementPresent("css=a[href$='checkout/cart']");
        $this->click("css=a[href$='checkout/cart']");

        //check basket
        $this->waitForTextPresent("Varianten Artikel (eindimensional) rot");
    }
}