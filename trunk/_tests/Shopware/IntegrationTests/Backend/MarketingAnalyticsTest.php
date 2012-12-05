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
class Shopware_IntegrationTests_Backend_MarketingAnalyticsTest extends Shopware_IntegrationTests_Backend_LoginTest
{ 
    /**
     * 
     */
    public function testBasicMarketingAnalytics()
    {
        // execute Login
        $this->login();
        // open Application in single mode
        $this->openMarketingAnalytics();
        $this->waitForElementPresent('css=div.x-grid-cell-inner img.sprite-chart-up-color');
    }
    
    /**
     * Open supplier module
     */
    private function openMarketingAnalytics()
    {
        sleep(2);
        $this->open('backend?app=Analytics');
        // Wait until default layer is loaded
        $this->waitForElementPresent('css=div[id^=ext-comp].x-analytics');
    }
}