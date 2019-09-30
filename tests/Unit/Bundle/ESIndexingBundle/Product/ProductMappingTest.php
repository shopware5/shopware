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

namespace Shopware\Tests\Unit\Bundle\ESIndexingBundle\Product;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\AttributeBundle\Service\CrudServiceInterface;
use Shopware\Bundle\ESIndexingBundle\FieldMapping;
use Shopware\Bundle\ESIndexingBundle\IdentifierSelector;
use Shopware\Bundle\ESIndexingBundle\Product\ProductMapping;
use Shopware\Bundle\ESIndexingBundle\TextMapping\TextMappingES6;
use Shopware\Bundle\SearchBundle\Facet\VariantFacet;
use Shopware\Bundle\SearchBundleDBAL\VariantHelper;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;

class ProductMappingTest extends TestCase
{
    public function testDynamicIsTrueInDefault(): void
    {
        $identifierSelector = $this->getIdentifierSelector();

        $fieldMapping = $this->getMockBuilder(FieldMapping::class)->disableOriginalConstructor()->getMock();
        $textMapping = new TextMappingES6();
        $crudService = $this->getCrudService();
        $shop = $this->getMockBuilder(Shop::class)->disableOriginalConstructor()->getMock();

        $productMapping = new ProductMapping($identifierSelector, $fieldMapping, $textMapping, $crudService, $this->getVariantHelper());

        $mapping = $productMapping->get($shop);

        static::assertTrue($mapping['dynamic']);
    }

    public function testDynamicIsFalseWhenFalseIsPassed(): void
    {
        $identifierSelector = $this->getIdentifierSelector();

        $fieldMapping = $this->getMockBuilder(FieldMapping::class)->disableOriginalConstructor()->getMock();
        $textMapping = new TextMappingES6();
        $crudService = $this->getCrudService();
        $shop = $this->getMockBuilder(Shop::class)->disableOriginalConstructor()->getMock();

        $productMapping = new ProductMapping(
            $identifierSelector,
            $fieldMapping,
            $textMapping,
            $crudService,
            $this->getVariantHelper(),
            false
        );

        $mapping = $productMapping->get($shop);

        static::assertFalse($mapping['dynamic']);
    }

    public function testDynamicIsTrueWhenTrueIsPassed(): void
    {
        $identifierSelector = $this->getIdentifierSelector();

        $fieldMapping = $this->getMockBuilder(FieldMapping::class)->disableOriginalConstructor()->getMock();
        $textMapping = new TextMappingES6();
        $crudService = $this->getCrudService();
        $shop = $this->getMockBuilder(Shop::class)->disableOriginalConstructor()->getMock();

        $productMapping = new ProductMapping(
            $identifierSelector,
            $fieldMapping,
            $textMapping,
            $crudService,
            $this->getVariantHelper(),
            true
        );

        $mapping = $productMapping->get($shop);

        static::assertTrue($mapping['dynamic']);
    }

    public function testVariantMappingWithOneOption(): void
    {
        $identifierSelector = $this->getIdentifierSelector();

        $fieldMapping = $this->getMockBuilder(FieldMapping::class)->disableOriginalConstructor()->getMock();
        $textMapping = new TextMappingES6();
        $crudService = $this->getCrudService();
        $shop = $this->getMockBuilder(Shop::class)->disableOriginalConstructor()->getMock();

        $productMapping = new ProductMapping(
            $identifierSelector,
            $fieldMapping,
            $textMapping,
            $crudService,
            $this->getVariantHelper([1]),
            true
        );

        $mapping = $productMapping->get($shop);

        static::assertArrayHasKey('g1', $mapping['properties']['visibility']['properties']);
    }

    public function testVariantMappingWithTwoOptions(): void
    {
        $identifierSelector = $this->getIdentifierSelector();

        $fieldMapping = $this->getMockBuilder(FieldMapping::class)->disableOriginalConstructor()->getMock();
        $textMapping = new TextMappingES6();
        $crudService = $this->getCrudService();
        $shop = $this->getMockBuilder(Shop::class)->disableOriginalConstructor()->getMock();

        $productMapping = new ProductMapping(
            $identifierSelector,
            $fieldMapping,
            $textMapping,
            $crudService,
            $this->getVariantHelper([1, 2]),
            true
        );

        $mapping = $productMapping->get($shop);

        static::assertArrayHasKey('g1', $mapping['properties']['visibility']['properties']);
        static::assertArrayHasKey('g2', $mapping['properties']['visibility']['properties']);
        static::assertArrayHasKey('g1-2', $mapping['properties']['visibility']['properties']);
    }

    public function testVariantMappingWithThreeOptions(): void
    {
        $identifierSelector = $this->getIdentifierSelector();

        $fieldMapping = $this->getMockBuilder(FieldMapping::class)->disableOriginalConstructor()->getMock();
        $textMapping = new TextMappingES6();
        $crudService = $this->getCrudService();
        $shop = $this->getMockBuilder(Shop::class)->disableOriginalConstructor()->getMock();

        $productMapping = new ProductMapping(
            $identifierSelector,
            $fieldMapping,
            $textMapping,
            $crudService,
            $this->getVariantHelper([1, 2, 3]),
            true
        );

        $mapping = $productMapping->get($shop);

        static::assertArrayHasKey('g1', $mapping['properties']['visibility']['properties']);
        static::assertArrayHasKey('g2', $mapping['properties']['visibility']['properties']);
        static::assertArrayHasKey('g3', $mapping['properties']['visibility']['properties']);
        static::assertArrayHasKey('g1-2', $mapping['properties']['visibility']['properties']);
        static::assertArrayHasKey('g1-3', $mapping['properties']['visibility']['properties']);
        static::assertArrayHasKey('g2-3', $mapping['properties']['visibility']['properties']);
    }

    public function getIdentifierSelector(): IdentifierSelector
    {
        $identifierSelector = $this->createMock(IdentifierSelector::class);

        $identifierSelector->method('getCustomerGroupKeys')->willReturn(['EK']);
        $identifierSelector->method('getShopCurrencyIds')->willReturn([1]);

        return $identifierSelector;
    }

    public function getCrudService(): CrudServiceInterface
    {
        $crudService = $this->createMock(CrudService::class);

        $crudService->method('getList')->willReturn([]);

        return $crudService;
    }

    public function getVariantHelper(array $options = []): VariantHelper
    {
        $variantHelper = $this->createMock(VariantHelper::class);
        $variantHelper->method('getVariantFacet')->willReturn(new VariantFacet($options));

        return $variantHelper;
    }
}
