<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Tests_Controllers_Frontend_ListingTest extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * Test Atom - Feeds
     * Call Category with Parameter sAtom=1 and parse Results
     * Parse result through Zend_Feed and count feed items
     *
     * @ticket 4624
     */
    public function testAtom()
    {
        //TODO - Activate after DOMDocument-Update
        return;

        $this->Front()->setParam('noViewRenderer', false);
        $this->dispatch('/Listing/index/?sCategory=1161&sAtom=1');
        $body = $this->Response()->getBody();
        $feed = new Zend_Feed_Atom(null,$body);
        $this->assertGreaterThan(1,$feed->count());
    }

    /**
     * Test Rss-Feeds
     * Call Category with Parameter sRss=1 and parse Results
     * Parse result through Zend_Feed and count feed items
     *
     * @ticket 4624
     */
    public function testRss()
    {
        //TODO - Activate after DOMDocument-Update
        return;

        $this->Front()->setParam('noViewRenderer', false);
        $this->dispatch('/Listing/index/?sCategory=1161&sRss=1');
        $body = $this->Response()->getBody();
        $feed = new Zend_Feed_Rss(null,$body);
        $this->assertGreaterThan(1, $feed->count());
    }
}
