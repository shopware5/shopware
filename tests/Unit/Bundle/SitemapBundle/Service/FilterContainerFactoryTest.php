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

namespace Shopware\Tests\Unit\Bundle\SitemapBundle\Service;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\SitemapBundle\Service\ConfigHandler;
use Shopware\Bundle\SitemapBundle\Service\FilterContainerFactory;

class FilterContainerFactoryTest extends TestCase
{
    public function testBuildFilterContainerReturnsFiltersForResourceFoo()
    {
        $configHandlerStub = $this->createMock(ConfigHandler::class);
        $configHandlerStub->method('get')
            ->with(ConfigHandler::EXCLUDED_URLS_KEY)
            ->willReturn([
                [
                    'resource' => 'foo',
                    'identifier' => '1',
                    'shopId' => '0',
                ], [
                    'resource' => 'foo',
                    'identifier' => '2',
                    'shopId' => '0',
                ],
            ]);

        $filterContainerFactory = $this->getFilterContainerFactory($configHandlerStub);

        $filterContainer = $filterContainerFactory->buildFilterContainer('foo', 1);

        static::assertEquals('foo', $filterContainer->getResourceName());
        static::assertEquals([1, 2], $filterContainer->getFilters());
    }

    public function testBuildFilterContainerReturnsNoFiltersWrongShop()
    {
        $configHandlerStub = $this->createMock(ConfigHandler::class);
        $configHandlerStub->method('get')
            ->with(ConfigHandler::EXCLUDED_URLS_KEY)
            ->willReturn([
                [
                    'resource' => 'foo',
                    'identifier' => '1',
                    'shopId' => '2',
                ], [
                    'resource' => 'foo',
                    'identifier' => '2',
                    'shopId' => '2',
                ],
            ]);

        $filterContainerFactory = $this->getFilterContainerFactory($configHandlerStub);

        $filterContainer = $filterContainerFactory->buildFilterContainer('foo', 1);

        static::assertCount(0, $filterContainer->getFilters());
    }

    public function testBuildFilterContainerReturnsFiltersForRightShopId()
    {
        $configHandlerStub = $this->createMock(ConfigHandler::class);
        $configHandlerStub->method('get')
            ->with(ConfigHandler::EXCLUDED_URLS_KEY)
            ->willReturn([
                [
                    'resource' => 'foo',
                    'identifier' => '1',
                    'shopId' => '1',
                ], [
                    'resource' => 'foo',
                    'identifier' => '2',
                    'shopId' => '1',
                ],
            ]);

        $filterContainerFactory = $this->getFilterContainerFactory($configHandlerStub);

        $filterContainer = $filterContainerFactory->buildFilterContainer('foo', 1);

        static::assertCount(2, $filterContainer->getFilters());
    }

    public function testBuildFilterContainerReturnsMultipleFiltersForRightShopIdAndShopIdZero()
    {
        $configHandlerStub = $this->createMock(ConfigHandler::class);
        $configHandlerStub->method('get')
            ->with(ConfigHandler::EXCLUDED_URLS_KEY)
            ->willReturn([
                [
                    'resource' => 'foo',
                    'identifier' => '1',
                    'shopId' => '1',
                ], [
                    'resource' => 'foo',
                    'identifier' => '2',
                    'shopId' => '1',
                ], [
                    'resource' => 'foo',
                    'identifier' => '3',
                    'shopId' => '0',
                ],
            ]);

        $filterContainerFactory = $this->getFilterContainerFactory($configHandlerStub);

        $filterContainer = $filterContainerFactory->buildFilterContainer('foo', 1);

        static::assertCount(3, $filterContainer->getFilters());
    }

