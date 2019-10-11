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

class sCategoriesTest extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * @var sCategories
     */
    private $module;

    public function setUp(): void
    {
        $this->module = Shopware()->Modules()->Categories();
    }

    /**
     * @covers \sCategories::sGetCategories
     */
    public function testGetCategoriesWithShopCategory()
    {
        $categoryTree = $this->module->sGetCategories(Shopware()->Shop()->get('parentID'));

        $ids = Shopware()->Db()->fetchCol("SELECT id from s_categories WHERE path LIKE '|" . Shopware()->Shop()->get('parentID') . "|'");

        foreach ($categoryTree as $key => $category) {
            static::assertContains($key, $ids);
            static::assertArrayHasKey('subcategories', $category);
            static::assertCount(0, $category['subcategories']);
            static::assertArrayHasKey('id', $category);
            static::assertEquals($key, $category['id']);
            $this->validateCategory($category);
        }
    }

    /**
     * @covers \sCategories::sGetCategories
     */
    public function testGetCategoriesWithSubcategory()
    {
        $categoryTree = $this->module->sGetCategories(13);

        foreach ($categoryTree as $key => $category) {
            static::assertArrayHasKey('id', $category);
            static::assertEquals($key, $category['id']);
            $this->validateCategory($category, 'subcategories');
        }
    }

    /**
     * @covers \sCategories::sGetCategoryIdByArticleId
     */
    public function testsGetCategoryIdByArticleId()
    {
        //first category which assigned to the product 2
        static::assertEquals(14, $this->module->sGetCategoryIdByArticleId(2));

        // Check that searching in default category or with null is the same
        static::assertEquals(
            $this->module->sGetCategoryIdByArticleId(2, Shopware()->Shop()->get('parentID')),
            $this->module->sGetCategoryIdByArticleId(2)
        );

        // Check that searching in different trees gives different results
        static::assertNotEquals(
            $this->module->sGetCategoryIdByArticleId(2, Shopware()->Shop()->get('parentID')),
            $this->module->sGetCategoryIdByArticleId(2, 39)
        );

        // provide own parent id to filter returned category id
        static::assertEquals(
            21,
            $this->module->sGetCategoryIdByArticleId(2, 10)
        );

        // Check that searching for an article where it doesn't exist returns 0
        static::assertEquals(0, $this->module->sGetCategoryIdByArticleId(75, 39));
    }

    /**
     * @covers \sCategories::sGetCategoriesByParent
     */
    public function testsGetCategoriesByParent()
    {
        // Calling on subcategory return path
        $path = $this->module->sGetCategoriesByParent(21);
        static::assertCount(2, $path);
        foreach ($path as $category) {
            static::assertArrayHasKey('id', $category);
            static::assertArrayHasKey('name', $category);
            static::assertArrayHasKey('blog', $category);
            static::assertArrayHasKey('link', $category);
        }

        // Calling on shop category return empty array
        static::assertCount(0, $this->module->sGetCategoriesByParent(Shopware()->Shop()->get('parentID')));

        // Assert root category
        $path = $this->module->sGetCategoriesByParent(1);
        static::assertCount(1, $path);
        foreach ($path as $category) {
            static::assertArrayHasKey('id', $category);
            static::assertArrayHasKey('name', $category);
            static::assertArrayHasKey('blog', $category);
            static::assertArrayHasKey('link', $category);
            static::assertEquals('Root', $category['name']);
            static::assertEquals(1, $category['id']);
        }
    }

    /**
     * @covers \sCategories::sGetWholeCategoryTree
     */
    public function testsGetWholeCategoryTree()
    {
        // Calling on leaf node should return empty array
        static::assertCount(0, $this->module->sGetWholeCategoryTree(21));

        // Default arguments should work
        static::assertEquals(
            $this->module->sGetWholeCategoryTree(),
            $this->module->sGetWholeCategoryTree(Shopware()->Shop()->get('parentID'))
        );

        // Calling on root node should return a complete tree
        $categoryTree = $this->module->sGetWholeCategoryTree(1);
        foreach ($categoryTree as $category) {
            static::assertArrayHasKey('id', $category);
            static::assertArrayHasKey('sub', $category);
            static::assertGreaterThan(0, count($category['sub']));
            $this->validateCategory($category, 'sub');
        }

        // Inactive categories are not loaded
        $inactive = Shopware()->Db()->fetchOne('SELECT parent FROM s_categories WHERE active = 0');
        $inactiveParentCategory = $this->module->sGetWholeCategoryTree($inactive);
        foreach ($inactiveParentCategory as $category) {
            $this->validateCategory($category, 'sub');
            static::assertNotEquals($inactive, $category['id']);
        }

        // Depth argument should work as intended
        $categoryTree = $this->module->sGetWholeCategoryTree(1, 2);
        foreach ($categoryTree as $category) {
            static::assertArrayHasKey('id', $category);
            static::assertArrayHasKey('sub', $category);
            foreach ($category['sub'] as $subcategory) {
                static::assertArrayHasKey('id', $subcategory);
                static::assertArrayNotHasKey('sub', $subcategory);
            }
        }
        $categoryTree = $this->module->sGetWholeCategoryTree(1, 1);
        foreach ($categoryTree as $category) {
            static::assertArrayHasKey('id', $category);
            static::assertArrayNotHasKey('sub', $category);
        }
    }

    /**
     * @covers \sCategories::sGetCategoryContent
     */
    public function testsGetCategoryContent()
    {
        // Call dispatch as we need the Router to be available inside sCore
        $this->dispatch('/');

        // Default arguments should work
        static::assertEquals(
            $this->module->sGetCategoryContent(null),
            $this->module->sGetCategoryContent(Shopware()->Shop()->get('parentID'))
        );

        $categoryArray = $this->module->sGetCategoryContent(21);
        static::assertArrayHasKey('id', $categoryArray);
        static::assertArrayHasKey('parentId', $categoryArray);
        static::assertArrayHasKey('name', $categoryArray);
        static::assertArrayHasKey('position', $categoryArray);
        static::assertArrayHasKey('active', $categoryArray);
        static::assertArrayHasKey('description', $categoryArray);
        static::assertArrayHasKey('template', $categoryArray);
        static::assertArrayHasKey('sSelf', $categoryArray);
        static::assertArrayHasKey('canonicalParams', $categoryArray);
        static::assertArrayHasKey('atomFeed', $categoryArray);
    }

    /**
     * @covers \sCategories::sGetCategoryPath
     */
    public function testsGetCategoryPath()
    {
        // Default arguments should work
        static::assertEquals(
            $this->module->sGetCategoryPath(21),
            $this->module->sGetCategoryPath(21, Shopware()->Shop()->get('parentID'))
        );

        // Looking for elements in root gives full path
        static::assertCount(2, $this->module->sGetCategoryPath(21, 3));

        // Looking for elements in wrong paths returns empty array
        static::assertCount(0, $this->module->sGetCategoryPath(21, 39));
    }

    /**
     * Test the sGetWholeCategoryTree method.
     * This should now only return children when all parents are active
     *
     * @ticket SW-5098
     */
    public function testGetWholeCategoryTree()
    {
        //set Category "Tees und Zubehör" to inactive so the childs should not be displayed
        $sql = "UPDATE `s_categories` SET `active` = '0' WHERE `id` =11";
        Shopware()->Db()->exec($sql);

        $allCategories = $this->module->sGetWholeCategoryTree(3, 3);

        //get "Genusswelten" this category should not have the inactive category "Tees and Zubehör" as subcategory
        $category = $this->getCategoryById($allCategories, 5);
        //search for Tees und Zubehör
        $result = $this->getCategoryById($category['sub'], 11);
        static::assertEmpty($result);

        //if the parent category is inactive the child's should not be displayed
        //category = "Genusswelten" the active child "Tees" and "Tees und Zubehör" should not be return because the father ist inactive
        $result = $this->getCategoryById($category['sub'], 12);
        static::assertEmpty($result);

        $result = $this->getCategoryById($category['sub'], 13);
        static::assertEmpty($result);

        //set Category "Tees und Zubehör" to inactive so the childs should not be displayed
        $sql = "UPDATE `s_categories` SET `active` = '1' WHERE `id` = 11";
        Shopware()->Db()->exec($sql);
    }

    /**
     * Returns a category by the category id
     *
     * @param array $allCategories
     * @param int   $categoryId
     *
     * @return category
     */
    private function getCategoryById($allCategories, $categoryId)
    {
        foreach ($allCategories as $category) {
            if ($category['id'] == $categoryId) {
                return $category;
            }
        }

        return null;
    }

    private function validateCategory($categoryArray, $subcategoriesIndex = null)
    {
        static::assertArrayHasKey('id', $categoryArray);
        static::assertArrayHasKey('name', $categoryArray);
        static::assertArrayHasKey('active', $categoryArray);
        static::assertArrayHasKey('description', $categoryArray);
        static::assertArrayHasKey('link', $categoryArray);
        if ($subcategoriesIndex !== null) {
            static::assertArrayHasKey($subcategoriesIndex, $categoryArray);
            foreach ($categoryArray[$subcategoriesIndex] as $subcategory) {
                $this->validateCategory($subcategory, $subcategoriesIndex);
            }
        }
    }
}
