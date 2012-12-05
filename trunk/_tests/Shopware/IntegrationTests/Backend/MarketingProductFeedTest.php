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
class Shopware_IntegrationTests_Backend_MarketingProductFeedTest extends Shopware_IntegrationTests_Backend_LoginTest
{ 
    /**
     * 
     */
    public function testBasicMarketingProductFeed()
    {
        // execute Login
        $this->login();
        // open Application in single mode
        $this->openMarketingProductFeed();
        $this->waitForElementPresent('css=button[data-action=add]');
        $this->click('css=button[data-action=add]');
        $this->waitForElementPresent('css=button[data-action=save]');
    }
    
    /**
     * Open supplier module
     */
    private function openMarketingProductFeed()
    {
        sleep(1);
        $this->open('backend?app=ProductFeed');
        // Wait until default layer is loaded
        $this->waitForElementPresent('css=div[id^=product_feed-main-window-]');
    }
}