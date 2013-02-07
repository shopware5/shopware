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
class Shopware_RegressionTests_Ticket5152 extends Enlight_Components_Test_Plugin_TestCase
{
    /**
     * Test case method
     */
    public function testDbCache()
    {
        $db = Shopware()->Adodb();

        $sql = '
            SELECT SQL_CALC_FOUND_ROWS *
            FROM `s_articles`
            LIMIT 10
        ';
        $rows = $db->CacheGetAll(100, $sql, false, 'category_3');
        $foundRows = $db->CacheGetFoundRows();

        $this->assertEquals(10, count($rows));
        $this->assertGreaterThan(10, $foundRows);
    }
}
