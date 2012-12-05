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
 * @group Article
 */
class Shopware_IntegrationTests_Backend_ArticleCategoryTest extends Shopware_IntegrationTests_Backend_LoginTest
{ 
    /**
     * 
     */
    public function testBasicArticleCategory()
    {
        // execute Login
        $this->login();
        // open Application in single mode
        $this->openArticleCategory();
        $this->waitForElementPresent("css=img.x-tree-elbow-end-plus");
        $this->click("css=img.x-tree-elbow-end-plus");
        $this->waitForElementNotPresent("css=img.x-tree-elbow-plus");
    }
    
    /**
     * Open supplier module
     */
    private function openArticleCategory()
    {
        sleep(2);
        $this->open('backend/?app=Category');
        // Wait until default layer is loaded
        $this->waitForElementPresent('css=div[id^=category-main-window]');
    }
}