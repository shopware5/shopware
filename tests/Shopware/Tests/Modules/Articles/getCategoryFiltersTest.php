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
class Shopware_Tests_Modules_Articles_getCategoryFiltersTest extends Enlight_Components_Test_Plugin_TestCase
{

    /**
     * Module instance
     *
     * @var sArticles
     */
    protected $module;

    /**
     * Test set up method
     */
    public function setUp()
    {
        parent::setUp();
        $this->module = Shopware()->Modules()->Articles();
    }

    protected $properties = array('optionName', 'optionValue', 'groupName', 'articleCount');

    protected function getCategories()
    {
        return require __DIR__ . '/fixtures/category_filters.php';
    }

    public function testCategoryFilters()
    {
        foreach($this->getCategories() as $expected) {
            $result = $this->module->getCategoryFilters($expected['categoryId'], $expected['activeFilters']);
            $this->checkCategoryFilterResult($expected['result'], $result);
        }
    }

    protected function checkCategoryFilterResult($expected, $result)
    {
        $this->assertCount(count($expected), $result);
        foreach($expected as $key => $currentExpected) {
            $currentResult = $result[$key];
            foreach($this->properties as $property) {
                $this->assertEquals($currentExpected[$property], $currentResult[$property]);
            }
        }
    }
}