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
class Shopware_RegressionTests_Ticket4508 extends Enlight_Components_Test_Plugin_TestCase
{

    /**
     * List of details changed during the test
     * will be cleaned up later
     * @var array
     */
    protected $changedDetails = array();

    /**
        * Set up test case, fix demo data where needed
        */
    public function setUp() {
        parent::setUp();

        // fix broken variant basePrice articles
        $sql = "
            UPDATE `s_articles_details` SET unitID = 1,
            purchaseunit =  REPLACE(REPLACE(`additionaltext`, ',', '.'), ' Liter', '')
            WHERE articleID IN  (7, 122, 2, 5);

            DELETE FROM s_articles_attributes WHERE articleID IS NULL;

            INSERT IGNORE INTO s_articles_attributes (`articleID`, `articledetailsID`) VALUES
            (2, 123), (2, 124), (2, 125),
            (5, 252), (5, 253), (5, 254), (5, 255),
            (7,249), (7,250), (7, 251),
            (122, 256), (122, 257), (122, 258), (122, 259);
        ";

        Shopware()->Db()->query($sql);

    }

    /**
     * Cleaning up testData
     */
    protected function tearDown()
    {
        parent::tearDown();


        // Restore old main detail
        if (!empty($this->changedDetails)) {
            $this->_restoreOldMainDetails();
        }
    }

    /**
     * Test  "from..." prices
     */
    public function testFromPrices()
    {
        $categories = array(
            23 => array(
                // base price calculation
                206 => '50,00',
                // block prices
                209 => '0,70'
            ),
            22 => array(
                // Variant price surcharge
                205 => '119,00'     // Will be fixed in future => SW-5094
            ),
            21 => array(
                // base price calculation
                7 => '2,49',
                122 => '2,99',
                4 => '7,99',
                12 => '9,99',
                5 => '10,95',
                9 => '24,99',
                6 => '35,95',
                8 => '49,95'
            )
        );

        foreach ($categories as $categoryId => $articles) {
            $results = Shopware()->Modules()->Articles()->sGetArticlesByCategory($categoryId);
            foreach ($articles as $articleId => $price) {
                $this->assertEquals($price, $results['sArticles'][$articleId]['price']);
            }
        }
    }

    /**
     * Test base price calculation
     */
    public function testBasePrices()
    {
        $categories = array(
            23 => array(
                206 => '100.0'
            ),
            21 => array(
                9 => '49.98',
                2 => '39.98',
                3 => '21.357142857143',
                12 => '14.271428571429',
                122 => '14.95',
                7 => '12.45',
                8 => '99.90',
                4 => '11.414285714286',
                5 => '54.75'
            )
        );

        foreach ($categories as $categoryId => $articles) {
            $results = Shopware()->Modules()->Articles()->sGetArticlesByCategory($categoryId);
            foreach ($articles as $articleId => $price) {
                $this->assertEquals($price, $results['sArticles'][$articleId]['referenceprice']);
            }
        }
    }

    /**
     * Check base price calculation fro main variants
     */
    public function testBasePricesWithChangedMainVariant()
    {
        $newMainDetails = array(
            122 => 259,
            7 => 250,
            5 =>  255,
            2 => 124
        );
        foreach ($newMainDetails as $articleId => $detailId) {
            $this->_setMainDetail($articleId, $detailId);
        }


        $categories = array(
            21 => array(
                // base price calculation
                7 => '12.45',
                122 => '14.95',
                5 => '54.75',
                2 => '39.98'

            )
        );

        foreach ($categories as $categoryId => $articles) {
            $results = Shopware()->Modules()->Articles()->sGetArticlesByCategory($categoryId);
            foreach ($articles as $articleId => $price) {
                $this->assertEquals($price, $results['sArticles'][$articleId]['referenceprice']);
            }
        }

    }

    /**
     * Helper function which restores the old main details if main details where changed
     */
    private function _restoreOldMainDetails()
    {
        $articleDetailIds = implode(", ", array_values($this->changedDetails));
        $articleIds = implode(", ", array_keys($this->changedDetails));

        $sql = "
        UPDATE s_articles_details SET kind = 2 WHERE articleID IN ({$articleIds});
        UPDATE s_articles_details SET kind = 1 WHERE id IN ({$articleDetailIds});
        ";

        foreach ($this->changedDetails as $articleId => $detailId) {
            $sql .= "UPDATE s_articles SET main_detail_id = {$detailId} WHERE id = {$articleId};";
        }

        Shopware()->Db()->query($sql);

    }

    /**
     * Helper function which sets a main (new) detail for a given article
     * @param $articleId
     * @param $detailId
     */
    private function _setMainDetail($articleId, $detailId)
    {
        $oldMainDetail = Shopware()->Db()->fetchOne("SELECT main_detail_id FROM s_articles WHERE id = ?", array($articleId));

        if ($oldMainDetail === $detailId) {
            return;
        }

        $sql = "
        UPDATE s_articles SET main_detail_id = :detailId WHERE id = :articleId;
        UPDATE s_articles_details SET kind = 2 WHERE articleID = :articleId;
        UPDATE s_articles_details SET kind = 1 WHERE id = :detailId;
        ";

        Shopware()->Db()->query($sql, array("articleId" =>$articleId, "detailId" => $detailId));

        $this->changedDetails[$articleId] = $oldMainDetail;

    }

}
