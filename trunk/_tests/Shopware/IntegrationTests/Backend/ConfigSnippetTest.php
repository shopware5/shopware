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
 * @group Config
 */
class Shopware_IntegrationTests_Backend_ConfigSnippetTest extends Shopware_IntegrationTests_Backend_LoginTest
{ 
    /**
     * 
     */
    public function testBasicConfigSnippet()
    {
        // execute Login
        $this->login();
        // open Application in single mode
        $this->openConfigSnippet();
        sleep(2);
        $this->waitForElementPresent('css=img.x-tree-elbow-plus');
        $this->click('css=img.x-tree-elbow-plus');
        $this->waitForElementPresent('css=div.x-grid-cell-inner img.x-tree-elbow-line');
    }
    
    /**
     * Open supplier module
     */
    private function openConfigSnippet()
    {
        sleep(2);
        $this->open('backend?app=Snippet');
        // Wait until default layer is loaded
        $this->waitForElementPresent('css=div[id^=snippet-main-window-]');
    }
}