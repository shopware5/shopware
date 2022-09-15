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

namespace Shopware\Tests\Unit\Bundle\EsBackendBundle;

use Generator;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\EsBackendBundle\SearchQueryBuilder;

class SearchQueryBuilderTest extends TestCase
{
    /**
     * @dataProvider buildQueryDataProvider
     *
     * @param array{bool: array{should: array<array<string, array<string, array<string, string|int>>>>}} $expectedArray
     */
    public function testBuildQuery(string $term, array $expectedArray): void
    {
        $query = (new SearchQueryBuilder())->buildQuery(['swag_all' => 1], $term);

        static::assertSame($expectedArray, $query->toArray());
    }

    public function buildQueryDataProvider(): Generator
    {
        yield [
            'foo bar',
            [
                'bool' => [
                    'should' => [
                        ['match' => ['swag_all' => ['query' => 'foo', 'boost' => 1]]],
                        ['wildcard' => ['swag_all' => ['value' => '*foo*']]],
                        ['match' => ['swag_all' => ['query' => 'bar', 'boost' => 1]]],
                        ['wildcard' => ['swag_all' => ['value' => '*bar*']]],
                        ['match' => ['swag_all' => ['query' => 'foo bar', 'boost' => 2]]],
                        ['match' => ['swag_all' => ['query' => 'foo bar', 'boost' => 2]]],
                    ],
                ],
            ],
        ];

        yield [
            '/foo.bar-fb\\',
            [
                'bool' => [
                    'should' => [
                        ['match' => ['swag_all' => ['query' => 'foo', 'boost' => 1]]],
                        ['wildcard' => ['swag_all' => ['value' => '*foo*']]],
                        ['match' => ['swag_all' => ['query' => 'bar', 'boost' => 1]]],
                        ['wildcard' => ['swag_all' => ['value' => '*bar*']]],
                        ['match' => ['swag_all' => ['query' => 'fb', 'boost' => 1]]],
                        ['wildcard' => ['swag_all' => ['value' => '*fb*']]],
                        ['match' => ['swag_all' => ['query' => 'foo bar', 'boost' => 2]]],
                        ['match' => ['swag_all' => ['query' => 'foo bar fb', 'boost' => 2]]],
                    ],
                ],
            ],
        ];

        yield [
            '<test> foo bar',
            [
                'bool' => [
                    'should' => [
                        ['match' => ['swag_all' => ['query' => 'foo', 'boost' => 1]]],
                        ['wildcard' => ['swag_all' => ['value' => '*foo*']]],
                        ['match' => ['swag_all' => ['query' => 'bar', 'boost' => 1]]],
                        ['wildcard' => ['swag_all' => ['value' => '*bar*']]],
                        ['match' => ['swag_all' => ['query' => 'foo bar', 'boost' => 2]]],
                        ['match' => ['swag_all' => ['query' => 'foo bar', 'boost' => 2]]],
                    ],
                ],
            ],
        ];

        yield [
            '"foo* bar?',
            [
                'bool' => [
                    'should' => [
                        ['match' => ['swag_all' => ['query' => 'foo', 'boost' => 1]]],
                        ['wildcard' => ['swag_all' => ['value' => '*foo*']]],
                        ['match' => ['swag_all' => ['query' => 'bar', 'boost' => 1]]],
                        ['wildcard' => ['swag_all' => ['value' => '*bar*']]],
                        ['match' => ['swag_all' => ['query' => 'foo bar', 'boost' => 2]]],
                        ['match' => ['swag_all' => ['query' => 'foo bar', 'boost' => 2]]],
                    ],
                ],
            ],
        ];

        yield [
            'foo bar a',
            [
                'bool' => [
                    'should' => [
                        ['match' => ['swag_all' => ['query' => 'foo', 'boost' => 1]]],
                        ['wildcard' => ['swag_all' => ['value' => '*foo*']]],
                        ['match' => ['swag_all' => ['query' => 'bar', 'boost' => 1]]],
                        ['wildcard' => ['swag_all' => ['value' => '*bar*']]],
                        ['match' => ['swag_all' => ['query' => 'foo bar', 'boost' => 2]]],
                        ['match' => ['swag_all' => ['query' => 'foo bar', 'boost' => 2]]],
                    ],
                ],
            ],
        ];
    }
}
