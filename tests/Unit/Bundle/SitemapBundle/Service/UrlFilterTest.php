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
use Shopware\Bundle\SitemapBundle\Service\UrlFilter;
use Shopware\Bundle\SitemapBundle\Struct\Url;
use Shopware\Bundle\SitemapBundle\UrlFilter\Base;
use Shopware\Bundle\SitemapBundle\UrlFilter\UrlFilterException;

class UrlFilterTest extends TestCase
{
    public function testFilterAppliesFilters()
    {
        $configHandlerStub = $this->createMock(ConfigHandler::class);
        $configHandlerStub->method('get')
            ->with(ConfigHandler::EXCLUDED_URLS_KEY)
            ->willReturn([
                [
                    'resource' => 'foo',
                    'identifier' => '1',
                    'shopId' => '1',
                ],
            ]);

        $urlFilter = $this->getUrlFilter($configHandlerStub, new FilterHandlers());

        $urls = $urlFilter->filter([
            new Url(
                'fooBar.com',
                new \DateTime(),
                'weekly',
                'foo',
                '1'
            ),
        ], 1);

        static::assertEmpty($urls);
    }

    public function testFilterOnlyFiltersWithValidIdentifier()
    {
        $identifier = 1;

        $configHandlerStub = $this->createMock(ConfigHandler::class);
        $configHandlerStub->method('get')
            ->with(ConfigHandler::EXCLUDED_URLS_KEY)
            ->willReturn([
                [
                    'resource' => 'foo',
                    'identifier' => $identifier,
                    'shopId' => '1',
                ],
            ]);

        $urlFilter = $this->getUrlFilter($configHandlerStub, new FilterHandlers());

        $urls = $urlFilter->filter([
            new Url(
                'fooBar.com',
                new \DateTime(),
                'weekly',
                'foo',
                $identifier
            ),
            new Url(
                'fooBar.com',
                new \DateTime(),
                'weekly',
                'foo',
                2
            ),
        ], 1);

        static::assertCount(1, $urls);
    }

    public function testFilterFiltersWholeResourceEmptyIdentifier()
    {
        $identifier = 1;

        $configHandlerStub = $this->createMock(ConfigHandler::class);
        $configHandlerStub->method('get')
            ->with(ConfigHandler::EXCLUDED_URLS_KEY)
            ->willReturn([
                [
                    'resource' => 'foo',
                    'identifier' => '',
                    'shopId' => '1',
                ],
            ]);

        $urlFilter = $this->getUrlFilter($configHandlerStub, new FilterHandlers());

        $urls = $urlFilter->filter([
            new Url(
                'fooBar.com',
                new \DateTime(),
                'weekly',
                'foo',
                $identifier
            ),
            new Url(
                'fooBar.com',
                new \DateTime(),
                'weekly',
                'foo',
                2
            ),
        ], 1);

        static::assertEmpty($urls);
    }

    public function testFilterHandlesMultipleIdentifiers()
    {
        $configHandlerStub = $this->createMock(ConfigHandler::class);
        $configHandlerStub->method('get')
            ->with(ConfigHandler::EXCLUDED_URLS_KEY)
            ->willReturn([
                [
                    'resource' => 'foo',
                    'identifier' => '10, 27',
                    'shopId' => '1',
                ],
            ]);

        $urlFilter = $this->getUrlFilter($configHandlerStub, new FilterHandlers());

        $urls = $urlFilter->filter([
            new Url(
                'fooBar.com',
                new \DateTime(),
                'weekly',
                'foo',
                10
            ),
            new Url(
                'fooBar.com',
                new \DateTime(),
                'weekly',
                'foo',
                27
            ),
            new Url(
                'fooBar.com',
                new \DateTime(),
                'weekly',
                'foo',
                25
            ),
        ], 1);

        static::assertCount(1, $urls);
        static::assertSame(25, $urls[0]->getIdentifier());
    }

    public function testFilterShouldNotFilterNoFiltersSet()
    {
        $identifier = 1;

        $configHandlerStub = $this->createMock(ConfigHandler::class);
        $configHandlerStub->method('get')
            ->with(ConfigHandler::EXCLUDED_URLS_KEY)
            ->willReturn([]);

        $urlFilter = $this->getUrlFilter($configHandlerStub, new FilterHandlers());

        $urls = $urlFilter->filter([
            new Url(
                'fooBar.com',
                new \DateTime(),
                'weekly',
                'foo',
                $identifier
            ),
            new Url(
                'fooBar.com',
                new \DateTime(),
                'weekly',
                'foo',
                2
            ),
        ], 1);

        static::assertCount(2, $urls);
    }

    public function testFilterShouldThrowExceptionNoFilterHandlerForResource()
    {
        $configHandlerStub = $this->createMock(ConfigHandler::class);
        $configHandlerStub->method('get')
            ->with(ConfigHandler::EXCLUDED_URLS_KEY)
            ->willReturn([
                [
                    'resource' => 'fooBar',
                    'identifier' => '1',
                    'shopId' => '1',
                ],
            ]);

        $urlFilter = $this->getUrlFilter($configHandlerStub, new FilterHandlers());

        $this->expectException(UrlFilterException::class);
        $this->expectExceptionMessage("No handler known for resource 'fooBar'");

        $urlFilter->filter([
            new Url(
                'fooBar.com',
                new \DateTime(),
                'weekly',
                'fooBar',
                1
            ),
        ], 1);
    }

    private function getUrlFilter(ConfigHandler $configHandlerStub, \IteratorAggregate $filterHandlers)
    {
        return new UrlFilter($this->getFilterContainerFactory($configHandlerStub), $filterHandlers);
    }

    private function getFilterContainerFactory(ConfigHandler $configHandlerStub)
    {
        return new FilterContainerFactory($configHandlerStub);
    }
}

class FilterHandlers implements \IteratorAggregate
{
    public function getIterator()
    {
        return new \ArrayIterator([
            'foo' => new Foo(),
        ]);
    }
}

class Foo extends Base
{
    public function supports($resourceName)
    {
        return $resourceName === 'foo';
    }
}
