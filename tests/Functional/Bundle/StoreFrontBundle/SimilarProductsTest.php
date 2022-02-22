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

namespace Shopware\Tests\Functional\Bundle\StoreFrontBundle;

use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\SimilarProductsServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Article\Article;
use Shopware\Models\Category\Category;

/**
 * @group elasticSearch
 */
class SimilarProductsTest extends TestCase
{
    /**
     * setting up test config
     */
    public static function setUpBeforeClass(): void
    {
        Shopware()->Config()->offsetSet('similarlimit', 3);
    }

    /**
     * Cleaning up test config
     */
    public static function tearDownAfterClass(): void
    {
        Shopware()->Config()->offsetSet('similarlimit', 0);
    }

    public function testSimilarProduct(): void
    {
        $context = $this->getContext();

        $number = 'testSimilarProduct';
        $article = $this->getProductObject($number, $context);

        $similarNumbers = [];
        $similarProducts = [];
        for ($i = 0; $i < 4; ++$i) {
            $similarNumber = 'SimilarProduct-' . $i;
            $similarNumbers[] = $similarNumber;
            $similarProduct = $this->getProductObject($similarNumber, $context);
            $similarProducts[] = $similarProduct->getId();
        }
        $this->linkSimilarProduct($article->getId(), $similarProducts);

        $product = $this->getContainer()->get(ListProductServiceInterface::class)->get($number, $context);
        static::assertNotNull($product);
        $similarProducts = $this->getContainer()->get(SimilarProductsServiceInterface::class)->get($product, $context);
        static::assertIsArray($similarProducts);

        static::assertCount(4, $similarProducts);

        foreach ($similarProducts as $similarProduct) {
            static::assertInstanceOf(ListProduct::class, $similarProduct);
            static::assertContains($similarProduct->getNumber(), $similarNumbers);
        }
    }

    public function testSimilarProductsList(): void
    {
        $context = $this->getContext();

        $number = 'testSimilarProductsList';
        $number2 = 'testSimilarProductsList2';

        $article = $this->getProductObject($number, $context);
        $article2 = $this->getProductObject($number2, $context);

        $similarNumbers = [];
        $similarProducts = [];
        for ($i = 0; $i < 4; ++$i) {
            $similarNumber = 'SimilarProduct-' . $i;
            $similarNumbers[] = $similarNumber;
            $similarProduct = $this->getProductObject($similarNumber, $context);
            $similarProducts[] = $similarProduct->getId();
        }

        $this->linkSimilarProduct($article->getId(), $similarProducts);
        $this->linkSimilarProduct($article2->getId(), $similarProducts);

        $products = $this->getContainer()->get(ListProductServiceInterface::class)
            ->getList([$number, $number2], $context);

        $similarProductList = $this->getContainer()->get(SimilarProductsServiceInterface::class)
            ->getList($products, $context);

        static::assertCount(2, $similarProductList);

        foreach ($products as $product) {
            $similarProducts = $similarProductList[$product->getNumber()];

            static::assertCount(4, $similarProducts);

            foreach ($similarProducts as $similarProduct) {
                static::assertInstanceOf(ListProduct::class, $similarProduct);
                static::assertContains($similarProduct->getNumber(), $similarNumbers);
            }
        }
    }

    public function testSimilarProductsByCategory(): void
    {
        $number = __FUNCTION__;
        $context = $this->getContext();
        $category = $this->helper->createCategory();

        $this->getProductObject($number, $context, $category);

        for ($i = 0; $i < 4; ++$i) {
            $similarNumber = 'SimilarProduct-' . $i;
            $this->getProductObject($similarNumber, $context, $category);
        }

        $helper = new Helper($this->getContainer());
        $convertedShop = (new Converter())->convertShop($helper->getShop());
        if (!$convertedShop->getCurrency()) {
            $convertedShop->setCurrency($context->getCurrency());
        }

        $helper->refreshSearchIndexes(
            $convertedShop
        );

        $product = $this->getContainer()->get(ListProductServiceInterface::class)->get($number, $context);
        static::assertNotNull($product);
        $similar = $this->getContainer()->get(SimilarProductsServiceInterface::class)->get($product, $context);
        static::assertIsArray($similar);

        static::assertCount(3, $similar);

        foreach ($similar as $similarProduct) {
            static::assertInstanceOf(
                ListProduct::class,
                $similarProduct
            );
        }
    }

    private function getProductObject(
        string $number,
        ShopContext $context,
        Category $category = null
    ): Article {
        $data = $this->getProduct($number, $context, $category);

        return $this->helper->createProduct($data);
    }

    /**
     * @param int[] $similarProductIds
     */
    private function linkSimilarProduct(int $productId, array $similarProductIds): void
    {
        foreach ($similarProductIds as $similarProductId) {
            Shopware()->Db()->insert('s_articles_similar', [
                'articleID' => $productId,
                'relatedarticle' => $similarProductId,
            ]);
        }
    }
}
