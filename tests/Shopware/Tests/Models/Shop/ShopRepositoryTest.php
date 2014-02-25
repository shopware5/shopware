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

class Shopware_Tests_Models_ShopRepositoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * Ensures that getActiveByRequest() returns the correct shop
     *
     * @ticket SW-7774
     * @ticket SW-6768
     */
    public function testGetActiveByRequest()
    {
        // Backup and change existing main shop
        $mainShopBackup = Shopware()->Db()->fetchRow("SELECT * FROM s_core_shops WHERE id = 1");
        Shopware()->Db()->update('s_core_shops', array(
            'host' => 'fallbackhost',
            'secure' => 1,
            'secure_base_path' => '/secure'
        ), 'id = 1');
        $mainShop   = Shopware()->Db()->fetchRow("SELECT * FROM s_core_shops WHERE id = 1");

        // Create test shops
        $sql = "
            INSERT IGNORE INTO `s_core_shops` (`id`, `main_id`, `name`, `title`, `position`, `host`, `base_path`, `base_url`, `hosts`, `secure`, `secure_host`, `secure_base_path`, `template_id`, `document_template_id`, `category_id`, `locale_id`, `currency_id`, `customer_group_id`, `fallback_id`, `customer_scope`, `default`, `active`, `always_secure`) VALUES
            (100, 1, 'testShop1', 'Testshop', 0, NULL, NULL, ?, '', 0, NULL, ?, 11, 11, 11, 2, 1, 1, 2, 0, 0, 1, 0),
            (101, 1, 'testShop2', 'Testshop', 0, NULL, NULL, ?, '', 0, NULL, ?, 11, 11, 11, 2, 1, 1, 2, 0, 0, 1, 0),
            (102, 1, 'testShop3', 'Testshop', 0, NULL, NULL, ?, '', 0, NULL, ?, 11, 11, 11, 2, 1, 1, 2, 0, 0, 1, 0),
            (103, 1, 'testShop4', 'Testshop', 0, NULL, NULL, ?, '', 0, NULL, ?, 11, 11, 11, 2, 1, 1, 2, 0, 0, 1, 0),
            (104, 1, 'testShop5', 'Testshop', 0, NULL, NULL, ?, '', 0, NULL, ?, 11, 11, 11, 2, 1, 1, 2, 0, 0, 1, 0);

        ";
        Shopware()->Db()->query($sql, array(
            $mainShop['base_path']."/english", $mainShop['secure_base_path']."/english",
            $mainShop['base_path']."/en/uk", $mainShop['secure_base_path']."/en/uk",
            $mainShop['base_path']."/en", $mainShop['secure_base_path']."/en",
            $mainShop['base_path']."/en/us", $mainShop['secure_base_path']."/en/us",
            $mainShop['base_path']."/aus/en", $mainShop['secure_base_path']."/aus/en"
        ));

        /** @var $repository \Shopware\Models\Shop\Repository */
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setHttpHost($mainShop["host"]);

        // Tests copied for SW-6768
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"]."/en", "testShop3");
        //check virtual url with superfluous / like localhost/en/
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"]."/en/", "testShop3");
        //check virtual url with direct controller call like localhost/en/blog
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"]."/en/blog", "testShop3");
        //check base shop with direct controller call like localhost/en/blog
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"]."/blog", $mainShop["name"]);
        //check without virtual url but an url with the same beginning like localhost/entsorgung
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"]."/entsorgung", $mainShop["name"]);
        //check different virtual url with like localhost/ente
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"]."/en/uk", "testShop2");
        //check without virtual url it has to choose the main shop instead of the language shop without the virtual url
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"], $mainShop["name"]);

        // These are just some basic urls
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"]."", $mainShop["name"]);
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"]."/", $mainShop["name"]);
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"]."/foo/en", $mainShop["name"]);
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"]."/foo/entsorgung", $mainShop["name"]);
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"]."/fenglish", $mainShop["name"]);
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"]."/english", 'testShop1');
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"]."/en", 'testShop3');

        // These cover the cases affected by the ticket, where the base_path would be present in the middle of the url
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"]."/foo/english", $mainShop["name"]);
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"]."/foo/en", $mainShop["name"]);
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"]."/foo/enaaa/", $mainShop["name"]);
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"]."/foo/uk/", $mainShop["name"]);
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"]."/foo/en/uk/", $mainShop["name"]);
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"]."/foo/en/uk/things", $mainShop["name"]);

        // And these are some extreme cases, due to the overlapping of urls
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"]."/en/ukfoooo", 'testShop3');
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"]."/en/uk", 'testShop2');
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"]."/en", 'testShop3');
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"]."/en/uk/things", 'testShop2');

        // Tests for secure
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["secure_base_path"]."/en/us", 'testShop4', true);
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["secure_base_path"]."/en/us", 'testShop4', false);
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["secure_base_path"]."/en/ukfoooo", 'testShop3', true);
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["secure_base_path"]."/en/ukfoooo", 'testShop3', false);
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["secure_base_path"]."/en/uk", 'testShop2', true);
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["secure_base_path"]."/en/uk", 'testShop2', false);
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["secure_base_path"]."/en/uk/things", 'testShop2', true);
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["secure_base_path"]."/en/uk/things", 'testShop2', false);

        // Remove test data and restore previous status
        Shopware()->Db()->exec("DELETE FROM s_core_shops WHERE id IN (100, 101, 102, 103, 104);");
        unset($mainShopBackup['id']);
        Shopware()->Db()->update('s_core_shops', $mainShopBackup, 'id = 1');
    }

    /**
     * helper method to call the getActiveByRequest Method with different params
     *
     * @param $request
     * @param $repository
     * @param $url
     * @param $shopName
     * @internal param $mainShop
     */
    public function callGetActiveShopByRequest(Enlight_Controller_Request_Request $request, \Shopware\Models\Shop\Repository $repository, $url, $shopName, $secure = false)
    {
        $request->setRequestUri($url);
        $request->setSecure($secure);

        $shop = $repository->getActiveByRequest($request);

        $this->assertNotNull($shop);
        $this->assertEquals($shopName, $shop->getName());
    }
}
