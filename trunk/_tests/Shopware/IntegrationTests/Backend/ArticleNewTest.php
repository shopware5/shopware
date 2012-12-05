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
class Shopware_IntegrationTests_Backend_ArticleNewTest extends Shopware_IntegrationTests_Backend_LoginTest
{ 
    /**
     * 
     */
    public function testBasicArticleNew()
    {
        // execute Login
        $this->login();
        // open Application in single mode
        $this->openArticleNew();
        $this->waitForElementPresent("css=div.x-form-arrow-trigger");
        $this->click("css=div.x-form-arrow-trigger");
        $this->waitForElementNotPresent("css=li.x-boundlist-list");
    }
    
    /**
     * Open supplier module
     */
    private function openArticleNew()
    {
        sleep(2);
        $this->open('backend/?app=Article');
        // Wait until default layer is loaded
        $this->waitForElementPresent('css=div[id^=article-detail-window] input[name=name]');
    }
}