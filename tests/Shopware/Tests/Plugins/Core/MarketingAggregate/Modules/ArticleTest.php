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
class Shopware_Tests_Plugins_Core_MarketingAggregate_Modules_ArticleTest extends Shopware_Tests_Plugins_Core_MarketingAggregate_AbstractMarketing
{

    /**
     * @var sArticles
     */
    protected $module;

    protected $category = null;

    /**
     * Set up function for this test case.
     */
    public function setUp()
    {
        parent::setUp();
        $this->module = Shopware()->Modules()->Articles();
    }


    /**
     * Helper function to initial the test case
     * demo data.
     */
    protected function insertDemoData()
    {
        $category = array(
            'parent' => '3',
            'path' => '|3|',
            'description' => 'TopSellerTest',
            'active' => '1'
        );
        $this->Db()->insert('s_categories', $category);

        $this->category = $this->getDemoCategory();

        $categoryArticles = array(
            array('articleID' => '3','categoryID' => '3','parentCategoryID' => $this->category['id']),
            array('articleID' => '3','categoryID' => $this->category['id'],'parentCategoryID' => $this->category['id']),
            array('articleID' => '2','categoryID' => '3','parentCategoryID' => $this->category['id']),
            array('articleID' => '2','categoryID' => $this->category['id'],'parentCategoryID' => $this->category['id'])
        );

        $sql = "UPDATE s_articles SET pseudosales = 1000 WHERE id = 2";
        $this->Db()->query($sql);

        $sql = "UPDATE s_articles SET pseudosales = 500 WHERE id = 3";
        $this->Db()->query($sql);

        foreach($categoryArticles as $article) {
            $this->Db()->insert('s_articles_categories_ro', $article);
        }


        $this->TopSeller()->refreshTopSellerForArticleId(2);
        $this->TopSeller()->refreshTopSellerForArticleId(3);
    }

    /**
     * Helper function to clean up the test case demo data.
     */
    protected function removeDemoData()
    {
        $category = $this->getDemoCategory();

        $sql = "DELETE FROM s_categories WHERE description = 'TopSellerTest'";
        $this->Db()->query($sql);

        if (!empty($category)) {
            $sql = "DELETE FROM s_articles_categories_ro WHERE parentCategoryID = ?";
            $this->Db()->query($sql, array($category['id']));
        }

        $sql = "UPDATE s_articles SET pseudosales = 0 WHERE id in (2, 3)";
        $this->Db()->query($sql);
    }

    /**
     * Helper function to get the test case demo category.
     * @return array
     */
    protected function getDemoCategory()
    {
        return $this->Db()->fetchRow("SELECT * FROM s_categories WHERE description = 'TopSellerTest' LIMIT 1");
    }

    /**
     * Test case for the frontend top seller selection
     */
    public function testTopSellerSelection()
    {
        $this->removeDemoData();
        $this->insertDemoData();

        $category = $this->getDemoCategory();

        $topSeller = $this->module->sGetArticleCharts($category['id']);
        $this->assertCount(2, $topSeller);

        //the article "2" pseudo sales are set to 1000 so we expect that this will be the first article
        //in the top seller slider.
        $this->assertEquals(2, $topSeller[0]['articleID']);
        $this->assertEquals(3, $topSeller[1]['articleID']);

        $this->removeDemoData();
    }
}
