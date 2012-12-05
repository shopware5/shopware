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
 * @group Customer
 */
class Shopware_IntegrationTests_Backend_CustomerNewTest extends Shopware_IntegrationTests_Backend_LoginTest
{ 
    /**
     * 
     */
    public function testBasicCustomerNew()
    {
        // execute Login
        $this->login();
        // open Application in single mode
        $this->openCustomerNew();
        $this->waitForElementPresent('css=input[name=email]');
    }
    
    /**
     * Open supplier module
     */
    private function openCustomerNew()
    {
        sleep(2);
        $this->open('backend');
        // Wait until default layer is loaded
        $this->waitForElementPresent('css=span.customer');
        $this->mouseOver('css=span.customer');
        $this->waitForElementPresent('css=img.sprite-user--plus');
        $this->click('css=img.sprite-user--plus');
        $this->waitForElementPresent('css=div[id^=customer-detail-window]');
    }
}