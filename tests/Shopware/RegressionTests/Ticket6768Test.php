<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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
 *
 */

/**
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Shopware_RegressionTests_Ticket6768Test extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * Tests set up method
     */
    public function setUp()
    {
        parent::setUp();

        Shopware()->Bootstrap()
                ->resetResource('Template');

        $sql= "SELECT base_path FROM s_core_shops WHERE id = 1";
        $mainBasePath = Shopware()->Db()->fetchOne($sql);

        $sql = "
            INSERT IGNORE INTO `s_core_shops` (`id`, `main_id`, `name`, `title`, `position`, `host`, `base_path`, `base_url`, `hosts`, `secure`, `secure_host`, `secure_base_path`, `template_id`, `document_template_id`, `category_id`, `locale_id`, `currency_id`, `customer_group_id`, `fallback_id`, `customer_scope`, `default`, `active`) VALUES
            (10, 1, 'testShop1', 'Testshop', 0, NULL, NULL, ?, '', 0, NULL, NULL, 11, 11, 11, 2, 1, 1, 2, 0, 0, 1),
            (11, 1, 'testShop2', 'Testshop', 0, NULL, NULL, ?, '', 0, NULL, NULL, 11, 11, 11, 2, 1, 1, 2, 0, 0, 1),
            (12, 1, 'testShop3', 'Testshop', 0, NULL, NULL, '', '', 0, NULL, NULL, 11, 11, 11, 2, 1, 1, 2, 0, 0, 1);
        ";
        Shopware()->Db()->query($sql,array($mainBasePath."/en",$mainBasePath."/ente"));

        $sql= "UPDATE `s_core_shops` SET `host` = 'fallbackhost' WHERE `id` = 1 AND `host` = ''";
        Shopware()->Db()->query($sql);
    }

    public function tearDown()
    {
        parent::tearDown();
        $sql = "
            DELETE FROM s_core_shops WHERE id IN (10, 11, 12);
        ";
        Shopware()->Db()->exec($sql);

        $sql= "UPDATE `s_core_shops` SET `host` = '' WHERE `id` = 1 AND `host` = 'fallbackhost'";
        Shopware()->Db()->query($sql);
    }

    public function testVirtualURLs()
    {
        $request = $this->Request();
        $repository = 'Shopware\Models\Shop\Shop';

        /** @var $repository \Shopware\Models\Shop\Repository */
        $repository = Shopware()->Models()->getRepository($repository);
        $sql= "SELECT * FROM s_core_shops WHERE id = 1";
        $mainShop = Shopware()->Db()->fetchRow($sql);
        $request->setHttpHost($mainShop["host"]);
        //check normal virtual url like localhost/en
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"]."/en", "testShop1");

        //check virtual url with superfluous / like localhost/en/
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"]."/en/", "testShop1");

        //check virtual url with direct controller call like localhost/en/blog
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"]."/en/blog", "testShop1");

        //check base shop with direct controller call like localhost/en/blog
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"]."/blog", $mainShop["name"]);

        //check without virtual url but an url with the same beginning like localhost/entsorgung
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"]."/entsorgung", $mainShop["name"]);

        //check different virtual url with like localhost/ente
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"]."/ente", "testShop2");

        //check without virtual url it has to choose the main shop instead of the language shop without the virtual url
        $this->callGetActiveShopByRequest($request, $repository, $mainShop["base_path"], $mainShop["name"]);
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
