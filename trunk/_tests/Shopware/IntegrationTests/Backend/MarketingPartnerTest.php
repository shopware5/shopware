<?php
/**
 * Shopware
 *
 * LICENSE
 *
 * Available through the world-wide-web at this URL:
 * http://shopware.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Shopware
 * @package    Shopware_IntegrationTests
 * @subpackage Backend
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @version    $Id$
 * @author:    Patrick Schücker
 * @author     $Author$
 */

/**
 * Test case
 *
 * @category   Shopware
 * @package    Shopware_IntegrationTests
 * @subpackage Backend
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license
 * @group selenium
 * @group Marketing
 */
class Shopware_IntegrationTests_Backend_MarketingPartnerTest extends Shopware_IntegrationTests_Backend_LoginTest
{ 
    /**
     * 
     */
    public function testBasicMarketingPartner()
    {
        // execute Login
        $this->login();
        // open Application in single mode
        $this->openMarketingPartner();
        sleep(2);
        $this->waitForElementPresent('css=button[data-action=add]');
        $this->click('css=button[data-action=add]');
        $this->waitForElementPresent('css=div[id^=partner-partner-window-]');
        $this->waitForElementPresent('css=div[id^=partner-partner-window-] button[id^=button-].x-btn-center');
        $this->click('css=div[id^=partner-partner-window-] button[id^=button-].x-btn-center');
    }
    
    /**
     * Open supplier module
     */
    private function openMarketingPartner()
    {
        sleep(2);
        $this->open('backend?app=Partner');
        // Wait until default layer is loaded
        $this->waitForElementPresent('css=div[id^=partner-main-window-]');
    }
}