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
class Shopware_Tests_Controllers_Frontend_ListingTest extends Enlight_Components_Test_Controller_TestCase
{

    protected $category;

    public function testCategoryDetailLink()
    {
        $default = Shopware()->Config()->categoryDetailLink;
        Shopware()->Config()->categoryDetailLink = true;

        $this->removeDemoData();
        $this->insertDemoData();

        $this->Request()
            ->setMethod('POST');

        $this->dispatch('/cat/index/sCategory/80');
        $this->assertTrue($this->Response()->isRedirect());

        $this->removeDemoData();
        Shopware()->Config()->categoryDetailLink = $default;
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
            'description' => 'ListingTest',
            'active' => '1'
        );
        Shopware()->Db()->insert('s_categories', $category);

        $this->category = $this->getDemoCategory();

        $categoryArticles = array(
            array('articleID' => '3','categoryID' => '3','parentCategoryID' => $this->category['id']),
            array('articleID' => '3','categoryID' => $this->category['id'],'parentCategoryID' => $this->category['id'])
        );

        foreach($categoryArticles as $article) {
            Shopware()->Db()->insert('s_articles_categories_ro', $article);
        }

        Shopware()->Db()->insert('s_articles_categories', array('articleID' => '3','categoryID' => $this->category['id']));
    }


    /**
     * Helper function to clean up the test case demo data.
     */
    protected function removeDemoData()
    {
        $category = $this->getDemoCategory();

        $sql = "DELETE FROM s_categories WHERE description = 'ListingTest'";
        Shopware()->Db()->query($sql);

        if (!empty($category)) {
            $sql = "DELETE FROM s_articles_categories_ro WHERE parentCategoryID = ?";
            Shopware()->Db()->query($sql, array($category['id']));
        }
    }

    /**
     * Helper function to get the test case demo category.
     * @return array
     */
    protected function getDemoCategory()
    {
        return Shopware()->Db()->fetchRow("SELECT * FROM s_categories WHERE description = 'ListingTest' LIMIT 1");
    }

}
