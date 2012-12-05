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
 * @author     Heiner Lohaus
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
class Shopware_IntegrationTests_Backend_WorkshopTest extends Enlight_Components_Test_Selenium_TestCase
{
    /**
     * Open a backend app in the selenium browser.
     *
     * @param string $app
     * @return void
     */
    public function openApp($app = 'Index')
    {
        $this->open('backend?app=' . urlencode($app));

        $this->waitForElementPresent('css=.login-window');

        $this->type('css=.login-window input[name=username]', 'demo');
        $this->type('css=.login-window input[name=password]', 'demo');
        $this->click('css=.login-window button');
    }

    /**
     * Selenium test case
     */
    public function testSelectUser()
    {
    	return;
        $this->openApp('Workshop');
        $this->waitForElementPresent('css=div[id^=workshop-resource-tree] .x-grid-table .x-grid-row');
        $this->click('css=div[id^=workshop-resource-tree] .x-grid-table .x-grid-row');
        $this->waitForElementPresent('css=div[id^=workshop-user-list] .x-grid-row');
        $this->mouseDown('css=div[id^=workshop-user-list] .x-grid-row');
        $this->click('css=.login-window button[data-action=workshop-resource-tree-edit]');
    }

    /**
     * Selenium test case
     */
    public function testEditResource()
    {
    	return;
        $this->openApp('Workshop');
        $this->waitForElementPresent('css=div[id^=workshop-resource-tree] .x-grid-table .x-grid-row');
        $this->click('css=div[id^=workshop-resource-tree] .x-grid-table .x-grid-row');
        $this->click('css=div[id^=workshop-resource-tree] button[data-action=workshop-resource-tree-edit]');
    }
}