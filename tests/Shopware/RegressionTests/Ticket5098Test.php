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
 * API Manger
 *
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_RegressionTests_Ticket5098 extends Enlight_Components_Test_Plugin_TestCase
{

    /**
     * Set up test case, fix demo data where needed
     */
    public function setUp() {
        parent::setUp();

        //set Category "Tees und Zubehör" to inactive so the childs should not be displayed
        $sql= "UPDATE `s_categories` SET `active` = '0' WHERE `id` =11";
        Shopware()->Db()->exec($sql);

    }

    /**
     * Cleaning up testData
     */
    protected function tearDown()
    {
        parent::tearDown();

        //set Category "Tees und Zubehör" to inactive so the childs should not be displayed
        $sql= "UPDATE `s_categories` SET `active` = '1' WHERE `id` = 11";
        Shopware()->Db()->exec($sql);
    }

    /**
     * Test Category Structure
     */
    public function testCategoryStructure()
    {
        $allCategories = Shopware()->Modules()->Categories()->sGetWholeCategoryTree(3,3);

        //get "Genusswelten" this category should not have the inactive category "Tees and Zubehör" as subcategory
        $category = $this->getCategoryById($allCategories, 5);
        //search for Tees und Zubehör
        $result = $this->getCategoryById($category["sub"],11);
        $this->assertTrue(empty($result));


        //if the parent category is inactive the child's should not be displayed
        //category = "Genusswelten" the active child "Tees" and "Tees und Zubehör" should not be return because the father ist inactive
        $result = $this->getCategoryById($category["sub"],12);
        $this->assertTrue(empty($result));

        $result = $this->getCategoryById($category["sub"],13);
        $this->assertTrue(empty($result));



        //todo@ms: write test for
//        $result = $this->repository->getActiveChildrenByIdQuery($parentId, $this->customerGroupId, $depth, true)->getArrayResult(); and
//        $result = $this->repository->getActiveChildrenByIdQuery($parentId, $this->customerGroupId, $depth)->getArrayResult(); and

        //todo@ms: write test for sitemap and sitemap xml


    }

    /**
     * Returns a category by the category id
     *
     * @param $allCategories
     * @param $categoryId
     * @return category
     */
    private function getCategoryById($allCategories, $categoryId) {

        foreach ($allCategories as $category) {
            if($category["id"] == $categoryId) {
                return $category;
            }
        }
        return null;
    }


}
