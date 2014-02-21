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

class Shopware_Tests_Models_ShopRepositoryTest extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * Ensures that getActiveByRequest() returns the correct shop
     *
     * @ticket SW-7774
     */
    public function testGetActiveByRequest()
    {
        // Set up some test shops
        Shopware()->Bootstrap()
            ->resetResource('Template');

        $sql= "SELECT base_path FROM s_core_shops WHERE id = 1";
        $mainBasePath = Shopware()->Db()->fetchOne($sql);

        $sql = "
            INSERT IGNORE INTO `s_core_shops` (`id`, `main_id`, `name`, `title`, `position`, `host`, `base_path`, `base_url`, `hosts`, `secure`, `secure_host`, `secure_base_path`, `template_id`, `document_template_id`, `category_id`, `locale_id`, `currency_id`, `customer_group_id`, `fallback_id`, `customer_scope`, `default`, `active`) VALUES
            (100, 1, 'testShop1', 'Testshop', 0, NULL, NULL, ?, '', 0, NULL, NULL, 11, 11, 11, 2, 1, 1, 2, 0, 0, 1),
            (101, 1, 'testShop2', 'Testshop', 0, NULL, NULL, ?, '', 0, NULL, NULL, 11, 11, 11, 2, 1, 1, 2, 0, 0, 1),
            (102, 1, 'testShop3', 'Testshop', 0, NULL, NULL, ?, '', 0, NULL, NULL, 11, 11, 11, 2, 1, 1, 2, 0, 0, 1);
        ";
        Shopware()->Db()->query(
            $sql, array(
            $mainBasePath."/english",
            $mainBasePath."/en/uk",
            $mainBasePath."/en"
        ));

        $sql= "UPDATE `s_core_shops` SET `host` = 'fallbackhost' WHERE `id` = 1 AND `host` = ''";
        Shopware()->Db()->query($sql);


        // The actual testing
        $request = $this->Request();
        $repository = 'Shopware\Models\Shop\Shop';

        /** @var $repository \Shopware\Models\Shop\Repository */
        $repository = Shopware()->Models()->getRepository($repository);
        $sql= "SELECT * FROM s_core_shops WHERE id = 1";
        $mainShop = Shopware()->Db()->fetchRow($sql);
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

        // Remove test data
        $sql = "
            DELETE FROM s_core_shops WHERE id IN (100, 101, 102);
        ";
        Shopware()->Db()->exec($sql);

        $sql= "UPDATE `s_core_shops` SET `host` = '' WHERE `id` = 1 AND `host` = 'fallbackhost'";
        Shopware()->Db()->query($sql);
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
    public function callGetActiveShopByRequest($request, $repository, $url, $shopName)
    {
        $request->setRequestUri($url);
        $shop = $repository->getActiveByRequest($request);
        $this->assertNotNull($shop);
        $this->assertEquals($shop->getName(), $shopName);
    }
}
