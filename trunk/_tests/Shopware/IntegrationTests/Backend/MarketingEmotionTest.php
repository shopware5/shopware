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
class Shopware_IntegrationTests_Backend_MarketingEmotionTest extends Shopware_IntegrationTests_Backend_LoginTest
{ 
    /**
     * 
     */
    public function testBasicMarketingEmotion()
    {
        // execute Login
        $this->login();
        // open Application in single mode
        $this->openMarketingEmotion();
        sleep(5);
        $this->waitForElementPresent('css=button[data-action=emotion-list-toolbar-add]');
        $this->click('css=button[data-action=emotion-list-toolbar-add]');
        $this->waitForElementPresent('css=button[data-action=emotion-detail-settings-save]');
    }
    
    /**
     * Open supplier module
     */
    private function openMarketingEmotion()
    {
        sleep(2);
        $this->open('backend?app=Emotion');
        // Wait until default layer is loaded
        $this->waitForElementPresent('css=div[id^=emotion-main-window-]');
    }
}