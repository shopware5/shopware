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
class Shopware_IntegrationTests_Frontend_ArticleConfiguratorTest extends Enlight_Components_Test_Selenium_TestCase
{

    public function testArticleConfigurator()
    {
        //  choose article
        $this->open("beispiele/konfigurator-artikel/46/konfigurator-tabelle");
        $this->waitForElementPresent("css=input[value='SW2011_3967.5']");
        $this->check("css=input[value='SW2011_3967.5']");

        //  add article too basket
        $this->waitForElementPresent("css=input[id=basketButton]");
        $this->click("css=input[id=basketButton]");
        $this->waitForElementPresent("css=div.ajax_add_article_container");
        $this->waitForElementPresent("css=a.modal_close");
        $this->click("css=a.modal_close");

        //  open basket
        $this->waitForElementPresent("css=a[href$='checkout/cart']");
        $this->click("css=a[href$='checkout/cart']");

        //  check basket
        $this->waitForTextPresent("SW2011_3967.5");
    }
}