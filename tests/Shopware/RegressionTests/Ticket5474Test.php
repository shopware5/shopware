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
 * Regression Test for Ticket 5474
 *
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Shopware_RegressionTests_Ticket5474 extends Enlight_Components_Test_Plugin_TestCase
{

    /**
     * Test if the query returns all necessary information
     */
    public function testCategoryBackendGetDetailQuery()
    {

        /**
         * @var $repository Shopware\Models\Category\Repository
         */
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Category\Category');

        //category Edelbrände
        $categoryDetailArray = $repository->getDetailQuery(14)->getArrayResult();

        $articleData = $categoryDetailArray[0]["articles"][0];
        $this->assertNotEmpty($articleData);

        $this->assertEquals(10, $articleData["id"]);
        $this->assertEquals('Aperitif-Glas Demi Sec', $articleData["name"]);

        $mainDetailData = $articleData["mainDetail"];
        $this->assertNotEmpty($mainDetailData);
        $this->assertEquals('16', $mainDetailData["id"]);
        $this->assertEquals('SW10010', $mainDetailData["number"]);

        $supplierData = $articleData["supplier"];
        $this->assertNotEmpty($supplierData);
        $this->assertEquals('2', $supplierData["id"]);
        $this->assertEquals('Feinbrennerei Sasse', $supplierData["name"]);

    }
}
