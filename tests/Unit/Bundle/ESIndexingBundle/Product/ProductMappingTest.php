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

namespace Shopware\Tests\Unit\Bundle\ESIndexingBundle\Property;

use Elasticsearch\Client;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\ESIndexingBundle\FieldMapping;
use Shopware\Bundle\ESIndexingBundle\IdentifierSelector;
use Shopware\Bundle\ESIndexingBundle\Product\ProductMapping;
use Shopware\Bundle\ESIndexingBundle\TextMapping\TextMappingES5;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;

class ProductMappingTest extends TestCase
{
    public function testDynamicIsTrueInDefault()
    {
        $identifierSelector = $this->getIdentifierSelector();

        $fieldMapping = $this->getMockBuilder(FieldMapping::class)->disableOriginalConstructor()->getMock();
        $textMapping = new TextMappingES5();
        $crudService = $this->getCrudService();
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        $shop = $this->getMockBuilder(Shop::class)->disableOriginalConstructor()->getMock();

        $productMapping = new ProductMapping($identifierSelector, $fieldMapping, $textMapping, $crudService, $client);

        $mapping = $productMapping->get($shop);

        static::assertTrue($mapping['dynamic']);
    }

    public function testDynamicIsFalseWhenFalseIsPassed()
    {
        $identifierSelector = $this->getIdentifierSelector();

        $fieldMapping = $this->getMockBuilder(FieldMapping::class)->disableOriginalConstructor()->getMock();
        $textMapping = new TextMappingES5();
        $crudService = $this->getCrudService();
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        $shop = $this->getMockBuilder(Shop::class)->disableOriginalConstructor()->getMock();

        $productMapping = new ProductMapping(
            $identifierSelector,
            $fieldMapping,
            $textMapping,
            $crudService,
            $client,
            false
        );

        $mapping = $productMapping->get($shop);

        static::assertFalse($mapping['dynamic']);
    }

    public function testDynamicIsTrueWhenTrueIsPassed()
    {
        $identifierSelector = $this->getIdentifierSelector();

        $fieldMapping = $this->getMockBuilder(FieldMapping::class)->disableOriginalConstructor()->getMock();
        $textMapping = new TextMappingES5();
        $crudService = $this->getCrudService();
        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        $shop = $this->getMockBuilder(Shop::class)->disableOriginalConstructor()->getMock();

        $productMapping = new ProductMapping(
            $identifierSelector,
            $fieldMapping,
            $textMapping,
            $crudService, $client,
            true
        );

        $mapping = $productMapping->get($shop);

        static::assertTrue($mapping['dynamic']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getIdentifierSelector()
    {
        $identifierSelector = $this->createMock(IdentifierSelector::class);

        $identifierSelector->method('getCustomerGroupKeys')->willReturn(['EK']);
        $identifierSelector->method('getShopCurrencyIds')->willReturn([1]);

        return $identifierSelector;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getCrudService()
    {
        $crudService = $this->createMock(CrudService::class);

        $crudService->method('getList')->willReturn([]);

        return $crudService;
    }
}
