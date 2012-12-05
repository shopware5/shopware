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
 * @author:    J. Schwehn
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
 * @group banner
 */
class Shopware_IntegrationTests_Backend_BannerTest extends Shopware_IntegrationTests_Backend_LoginTest
{ 
    /**
     * 
     */
    public function testBasicBanner()
    {
        // execute Login
        $this->login();
        // open Application in single mode
        $this->openBanner();
        // Wait until the category tree is build
        $this->waitForElementPresent("css=div[id^=treepanel] table tr.x-grid-row td:nth-child(1)");
        // Select first element
        $this->mouseDown("css=div[id^=treepanel] table tr.x-grid-row td:nth-child(1)");
        // click the first element
        $this->click("css=div[id^=treepanel] table tr.x-grid-row td:nth-child(1)");
        // wait until the banners are loaded
        // $this->waitForElementPresent("css=div[id^=dataview] div.thumb-wrap");
    }
    
    /**
     * Open supplier module
     */
    private function openBanner()
    {
        sleep(2);
        $this->open('backend/?app=Banner');
        // Wait until default layer is loaded
//        $this->waitForElementPresent("css=.x-window-header-text");
        $this->waitForElementPresent('css=button[data-action=addBanner]');
    }
}