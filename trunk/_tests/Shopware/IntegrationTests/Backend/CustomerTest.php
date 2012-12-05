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
 * @author:    Oliver Denter
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
 */
class Shopware_IntegrationTests_Backend_CustomerTest extends Shopware_IntegrationTests_Backend_LoginTest
{
    public function testBasicCustomer()
    {
        $this->login();
        $this->openCustomer();
        $this->addCustomer();
    }

    private function openCustomer() {
        sleep(2);
        $this->open('backend?app=Customer');
        $this->waitForElementPresent('css=div[id^=customer-list] button[data-action=addCustomer]');
    }

    private function addCustomer() {
        //wait for the add button
        $this->waitForElementPresent('css=div[id^=customer-list] button[data-action=addCustomer]');
        $this->click('css=div[id^=customer-list] button[data-action=addCustomer]');
        //wait for the email field
        $this->waitForElementPresent('css=div[id^=customer-detail-window] input[name=email]');
    }
}
