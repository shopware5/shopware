<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Modules\Articles;

use Enlight_Components_Test_Plugin_TestCase;

/**
 * tests the base price calculation
 *
 * @ticket SW-7204
 */
class BasePriceCalculationTest extends Enlight_Components_Test_Plugin_TestCase
{
    /**
     * Set up test case, fix demo data where needed
     */
    public function setUp(): void
    {
        parent::setUp();

        $sql = "UPDATE `s_articles_details` SET `kind` = '1' WHERE `ordernumber` = 'SW10002.1'";
        Shopware()->Db()->query($sql);

        $sql = "UPDATE `s_articles_details` SET `kind` = '2' WHERE `ordernumber` = 'SW10002.3'";
        Shopware()->Db()->query($sql);
    }

    /**
     * Cleaning up testData
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        // Restore old main detail
        $sql = "UPDATE `s_articles_details` SET `kind` = '2' WHERE `ordernumber` = 'SW10002.1'";
        Shopware()->Db()->query($sql);

        $sql = "UPDATE `s_articles_details` SET `kind` = '1' WHERE `ordernumber` = 'SW10002.3'";
        Shopware()->Db()->query($sql);
    }

    /**
     * test just to calculate the right reference price
     */
    public function testCalculateReferencePrice(): void
    {
        $testData = [
            ['price' => 19.0, 'purchaseUnit' => 0.7, 'referenceUnit' => 1],
            ['price' => '199,99', 'purchaseUnit' => 0.7, 'referenceUnit' => 3],
            ['price' => 19999.99, 'purchaseUnit' => '0.999', 'referenceUnit' => '1.9'],
            ['price' => '19999,89', 'purchaseUnit' => 0.999, 'referenceUnit' => 1],
            ['price' => '0,139', 'purchaseUnit' => 99, 'referenceUnit' => 1],
        ];
        $expectedData = [
            27.142857142857,
            857.1,
            38038.019019019,
            20019.90990991,
            0.0014040404040404,
        ];
        foreach ($testData as $key => $data) {
            $referencePrice = Shopware()->Modules()->Articles()->calculateReferencePrice(
                $data['price'],
                $data['purchaseUnit'],
                $data['referenceUnit']
            );
            static::assertSame($expectedData[$key], $referencePrice);
        }
    }

    /**
     * set the main variant to a bigger prices variant and check if the base price data of the main article is returned
     */
    public function testsGetArticleById(): void
    {
        $this->dispatch('/');
        $articleDetailData = Shopware()->Modules()->Articles()->sGetArticleById(2);
        static::assertSame(39.98, $articleDetailData['referenceprice']);
    }

    /**
     * test the right base price result of the sGetPromotionById
     */
    public function testsGetPromotionById(): void
    {
        $this->dispatch('/');
        $productData = Shopware()->Modules()->Articles()->sGetPromotionById('fix', 0, 2);
        static::assertIsArray($productData);
        static::assertSame(1.0, $productData['referenceunit']);
        static::assertSame(0.5, $productData['purchaseunit']);
        static::assertSame('39,98', $productData['referenceprice']);

        $this->dispatch('/');
        $productData = Shopware()->Modules()->Articles()->sGetPromotionById('fix', 0, 5);
        static::assertIsArray($productData);
        static::assertSame(0.7, $productData['purchaseunit']);
        static::assertSame(1.0, $productData['referenceunit']);
        static::assertSame('15,64', $productData['referenceprice']);
    }

    /**
     * test the right base price result of the sGetProductByOrderNumber
     */
    public function testsGetProductByOrderNumber(): void
    {
        $this->dispatch('/');
        $productData = Shopware()->Modules()->Articles()->sGetProductByOrdernumber('SW10002.2');
        static::assertIsArray($productData);
        static::assertSame(0.5, $productData['purchaseunit']);
        static::assertSame(1.0, $productData['referenceunit']);
        static::assertSame('39,98', $productData['referenceprice']);

        $productData = Shopware()->Modules()->Articles()->sGetProductByOrdernumber('SW10003');
        static::assertIsArray($productData);
        static::assertSame(0.7, $productData['purchaseunit']);
        static::assertSame(1.0, $productData['referenceunit']);
        static::assertSame('21,36', $productData['referenceprice']);
    }
}
