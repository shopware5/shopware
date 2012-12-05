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
 * @group supplier
 */
class Shopware_IntegrationTests_Backend_ShippingTest extends Shopware_IntegrationTests_Backend_LoginTest
{

    /** 
     * Selenium test case
     */

    public function testBasicShipping() 
    {
        $this->login();
        $this->openShipping();
        $this->click("css=div[id^=dispatchGrid] button[data-action=addShipping]");
        $this->waitForElementPresent("css=div[id^=shopware-shipping-edit]");
    }

    /**
     * Open supplier module
     */
    private function openShipping()
    {
        sleep(2);
        $this->open('backend/?app=Shipping');
        // Wait until default layer is loaded
        $this->waitForElementPresent("css=.x-window-header-text");
        $this->waitForElementPresent('css=div[id^=dispatchGrid]');
    }
}