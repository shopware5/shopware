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
 * Regression Test for Ticket 5098
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
     * Test the sGetWholeCategoryTree method.
     * This should now only return children when all parents are active
     */
    public function testGetWholeCategoryTree()
    {
        $allCategories = Shopware()->Modules()->Categories()->sGetWholeCategoryTree(3,3);

        //get "Genusswelten" this category should not have the inactive category "Tees and Zubehör" as subcategory
        $category = $this->getCategoryById($allCategories, 5);
        //search for Tees und Zubehör
        $result = $this->getCategoryById($category["sub"],11);
        $this->assertEmpty($result);


        //if the parent category is inactive the child's should not be displayed
        //category = "Genusswelten" the active child "Tees" and "Tees und Zubehör" should not be return because the father ist inactive
        $result = $this->getCategoryById($category["sub"],12);
        $this->assertEmpty($result);

        $result = $this->getCategoryById($category["sub"],13);
        $this->assertEmpty($result);

    }

    /**
     * Test if the Query returns depending on the options the right children
     */
    public function testGetActiveChildrenByIdQuery()
    {

        /**
         * @var $repository Shopware\Models\Category\Repository
         */
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Category\Category');

        $categoryArray = $repository->getActiveChildrenTree(3, 1, 3);

        //This category should always been in this structure because it always active
        $this->assertTrue($this->isCategoryNameInArray("Genusswelten",$categoryArray));
        //This category should not be in this array because the category is inactive
        $this->assertFalse($this->isCategoryNameInArray("Tees und Zubehör",$categoryArray));

        //This categories should not be in the array because the option $onlyWithActiveParent is set to true so children will only be returned if the parent is active
        $this->assertFalse($this->isCategoryNameInArray("Tees",$categoryArray));
        $this->assertFalse($this->isCategoryNameInArray("Tee-Zubehör",$categoryArray));

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

    /**
     * Helper method to check if an category name can be found in the given array
     *
     * @param $categoryName
     * @param $categoryArray
     * @return bool
     */
    private function isCategoryNameInArray($categoryName, $categoryArray) {
        foreach ($categoryArray as $category) {
            if($category["name"] == $categoryName) {
                return true;
            }
        }
        return false;
    }


}
