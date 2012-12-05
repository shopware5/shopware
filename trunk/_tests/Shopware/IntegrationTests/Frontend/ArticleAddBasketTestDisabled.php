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
class Shopware_IntegrationTests_Frontend_ArticleAddBasketTest extends Enlight_Components_Test_Selenium_TestCase
{

    public function testArticleAddBasket()
    {
        //  add article "Karaffe" to basket
        sleep(5);
        $this->open("/");
        sleep(2);
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

    }
}
