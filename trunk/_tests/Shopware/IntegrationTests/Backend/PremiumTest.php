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
class Shopware_IntegrationTests_Backend_PremiumTest extends Shopware_IntegrationTests_Backend_LoginTest
{
    /**
     * Add premium article
     */
    public function testBasicPremium()
    {
        $this->login();
        $this->openPremium();
        $this->waitForElementPresent('css=div[id^=premium-main-list] button[data-action=add]');
        $this->click('css=div[id^=premium-main-list] button[data-action=add]');
        $this->waitForElementPresent('css=div[id^=premium-main-detail] input[name=orderNumber]');
    }
    /**
     * Open premium module
     */
    private function openPremium()
    {
        sleep(2);
        $this->open('backend?app=Premium');
    }
}