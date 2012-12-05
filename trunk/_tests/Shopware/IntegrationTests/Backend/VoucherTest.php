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
 * @author:    M.Schmaeing
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
 * @group voucher
 */
class Shopware_IntegrationTests_Backend_VoucherTest extends Shopware_IntegrationTests_Backend_LoginTest
{

    /**
     * Selenium test case
     */
    public function testBasicVoucher()
    {
        $this->login();
        $this->openVoucher();

        $this->waitForElementPresent('css=div[id^=voucher-voucher-list] button[data-action=add]');
        $this->click('css=div[id^=voucher-voucher-list] button[data-action=add]');
        $this->waitForElementPresent("css=div[id^=voucher-voucher-base_configuration] input[name=description]");
    }
    
    /**
     * Open voucher module
     */
    private function openVoucher()
    {
        sleep(2);
        $this->open('backend/?app=Voucher');
        // Wait until default layer is loaded
        $this->waitForElementPresent("css=.x-window-header-text");
        $this->waitForElementPresent('css=div[id^=voucher-voucher-list] button[data-action=add]');
    }
}