    public function testBuildFilterContainerReturnsEmptyIdentifierAsZero()
    {
        $configHandlerStub = $this->createMock(ConfigHandler::class);
        $configHandlerStub->method('get')
            ->with(ConfigHandler::EXCLUDED_URLS_KEY)
            ->willReturn([
                [
                    'resource' => 'foo',
                    'identifier' => '1',
                    'shopId' => '1',
                ], [
                    'resource' => 'foo',
                    'identifier' => '2',
                    'shopId' => '1',
                ], [
                    'resource' => 'foo',
                    'identifier' => '',
                    'shopId' => '0',
                ],
            ]);

        $filterContainerFactory = $this->getFilterContainerFactory($configHandlerStub);

        $filterContainer = $filterContainerFactory->buildFilterContainer('foo', 1);

        static::assertEquals([1, 2, 0], $filterContainer->getFilters());
    }

    public function testBuildFilterContainerHandlesZeroForIdentifier()
    {
        $configHandlerStubEmptyIdentifier = $this->createMock(ConfigHandler::class);
        $configHandlerStubEmptyIdentifier->method('get')
            ->with(ConfigHandler::EXCLUDED_URLS_KEY)
            ->willReturn([
                [
                    'resource' => 'foo',
                    'identifier' => 0,
                    'shopId' => '',
                ],
            ]);

        $filterContainerFactory = $this->getFilterContainerFactory($configHandlerStubEmptyIdentifier);
        $filterContainer = $filterContainerFactory->buildFilterContainer('foo', 1);

        static::assertEquals([0], $filterContainer->getFilters());
    }

    public function testBuildFilterContainerHandlesZeroForShopId()
    {
        $configHandlerStubEmptyIdentifier = $this->createMock(ConfigHandler::class);
        $configHandlerStubEmptyIdentifier->method('get')
            ->with(ConfigHandler::EXCLUDED_URLS_KEY)
            ->willReturn([
                [
                    'resource' => 'foo',
                    'identifier' => 13,
                    'shopId' => 0,
                ],
            ]);

        $filterContainerFactory = $this->getFilterContainerFactory($configHandlerStubEmptyIdentifier);
        $filterContainer = $filterContainerFactory->buildFilterContainer('foo', 1);

        static::assertEquals([13], $filterContainer->getFilters());

        $filterContainer = $filterContainerFactory->buildFilterContainer('foo', 2);
        static::assertEquals([13], $filterContainer->getFilters());
    }

    public function testBuildFilterContainerHandlesEmptyStringAsZeroForShopId()
    {
        $configHandlerStubEmptyIdentifier = $this->createMock(ConfigHandler::class);
        $configHandlerStubEmptyIdentifier->method('get')
            ->with(ConfigHandler::EXCLUDED_URLS_KEY)
            ->willReturn([
                [
                    'resource' => 'foo',
                    'identifier' => 13,
                    'shopId' => '',
                ],
            ]);

        $filterContainerFactory = $this->getFilterContainerFactory($configHandlerStubEmptyIdentifier);
        $filterContainer = $filterContainerFactory->buildFilterContainer('foo', 1);

        static::assertEquals([13], $filterContainer->getFilters());

        $filterContainer = $filterContainerFactory->buildFilterContainer('foo', 2);
        static::assertEquals([13], $filterContainer->getFilters());
    }

    public function testBuildFilterContainerReturnsMultipleIdentifiersCommaSeparated()
    {
        $configHandlerStub = $this->createMock(ConfigHandler::class);
        $configHandlerStub->method('get')
            ->with(ConfigHandler::EXCLUDED_URLS_KEY)
            ->willReturn([
                [
                    'resource' => 'foo',
                    'identifier' => '1,2,3',
                    'shopId' => '1',
                ],
            ]);

        $filterContainerFactory = $this->getFilterContainerFactory($configHandlerStub);

        $filterContainer = $filterContainerFactory->buildFilterContainer('foo', 1);

        static::assertEquals([1, 2, 3], $filterContainer->getFilters());
    }

    private function getFilterContainerFactory(ConfigHandler $configHandlerStub)
    {
        return new FilterContainerFactory($configHandlerStub);
    }
}
