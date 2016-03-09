<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
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
class Shopware_RegressionTests_Ticket5219 extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * Cleaning up testData
     */
    protected function tearDown()
    {
        parent::tearDown();

        $sql= "UPDATE `s_core_customergroups` SET `taxinput` = '1' WHERE`groupkey` = 'EK'";
        Shopware()->Db()->query($sql);
    }

    /**
     * Checks if the exports contains duplicated rows
     */
    public function testPriceExport()
    {
        $csvData = $this->getExportData();
        $this->assertArticlePrice($csvData, 'SW10003', '14,95');

        $this->Response()->clearBody();

        //change customer group settings
        $sql= "UPDATE `s_core_customergroups` SET `taxinput` = '0' WHERE`groupkey` = 'EK'";
        Shopware()->Db()->query($sql);

        $csvData = $this->getExportData();
        $this->assertArticlePrice($csvData, 'SW10003', '12,56');
    }

    /**
     * helper method to get and verify the export data
     *
     * @return array
     */
    private function getExportData()
    {
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
        $this->Front()->setParam('noViewRenderer', false);
        $this->dispatch('/backend/ImportExport/exportPrices?type=prices&exportVariants=0');
        $header = $this->Response()->getHeaders();

        $this->assertEquals("Content-Disposition", $header[1]["name"]);
        $this->assertEquals("Content-Transfer-Encoding", $header[2]["name"]);
        $this->assertEquals("binary", $header[2]["value"]);
        $this->assertEquals("text/x-comma-separated-values;charset=utf-8", $header[0]["value"]);
        $csvOutput = $this->Response()->getBody();

        return explode("\n", $csvOutput);
    }

    /**
     * helper method to find the article and assert the price
     *
     * @param $csvData
     * @param $orderNumber
     * @param $price
     */
    private function assertArticlePrice($csvData, $orderNumber, $price)
    {
        foreach ($csvData as $row) {
            $columns = explode(";", $row);
            if ($columns[0] == $orderNumber && $columns[2] == "EK") {
                $this->assertEquals($price, $columns[1]);
            }
        }
    }
}
