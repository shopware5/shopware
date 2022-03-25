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

namespace Shopware\Tests\Functional\Bundle\SearchBundleEs\FacetHandler;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;
use Shopware\Bundle\SearchBundle\Facet\ProductAttributeFacet;
use Shopware\Bundle\SearchBundle\FacetResult\ValueListFacetResult;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\SearchBundleES\FacetHandler\ProductAttributeFacetHandler;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Tests\Functional\Bundle\SearchBundleEs\FacetHandler\_fixtures\ElasticResult;
use Shopware\Tests\Functional\Traits\ContainerTrait;

/**
 * @group elasticSearch
 */
class ProductAttributeFacetHandlerTest extends TestCase
{
    use ContainerTrait;

    public function testHydrate(): void
    {
        $elasticResult = (new ElasticResult())->getResult();
        $productNumberSearchResult = new ProductNumberSearchResult([], 0, [], []);
        $criteria = new Criteria();
        $context = $this->getShopContext();

        $productAttributeFacetHandler = $this->getContainer()->get(ProductAttributeFacetHandler::class);

        $reflectionClass = new ReflectionClass(ProductAttributeFacetHandler::class);
        $reflectionProperty = $reflectionClass->getProperty('criteriaParts');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($productAttributeFacetHandler, [$this->getProductAttributeFacet()]);

        $productAttributeFacetHandler->hydrate(
            $elasticResult,
            $productNumberSearchResult,
            $criteria,
            $context
        );

        static::assertCount(1, $productNumberSearchResult->getFacets());
        static::assertInstanceOf(ValueListFacetResult::class, $productNumberSearchResult->getFacets()[0]);
        static::assertSame('product_attribute_attr4', $productNumberSearchResult->getFacets()[0]->getFacetName());
    }

    public function testHydrateWithEmptyBuckets(): void
    {
        $elasticResult = (new ElasticResult())->getResult();
        $elasticResult['aggregations']['product_attribute_attr4']['buckets'] = [];
        $elasticResult['aggregations']['properties']['buckets'] = [];

        $productNumberSearchResult = new ProductNumberSearchResult([], 0, [], []);
        $criteria = new Criteria();
        $context = $this->getShopContext();

        $productAttributeFacetHandler = $this->getContainer()->get(ProductAttributeFacetHandler::class);

        $reflectionClass = new ReflectionClass(ProductAttributeFacetHandler::class);
        $reflectionProperty = $reflectionClass->getProperty('criteriaParts');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($productAttributeFacetHandler, [$this->getProductAttributeFacet()]);

        $productAttributeFacetHandler->hydrate(
            $elasticResult,
            $productNumberSearchResult,
            $criteria,
            $context
        );

        static::assertCount(0, $productNumberSearchResult->getFacets());
    }

    private function getShopContext(): ShopContextInterface
    {
        return $this->getContainer()->get('shopware_storefront.context_service')
            ->createShopContext(1, 1, 'EK');
    }

    private function getProductAttributeFacet(): CriteriaPartInterface
    {
        return new ProductAttributeFacet(
            'attr4',
            'value_list',
            'attrib',
            'Attribute',
            'template',
            'suffix',
            2
        );
    }
}
