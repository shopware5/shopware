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
 * @author     S. Pohl
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
class Shopware_IntegrationTests_Backend_AuthTest extends Shopware_IntegrationTests_Backend_LoginTest
{

    /**
     * Tests the add user process
     *
     */
    public function testBasicUser()
    {
        // perform a login
        $this->login();
        // open the auth module
        $this->openAuth();
        // Wait until the add button is loaded
        $this->waitForElementPresent("css=div[id^=usermanager-user-list] button[data-action=addUser]");
        // Click the add button
        $this->click("css=div[id^=usermanager-user-list] button[data-action=addUser]");
        // Wait for the add window to show.
        $this->waitForElementPresent("css=div[id^=usermanager-user-create]");
    }
    
    /**
     * Open supplier module
     */
    private function openAuth()
    {
        sleep(2);
        $this->open('backend/?app=UserManager');
        // Wait until default layer is loaded
        $this->waitForElementPresent("css=.x-window-header-text");
        $this->waitForElementPresent('css=button[data-action=addUser]');
    }
}