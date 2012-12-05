<?php
/**
 * Test case Frontend Search
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2012, shopware AG
 * @author Stefan Heyne
 * @package Shopware
 * @subpackage IntegrationTests
 */
class Shopware_IntegrationTests_Frontend_SearchTest extends Enlight_Components_Test_Selenium_TestCase
{
    /**
     * Test article search-functions by name & number
     *
     */

    public function testSearchTest()
    {
    //searching by name
    $this->open('shopware.php');
    $this->focus("css=#searchfield");
    $this->type("css=#searchfield", "karaffe");
    $this->waitForElementPresent("css=.resultlink");
    $this->click("css=#submit_search");
    $this->waitForElementPresent("css=.result_box");
    $this->click("css=a[title=\"Karaffe\"]");
    $this->waitForElementPresent("css=#detailbox");

    //searching by articlenumber
    $this->focus("css=#searchfield");
    $this->type("css=#searchfield", "SW2001_5409");
    $this->click("css=#submit_search");
    $this->waitForElementPresent("css=#detailbox");
  }
}
?>