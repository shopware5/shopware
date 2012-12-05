<?php
/**
 * Shopware
 *
 * LICENSE
 *
 * Available through the world-wide-web at this URL:
 * http://shopware.de/license
 * If you did t receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Shopware
 * @package    Shopware_IntegrationTests
 * @subpackage Backend
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @version    $Id$
 * @author:    P.Stahl
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
class Shopware_IntegrationTests_Backend_PaymentTest extends Shopware_IntegrationTests_Backend_LoginTest
{
    public function testBasicPayments() {
        $this->login();
        $this->openPayment();
        $this->waitForElementPresent('css=div[id^=payment-main-window]');
        $this->click('css=div[id^=payment-main-tree] button[data-action=create]');
        $this->waitForElementPresent('css=div[id^=payment-main-tree] tr');
    }

    private function openPayment(){
        sleep(2);
        $this->open('backend?app=Payment');
    }
}