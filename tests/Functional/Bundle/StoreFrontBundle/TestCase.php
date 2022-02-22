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

use Enlight_Components_Test_TestCase;
use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\SearchBundle\ProductNumberSearchInterface;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\SearchBundle\SortingInterface;
use Shopware\Bundle\SearchBundle\VariantSearch;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\Currency;
use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group as CustomerGroupStruct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Article as Product;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Category\Category;
use Shopware\Models\Customer\Group as CustomerGroupModel;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware_Components_Config;

abstract class TestCase extends Enlight_Components_Test_TestCase
{
    use ContainerTrait;

    protected Helper $helper;

    protected Converter $converter;

    protected function setUp(): void
    {
        $this->helper = new Helper($this->getContainer());
        $this->converter = new Converter();
        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->helper->cleanUp();
        parent::tearDown();
    }

    /**
     * @param array<string, array> $products
     *
     * @return Product[]
     */
    public function createProducts(array $products, ShopContext $context, Category $category): array
    {
        $createdProducts = [];
        foreach ($products as $number => $additionally) {
            $createdProducts[$number] = $this->createProduct(
                $number,
                $context,
                $category,
                $additionally
            );
        }

        return $createdProducts;
    }

    public function getEkCustomerGroup(): CustomerGroupStruct
    {
        $customerGroup = $this->getContainer()->get(ModelManager::class)->find(CustomerGroupModel::class, 1);
        static::assertInstanceOf(CustomerGroupModel::class, $customerGroup);

        return $this->converter->convertCustomerGroup($customerGroup);
    }

    /**
     * @param ConditionInterface[] $conditions
     * @param FacetInterface[]     $facets
     * @param SortingInterface[]   $sortings
     */
    protected function search(
        array $products,
        array $expectedNumbers,
        Category $category = null,
        array $conditions = [],
        array $facets = [],
        array $sortings = [],
        TestContext $context = null,
        array $configs = [],
        bool $variantSearch = false
    ): ProductNumberSearchResult {
        if ($context === null) {
            $context = $this->getContext();
        }

        if ($category === null) {
            $category = $this->helper->createCategory();
        }

        $config = $this->getContainer()->get(Shopware_Components_Config::class);
        $originals = [];
        foreach ($configs as $key => $value) {
            $originals[$key] = $config->get($key);
            $config->offsetSet($key, $value);
        }

        $this->createProducts($products, $context, $category);

        $this->helper->refreshSearchIndexes($context->getShop());

        $criteria = new Criteria();

        $this->addCategoryBaseCondition($criteria, $category);

        $this->addConditions($criteria, $conditions);

        $this->addFacets($criteria, $facets);

        $this->addSortings($criteria, $sortings);

        $criteria->offset(0)->limit(4000);

        if ($variantSearch) {
            $search = $this->getContainer()->get(VariantSearch::class);
        } else {
            $search = $this->getContainer()->get(ProductNumberSearchInterface::class);
        }

        $result = $search->search($criteria, $context);

        foreach ($originals as $key => $value) {
            $config->offsetSet($key, $value);
        }

        $this->assertSearchResult($result, $expectedNumbers);

        return $result;
    }

    protected function addCategoryBaseCondition(Criteria $criteria, Category $category): void
    {
        $criteria->addBaseCondition(
            new CategoryCondition([$category->getId()])
        );
    }

    /**
     * @param ConditionInterface[] $conditions
     */
    protected function addConditions(Criteria $criteria, array $conditions): void
    {
        foreach ($conditions as $condition) {
            $criteria->addCondition($condition);
        }
    }

    /**
     * @param FacetInterface[] $facets
     */
    protected function addFacets(Criteria $criteria, array $facets): void
    {
        foreach ($facets as $facet) {
            $criteria->addFacet($facet);
        }
    }

    /**
     * @param SortingInterface[] $sortings
     */
    protected function addSortings(Criteria $criteria, array $sortings): void
    {
        foreach ($sortings as $sorting) {
            $criteria->addSorting($sorting);
        }
    }

    /**
     * @param array<string, mixed>|int $additionally
     */
    protected function createProduct(
        string $number,
        ShopContext $context,
        Category $category,
        $additionally
    ): Product {
        $data = $this->getProduct(
            $number,
            $context,
            $category,
            $additionally
        );

        return $this->helper->createProduct($data);
    }

    protected function assertSearchResult(ProductNumberSearchResult $result, array $expectedNumbers): void
    {
        $numbers = array_map(function (BaseProduct $product) {
            return $product->getNumber();
        }, $result->getProducts());

        foreach ($numbers as $number) {
            static::assertContains($number, $expectedNumbers, sprintf('Product with number: `%s` found but not expected', $number));
        }
        foreach ($expectedNumbers as $number) {
            static::assertContains($number, $numbers, sprintf('Expected product number: `%s` not found', $number));
        }

        static::assertCount(\count($expectedNumbers), $result->getProducts());
        static::assertEquals(\count($expectedNumbers), $result->getTotalCount());
    }

    /**
     * @param array<string> $expectedNumbers
     */
    protected function assertSearchResultSorting(
        ProductNumberSearchResult $result,
        array $expectedNumbers
    ): void {
        foreach (array_values($result->getProducts()) as $index => $product) {
            $expectedProduct = $expectedNumbers[$index];

            static::assertEquals(
                $expectedProduct,
                $product->getNumber(),
                sprintf(
                    'Expected %s at search result position %s, but got product %s',
                    $expectedProduct,
                    $index,
                    $product->getNumber()
                )
            );
        }
    }

    protected function getContext(int $shopId = 1): TestContext
    {
        $tax = $this->helper->createTax();
        $customerGroup = $this->helper->createCustomerGroup();
        $shop = $this->helper->getShop($shopId);

        $context = $this->helper->createContext(
            $customerGroup,
            $shop,
            [$tax]
        );

        if (!$context->getShop()->getCurrency() instanceof Currency) {
            $context->getShop()->setCurrency($context->getCurrency());
        }

        return $context;
    }

    /**
     * @param array<string, mixed>|bool|int|string|Category|Supplier|null $additionally
     *
     * @return array<string, mixed>
     */
    protected function getProduct(
        string $number,
        ShopContext $context,
        Category $category = null,
        $additionally = []
    ): array {
        $taxRules = $context->getTaxRules();
        $product = $this->helper->getSimpleProduct(
            $number,
            array_shift($taxRules),
            $context->getCurrentCustomerGroup()
        );
        $product['categories'] = [['id' => $context->getShop()->getCategory()->getId()]];

        if ($category) {
            $product['categories'] = [
                ['id' => $category->getId()],
            ];
        }

        if (!\is_array($additionally)) {
            $additionally = [];
        }

        return array_merge($product, $additionally);
    }
}
