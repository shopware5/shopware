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
class Shopware_IntegrationTests_Backend_SupplierTest extends Shopware_IntegrationTests_Backend_LoginTest
{
    /**
     * Selenium test case
     */
    public function testBasicSupplier()
    {
        $this->login();
        $this->openSupplier();
        $this->click('css=div[id^=supplierGrid] button[data-action=addSupplier]');
        $this->waitForElementPresent("css=div[id^=supplier-create] input[name=name]");
    }


    /**
     * Open supplier module
     */
    private function openSupplier()
    {
        sleep(2);
        $this->open('backend/?app=Supplier');
        // Wait until default layer is loaded
        $this->waitForElementPresent("css=.x-window-header-text");
        $this->waitForElementPresent('css=div[id^=supplierGrid] button[data-action=addSupplier]');
    }
}