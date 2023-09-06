<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Unit\Components\HttpCache;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Shopware\Components\HttpCache\Store;
use Symfony\Component\HttpFoundation\Request;

class StoreTest extends TestCase
{
    private Store $httpCacheStore;

    public function setUp(): void
    {
        $this->httpCacheStore = new Store(
            'test',
            [],
            true,
            [          // Set of parameters that must be ignored in the url
                'foo',
                '_foo',
                '__foo',
            ]
        );
    }

    /**
     * @return array<array{original: string, compare: string}>
     */
    public function provideUrls(): array
    {
        return [
            [
                'original' => 'http://example.com',
                'compare' => 'http://example.com/',
            ],
            [
                'original' => 'http://example.com/',
                'compare' => 'http://example.com/',
            ],
            [
                'original' => 'http://example.com/category',
                'compare' => 'http://example.com/category',
            ],
            [
                'original' => 'http://example.com/category/',
                'compare' => 'http://example.com/category/',
            ],
            [
                'original' => 'http://example.com/?a=a&a=1',
                'compare' => 'http://example.com/?a=1',
            ],
            [
                'original' => 'http://example.com/category/?a=a&a=1',
                'compare' => 'http://example.com/category/?a=1',
            ],
            [
                'original' => 'http://example.com?z=a&a=a',
                'compare' => 'http://example.com/?a=a&z=a',
            ],
            [
                'original' => 'http://example.com/category?z=a&a=a',
                'compare' => 'http://example.com/category?a=a&z=a',
            ],
            [
                'original' => 'http://example.com?Z=a&z=a',
                'compare' => 'http://example.com/?Z=a&z=a',
            ],
            [
                'original' => 'http://example.com/category/?Z=a&z=a',
                'compare' => 'http://example.com/category/?Z=a&z=a',
            ],
            [
                'original' => 'http://example.com/?cars[0]=Saab&cars[1]=Audi&colors[0]=red&colors[1]=red&colors[2]=blue&foo=1',
                'compare' => 'http://example.com/?cars[0]=Saab&cars[1]=Audi&colors[0]=red&colors[1]=red&colors[2]=blue',
            ],
            [
                'original' => 'http://example.com/category/sub/?cars[0]=Saab&cars[1]=Audi&colors[0]=red&colors[1]=red&colors[2]=blue&foo=1',
                'compare' => 'http://example.com/category/sub/?cars[0]=Saab&cars[1]=Audi&colors[0]=red&colors[1]=red&colors[2]=blue',
            ],
            [
                'original' => 'http://example.com/foo?foo',
                'compare' => 'http://example.com/foo',
            ],
            [
                'original' => 'http://example.com/foo/?foo',
                'compare' => 'http://example.com/foo/',
            ],
            [
                'original' => 'http://example.com?foo=bar',
                'compare' => 'http://example.com/',
            ],
            [
                'original' => 'http://example.com/category?foo=bar',
                'compare' => 'http://example.com/category',
            ],
            [
                'original' => 'http://example.com?_foo=bar',
                'compare' => 'http://example.com/',
            ],
            [
                'original' => 'http://example.com?__foo=bar',
                'compare' => 'http://example.com/',
            ],
            [
                'original' => 'http://example.com/?foo&z=a&a=a',
                'compare' => 'http://example.com/?a=a&z=a',
            ],
            [
                'original' => 'http://example.com/category.html?foo&z=a&a=a',
                'compare' => 'http://example.com/category.html?a=a&z=a',
            ],
            [
                'original' => 'http://example.com/?foo=bar&z=a&a=a',
                'compare' => 'http://example.com/?a=a&z=a',
            ],
            [
                'original' => 'http://example.com/category/sub?foo=bar&z=a&a=a',
                'compare' => 'http://example.com/category/sub?a=a&z=a',
            ],
            [
                'original' => 'http://example.com/?_foo=bar&z=a&a=a',
                'compare' => 'http://example.com/?a=a&z=a',
            ],
            [
                'original' => 'http://example.com/category?_foo=bar&z=a&a=a',
                'compare' => 'http://example.com/category?a=a&z=a',
            ],
            [
                'original' => 'http://example.com?__foo=bar&z=a&a=a',
                'compare' => 'http://example.com/?a=a&z=a',
            ],
            [
                'original' => 'http://example.com/somecategory?__foo=bar&z=a&a=a',
                'compare' => 'http://example.com/somecategory?a=a&z=a',
            ],
            [
                'original' => 'http://example.com?z=a&foo=bar&a=a',
                'compare' => 'http://example.com/?a=a&z=a',
            ],
            [
                'original' => 'http://example.com/somecategory?z=a&foo=bar&a=a',
                'compare' => 'http://example.com/somecategory?a=a&z=a',
            ],
            [
                'original' => 'http://example.com/?z=a&a=a&foo=bar',
                'compare' => 'http://example.com/?a=a&z=a',
            ],
            [
                'original' => 'http://example.com/somecategory/?z=a&a=a&foo=bar',
                'compare' => 'http://example.com/somecategory/?a=a&z=a',
            ],
        ];
    }

    /**
     * @dataProvider provideUrls
     *
     * @see Store::generateCacheKey
     */
    public function testGenerateCacheKey(string $originalURL, string $expectedURL): void
    {
        $originalRequest = Request::create($originalURL);
        $method = (new ReflectionClass($this->httpCacheStore))->getMethod('generateCacheKey');
        $method->setAccessible(true);

        static::assertSame(
            'md' . hash('sha256', $expectedURL),
            $method->invokeArgs($this->httpCacheStore, [$originalRequest])
        );
    }

    /**
     * @dataProvider provideUrls
     *
     * @see Store::verifyIgnoredParameters
     */
    public function testVerifyIgnoredParameters(string $originalURL, string $expectedURL): void
    {
        $originalRequest = Request::create($originalURL);
        $method = (new ReflectionClass($this->httpCacheStore))->getMethod('verifyIgnoredParameters');
        $method->setAccessible(true);

        static::assertSame(
            $expectedURL,
            $method->invokeArgs($this->httpCacheStore, [$originalRequest])
        );
    }
}
