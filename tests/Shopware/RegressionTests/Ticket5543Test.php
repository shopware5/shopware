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
 */

/**
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Shopware_RegressionTests_Ticket5543 extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * Set up the order documents
     */
    public function setUp()
    {
        parent::setUp();

        $sql= "INSERT IGNORE INTO `s_order_documents` (`date`, `type`, `userID`, `orderID`, `amount`, `docID`, `hash`) VALUES
            ('2013-04-26', 1, 2, 15, 998.56, 20001, 'bb4eef5a6d79acb7fab2b9da19b59ce7'),
            ('2013-04-26', 1, 1, 57, 201.86, 20002, '110068dc105c9651c2cd1f202f0c9be1'),
            ('2013-04-26', 2, 2, 15, 998.56, 20001, '15d2f8a284a648576608f1f26a54948c'),
            ('2013-04-26', 2, 1, 57, 201.86, 20002, '9209b7e17b00e02a4be3f4ae17f943c5')";
        Shopware()->Db()->query($sql);

        // disable auth and acl
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
    }

    /**
     * Cleaning up testData
     */
    protected function tearDown()
    {
        parent::tearDown();

       $sql= "DELETE FROM`s_order_documents` WHERE `docID` IN (20001,20002);";
       Shopware()->Db()->query($sql, array());
    }

    /**
     * Checks if the exports contains duplicated rows
     */
    public function testArticleXMLExport()
    {
        $this->Front()->setParam('noViewRenderer', false);
        $this->dispatch('/backend/ImportExport/exportOrders?format=csv');
        $header = $this->Response()->getHeaders();

        $this->assertEquals("Content-Disposition",$header[1]["name"]);
        $this->assertEquals("Content-Transfer-Encoding",$header[2]["name"]);
        $this->assertEquals("binary",$header[2]["value"]);
        $this->assertEquals("text/x-comma-separated-values;charset=utf-8",$header[0]["value"]);
        $csvOutput = $this->Response()->getBody();

        $csvData = explode("\n",$csvOutput);

        foreach ($csvData as $key => $row) {
            if (!empty($csvData[$key - 1]) && !empty($row) && $key - 1  != 0) {
                $result = array_diff(array($csvData[$key - 1]), array($row));
                $this->assertNotEmpty($result);
            }
        }
    }


}
