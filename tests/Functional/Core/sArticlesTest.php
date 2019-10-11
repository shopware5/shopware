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

use Shopware\Tests\Functional\Helper\Utils;

class sArticlesTest extends Enlight_Components_Test_Controller_TestCase
{
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

    /**
     * Checks if price group is taken into account correctly
     *
     * @ticket SW-4887
     */
    public function testPriceGroupForMainVariant()
    {
        // Add price group
        $sql = '
        UPDATE s_articles SET pricegroupActive = 1 WHERE id = 2;
        INSERT INTO s_core_pricegroups_discounts (`groupID`, `customergroupID`, `discount`, `discountstart`) VALUES (1, 1, 5, 1);
        ';

        Shopware()->Db()->query($sql);

        $this->dispatch('/');

        Shopware()->Container()->get('shopware_storefront.context_service')->initializeShopContext();

        $correctPrice = '18,99';
        $article = Shopware()->Modules()->Articles()->sGetArticleById(
            2
        );
        static::assertEquals($correctPrice, $article['price']);

        // delete price group
        $sql = '
        UPDATE s_articles SET pricegroupActive = 0 WHERE id = 2;
        DELETE FROM s_core_pricegroups_discounts WHERE `customergroupID` = 1 AND `discount` = 5;
        ';
        Shopware()->Db()->query($sql);
    }

    /**
     * @ticket SW-5391
     */
    public function testsGetPromotionByIdWithNonExistingArticle()
    {
        $result = Shopware()->Modules()->Articles()->sGetPromotionById('fix', 0, 9999999);

        // a query to a not existing article should return 'false' and not throw an exception
        static::assertFalse($result);
    }

    /**
     * Assert that numbers that are small enough to be represented as floating points by PHP when they are converted to
     * string are rounded correctly by sArticles::sRound.
     */
    public function testsRoundWithFloatingPointRepresentation()
    {
        $sArticles = new sArticles();
        $input = 0.00001; // 1.0E-5

        // Round 1.0E-5
        $result = $sArticles->sRound($input);

        static::assertEquals(0, $result);
    }

    /**
     * Assert that negative numbers that are small enough to be represented as floating points by PHP when they are
     * converted to string are rounded correctly by sArticles::sRound. Also assert that they are not represented as -0
     * (negative zero)
     */
    public function testsRoundWithFloatingPointRepresentationNegative()
    {
        $sArticles = new sArticles();
        $input = -0.00001; // -1.0E-5

        // Round -1.0E-5
        $result = $sArticles->sRound($input);

        static::assertEquals(0, $result);
        // Make sure we don't get negative zero
        static::assertEquals('0', (string) $result);
    }

    /**
     * Assert that numbers that are small enough to be represented as floating points by PHP when they are converted to
     * string are rounded correctly by sArticles::sRound.
     */
    public function testsRoundWithFloatingPointRepresentationLarge()
    {
        $sArticles = new sArticles();
        $input = 100000000000000.0; // 1.0E14

        // Round 1.0E14
        $result = $sArticles->sRound($input);

        static::assertEquals(100000000000000, $result);
    }

    protected function assertsArticlesState($sArticles, $categoryId, $translationId, $customerGroupId)
    {
        static::assertInstanceOf('Shopware\Models\Category\Category', Utils::hijackAndReadProperty($sArticles, 'category'));
        static::assertEquals($categoryId, Utils::hijackAndReadProperty($sArticles, 'categoryId'));
        static::assertEquals($translationId, Utils::hijackAndReadProperty($sArticles, 'translationId'));
        static::assertEquals($customerGroupId, Utils::hijackAndReadProperty($sArticles, 'customerGroupId'));
    }
}
