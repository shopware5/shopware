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
 * @author     Benjamin Cremer
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
class Shopware_IntegrationTests_Backend_FormTest extends Shopware_IntegrationTests_Backend_LoginTest
{
    /**
     * Selenium test case
     */
    public function testBasicForm()
    {
        $this->openApp();

        $this->waitForElementPresent('css=div[id^=form-main-mainwindow] button[data-action=add]');
        $this->click('css=div[id^=form-main-mainwindow] button[data-action=add]');

        $this->waitForElementPresent('css=div[id^=form-main-editwindow]');
        $this->waitForElementPresent('css=div[id^=form-main-editwindow] .x-window-header-text');
    }
    /**
     * Open the app
     */
    public function openApp()
    {
        $this->open('backend?app=Form');

        $this->waitForElementPresent('css=.login-window');

        $this->type('css=.login-window input[name=username]', $this->loginUser);
        $this->type('css=.login-window input[name=password]', $this->loginPass);
        $this->click('css=.login-window button');

        // Wait until default layer is loaded
        $this->waitForElementPresent("css=.x-window-header-text");
        $this->waitForElementPresent('css=div[id^=form-main-mainwindow]');
    }
}