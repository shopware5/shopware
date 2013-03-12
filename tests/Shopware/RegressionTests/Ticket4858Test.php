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
 *
 * @author     Heiner Lohaus
 */

/**
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_RegressionTests_Ticket4858Test extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * Tests set up method
     */
    public function setUp()
    {
        parent::setUp();

        Shopware()->Bootstrap()
            ->resetResource('Template');

        $sql = "
            INSERT IGNORE INTO `s_core_shops` (
              `id`, `main_id`, `name`, `title`, `position`,
              `host`, `base_path`, `base_url`, `hosts`,
              `secure`, `secure_host`, `secure_base_path`,
              `template_id`, `document_template_id`, `category_id`,
              `locale_id`, `currency_id`, `customer_group_id`,
              `fallback_id`, `customer_scope`, `default`, `active`
            ) VALUES (
              10, NULL, 'Testshop 2', 'Testshop 2', 0,
              '2test.in', NULL, NULL, '2fr.test.in\\n2nl.test.in\\n',
              0, NULL, NULL,
              11, 11, 11, 2, 1, 1, 2, 0, 0, 1
            ), (
              11, NULL, 'Testshop 1', 'Testshop 1', 0,
              'test.in', NULL, NULL, 'fr.test.in\\nnl.test.in\\n',
              0, NULL, NULL,
              11, 11, 11, 2, 1, 1, 2, 0, 0, 1
            );
        ";
        Shopware()->Db()->exec($sql);
    }

    public function tearDown()
    {
        parent::tearDown();
        $sql = "
            DELETE FROM s_core_shops WHERE id IN (10, 11);
        ";
        Shopware()->Db()->exec($sql);
    }

    public function getTestData()
    {
        return array(
            array('test.in', 'fr.test.in'),
            array('test.in', 'nl.test.in'),
            array('2test.in', '2fr.test.in'),
            array('2test.in', '2nl.test.in')
        );
    }

    /**
    /**
     * @dataProvider getTestData
     */
    public function testMultiShopLocation($host, $alias)
    {
        $request = $this->Request();
        $repository = 'Shopware\Models\Shop\Shop';
        /** @var $repository \Shopware\Models\Shop\Repository */
        $repository = Shopware()->Models()->getRepository($repository);

        $this->Request()->setHttpHost($alias);
        $shop = $repository->getActiveByRequest($request);

        $this->assertNotNull($shop);
        $this->assertEquals($host, $shop->getHost());
    }
}
