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
class Shopware_IntegrationTests_Backend_VoteTest extends Shopware_IntegrationTests_Backend_LoginTest
{
    /**
     * TestCase to test opening the infoPanel and its information
     */
    public function testBasicOpenInfoPanelInformation()
    {
        $this->login();
        $this->openVote();

        $this->waitForElementPresent('css=div[id^=vote-main-list] tr:nth-child(1)');
    }


    private function openVote()
    {
        sleep(2);
        $this->open('backend?app=Vote');
    }
}