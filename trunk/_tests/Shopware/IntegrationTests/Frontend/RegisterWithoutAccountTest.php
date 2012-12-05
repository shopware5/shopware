<?php
/**
 * Check Register without an customer account
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2012, shopware AG
 * @author Sebastian Kloepper
 * @package Shopware
 * @subpackage IntegrationTests
 */
class Shopware_IntegrationTests_Frontend_RegisterWithoutAccountTest extends Enlight_Components_Test_Selenium_TestCase
{
    /**
     * Register Test
     */
    public function testRegister()
    {
        $this->open('account/logout/');

        // Open modal-box
        $this->click("link=Mein Konto");
        $this->waitForElementPresent("css=.new_customer .button-right");
        $this->click("css=.new_customer .button-right");

        // Form Register
        $this->waitForElementPresent("css=#firstname");
        $this->type("css=#firstname", "Selenium");
        $this->type("css=#lastname", "Test");

        // Register without regular account
        $this->click("css=#register_personal_skipLogin");
        $this->click("css=#register_personal_email");
        $this->type("css=#register_personal_email", "selenium@shopware.de");
        $this->type("css=#phone", "02555-928850");
        $this->type("css=#street", "Test");
        $this->type("css=#streetnumber", "6");
        $this->type("css=#zipcode", "48624");
        $this->type("css=#city", "Schöppingen");
        $this->select("css=#country", "label=Deutschland");
        $this->click("css=#country > option[value=\"2\"]");
        $this->click("css=#registerbutton");

        $this->waitForElementPresent("css=.cat_text");
        $this->click("css=.logout");
    }
}