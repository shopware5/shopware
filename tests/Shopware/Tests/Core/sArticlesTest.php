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
 * Shopware SwagAboCommerce Plugin - Bootstrap
 *
 * @category  Shopware
 * @package   Shopware\Plugins\SwagAboCommerce
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class sArticlesTest extends PHPUnit_Framework_TestCase
{
    protected function assertsArticlesState($sArticles, $categoryId, $translationId, $customerGroupId)
    {
        $this->assertInstanceOf('Shopware\Models\Category\Category', $this->readAttribute($sArticles, 'category'));
        $this->assertEquals($categoryId, $this->readAttribute($sArticles, 'categoryId'));
        $this->assertEquals($translationId, $this->readAttribute($sArticles, 'translationId'));
        $this->assertEquals($customerGroupId, $this->readAttribute($sArticles, 'customerGroupId'));
    }

    public function testCanInstanciatesArticles()
    {
        $sArticles = new sArticles();
        $categoryId = Shopware()->Shop()->getCategory()->getId();
        $translationId = (!Shopware()->Shop()->getDefault() ? Shopware()->Shop()->getId() : null);
        $customerGroupId = ((int) Shopware()->Modules()->System()->sUSERGROUPDATA['id']);

        $this->assertsArticlesState($sArticles, $categoryId, $translationId, $customerGroupId);
    }

    public function testCanInjectParameters()
    {
        $category = new \Shopware\Models\Category\Category();

        $categoryId = 1;
        $translationId = 12;
        $customerGroupId = 23;

        $category->setPrimaryIdentifier($categoryId);
        $sArticles = new sArticles($category, $translationId, $customerGroupId);

        $this->assertsArticlesState($sArticles, $categoryId, $translationId, $customerGroupId);
    }
}

