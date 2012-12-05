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
 * @group System
 */
class Shopware_IntegrationTests_Backend_TranslationTest extends Shopware_IntegrationTests_Backend_LoginTest
{ 
    /**
     * 
     */
    public function testBasicTranslation()
    {
        // execute Login
        $this->login();
        // open Application in single mode
        $this->openTranslation();
        $this->waitForElementPresent('css=button[data-action=translation-main-toolbar-google]');
        sleep(1);
        $this->click('css=button[data-action=translation-main-toolbar-google]');
        $this->waitForElementPresent('css=button[data-action=translation-main-services-window-translate]');
        $this->waitForElementPresent('css=div[id^=translation-main-services-window-].x-box-inner img[id^=tool].x-tool-close');
        $this->click('css=div[id^=translation-main-services-window-].x-box-inner img[id^=tool].x-tool-close');
    }
    
    /**
     * Open supplier module
     */
    private function openTranslation()
    {
        sleep(2);
        $this->open('backend?app=Article');
        // Wait until default layer is loaded
        $this->waitForElementPresent('css=span.sprite-globe-green');
        $this->click('css=span.sprite-globe-green');
        $this->waitForElementPresent('css=div[id^=translation-main-form].x-panel-body');
    }
}