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
    protected function setUp()
    {
        $sql = "SELECT base_path FROM s_core_shops WHERE id = 1";
        $mainBasePath = Shopware()->Db()->fetchOne($sql);

        $sql = "
            INSERT IGNORE INTO `s_core_shops` (`id`, `main_id`, `name`, `title`, `position`, `host`, `base_path`, `base_url`, `hosts`, `secure`, `secure_host`, `secure_base_path`, `template_id`, `document_template_id`, `category_id`, `locale_id`, `currency_id`, `customer_group_id`, `fallback_id`, `customer_scope`, `default`, `active`) VALUES
            (102, 1, 'testShop1', 'Testshop', 0, NULL, NULL, ?, '', 0, NULL, NULL, 11, 11, 11, 2, 1, 1, 2, 0, 0, 1),
            (101, 1, 'testShop2', 'Testshop', 0, NULL, NULL, ?, '', 0, NULL, NULL, 11, 11, 11, 2, 1, 1, 2, 0, 0, 1),
            (100, 1, 'testShop3', 'Testshop', 0, NULL, NULL, ?, '', 0, NULL, NULL, 11, 11, 11, 2, 1, 1, 2, 0, 0, 1);
        ";
        Shopware()->Db()->query($sql, array(
            $mainBasePath."/english",
            $mainBasePath."/en/uk",
            $mainBasePath."/en"
        ));

        $sql = "UPDATE `s_core_shops` SET `host` = 'fallbackhost' WHERE `id` = 1 AND `host` = ''";
        Shopware()->Db()->query($sql);
    }

    protected function tearDown()
    {
        // Remove test data
        $sql = "DELETE FROM s_core_shops WHERE id IN (100, 101, 102);";
        Shopware()->Db()->exec($sql);

        $sql = "UPDATE `s_core_shops` SET `host` = '' WHERE `id` = 1 AND `host` = 'fallbackhost'";
        Shopware()->Db()->query($sql);
    }

    /**
     * Ensures that getActiveByRequest() returns the correct shop
     *
     * @ticket SW-7774
     */
    public function testGetActiveByRequest()
    {
        /** @var $repository \Shopware\Models\Shop\Repository */
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
        $mainShop   = Shopware()->Db()->fetchRow("SELECT * FROM s_core_shops WHERE id = 1");

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setHttpHost($mainShop["host"]);

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
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"]."/en/uk/things", 'testShop2');
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
    public function callGetActiveShopByRequest(Enlight_Controller_Request_Request $request, \Shopware\Models\Shop\Repository $repository, $url, $shopName)
    {
        $request->setRequestUri($url);

        $shop = $repository->getActiveByRequest($request);

        $this->assertNotNull($shop);
        $this->assertEquals($shopName, $shop->getName());
    }
}
