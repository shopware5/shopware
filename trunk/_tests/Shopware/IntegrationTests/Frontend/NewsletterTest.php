<?php
/**
 * Test case newsletter subscription
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2012, shopware AG
 * @author Stefan Heyne
 * @package Shopware
 * @subpackage IntegrationTests
 */
class Shopware_IntegrationTests_Frontend_NewsletterTest extends Enlight_Components_Test_Selenium_TestCase
{
/**
 * Test newsletter subscribe & unsubsribe
 *
 * @return void
 */

  public function testNewsletterSubscription()
  {
//    $this->open("/shopware_400/");
    $this->open("newsletter");
    $this->waitForElementPresent("css=#chkmail");
    $this->type("css=#newsletter", "te@shopware.de");
    $this->select("css=#salutation", "value=mr");
    $this->type("css=#firstname", "firstname");
    $this->type("css=#lastname", "lastname");
    $this->type("css=#street", "street");
    $this->type("css=#streetnumber", "999a");
    $this->type("css=#zipcode", "48624");
    $this->type("css=#city", "Schöppingü´ün");
    $this->click("css=.button-right");
    $this->waitForElementPresent("css=.success");
    $this->select("css=#chkmail", "value=-1");
    $this->click("css=.button-right");
    $this->waitForElementPresent("css=.error:contains(Ihre eMail-Adresse wurde gelöscht)");
  }
}
?>