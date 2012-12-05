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
class Shopware_IntegrationTests_Frontend_OrderBasketTest extends Enlight_Components_Test_Selenium_TestCase
{

    public function testOrderBasket()
    {
        //        Karaffe in den Warenkorb legen
        $this->open("lifestyle-wohnen/6/karaffe");
        $this->waitForElementPresent("css=input[id=basketButton]");
        $this->click("css=input[id=basketButton]");
        $this->waitForElementPresent("css=div.ajax_add_article_container");
        $this->waitForElementPresent("css=a.modal_close");
        $this->click("css=a.modal_close");

        //        Konfigurator Artikel in den Warenkorb legen
        $this->open("beispiele/konfigurator-artikel/143/varianten-artikel-eindimensional?c=1167");
        $this->waitForElementPresent("css=select.variant");
        $this->waitForElementPresent("css=select[id=sAdd]");
        $this->select("css=select[id=sAdd]", "label=rot");
        $this->waitForElementPresent("css=input[id=basketButton]");
        $this->click("css=input[id=basketButton]");
        $this->waitForElementPresent("css=div.ajax_add_article_container");
        $this->waitForElementPresent("css=a.modal_close");
        $this->click("css=a.modal_close");

        //      Warenkorb öffnen
        $this->waitForElementPresent("css=a[href$='checkout/cart']");
        $this->click("css=a[href$='checkout/cart']");

        //      Prüfen ob Artikel im Warenkorb liegen
        $this->waitForElementPresent("css=a[title='Varianten Artikel (eindimensional) rot']");
	    $this->waitForElementPresent("css=img[alt=Karaffe]");

        //      Artikel per Artikelnummer direkt hinzufügen
        $this->waitForElementPresent("css=input[id=basket_add_article]");
        $this->type("css=input[id=basket_add_article]",	"SW2001");
        $this->waitForElementPresent("css=div.add_article input[value=Hinzufügen]");
        $this->click("css=div.add_article input[value=Hinzufügen]");
        $this->waitForElementPresent("css=a[title=Schmuckbaum]");

        //      Anzahl eines Artikel erhöhen
        $this->select("css=div.grid_1 select[name=sQuantity]", "value=2");
	    $this->waitForTextPresent("99,90");

        //      Artikel löschen
        $this->click("css=div.table_row form[name^=basket_change_quantity] a.del");
        $this->waitForElementNotPresent("css=a[title='Karaffe']");
        
    }
}