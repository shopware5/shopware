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

namespace Shopware\Tests\Functional\Bundle\SearchBundleEs\FacetHandler\_fixtures;

class ElasticResult
{
    /**
     * @return array<mixed,mixed>
     */
    public function getResult(): array
    {
        return [
            'took' => 4,
            'timed_out' => false,
            '_shards' => [
                'total' => 1,
                'successful' => 1,
                'skipped' => 0,
                'failed' => 0,
            ],
            'hits' => [
                'total' => 12,
                'max_score' => null,
                'hits' => [
                    0 => [
                        '_index' => 'sw_shop1_product_20211020084630',
                        '_type' => '_doc',
                        '_id' => 'SW10009',
                        '_score' => null,
                        '_source' => [
                            'number' => 'SW10009',
                            'mainVariantId' => 15,
                            'id' => 9,
                            'variantId' => 15,
                        ],
                        'sort' => [
                            0 => 1345075200000,
                            1 => 9,
                        ],
                    ],
                    1 => [
                        '_index' => 'sw_shop1_product_20211020084630',
                        '_type' => '_doc',
                        '_id' => 'SW10002.3',
                        '_score' => null,
                        '_source' => [
                            'number' => 'SW10002.3',
                            'mainVariantId' => 125,
                            'id' => 2,
                            'variantId' => 125,
                        ],
                        'sort' => [
                            0 => 1344988800000,
                            1 => 2,
                        ],
                    ],
                    2 => [
                        '_index' => 'sw_shop1_product_20211020084630',
                        '_type' => '_doc',
                        '_id' => 'SW10003',
                        '_score' => null,
                        '_source' => [
                            'number' => 'SW10003',
                            'mainVariantId' => 3,
                            'id' => 3,
                            'variantId' => 3,
                        ],
                        'sort' => [
                            0 => 1344988800000,
                            1 => 3,
                        ],
                    ],
                    3 => [
                        '_index' => 'sw_shop1_product_20211020084630',
                        '_type' => '_doc',
                        '_id' => 'SW10006',
                        '_score' => null,
                        '_source' => [
                            'number' => 'SW10006',
                            'mainVariantId' => 6,
                            'id' => 6,
                            'variantId' => 6,
                        ],
                        'sort' => [
                            0 => 1344988800000,
                            1 => 6,
                        ],
                    ],
                    4 => [
                        '_index' => 'sw_shop1_product_20211020084630',
                        '_type' => '_doc',
                        '_id' => 'SW10012',
                        '_score' => null,
                        '_source' => [
                            'number' => 'SW10012',
                            'mainVariantId' => 18,
                            'id' => 12,
                            'variantId' => 18,
                        ],
                        'sort' => [
                            0 => 1343779200000,
                            1 => 12,
                        ],
                    ],
                    5 => [
                        '_index' => 'sw_shop1_product_20211020084630',
                        '_type' => '_doc',
                        '_id' => 'SW10011',
                        '_score' => null,
                        '_source' => [
                            'number' => 'SW10011',
                            'mainVariantId' => 17,
                            'id' => 11,
                            'variantId' => 17,
                        ],
                        'sort' => [
                            0 => 1343692800000,
                            1 => 11,
                        ],
                    ],
                    6 => [
                        '_index' => 'sw_shop1_product_20211020084630',
                        '_type' => '_doc',
                        '_id' => 'SW10123.1',
                        '_score' => null,
                        '_source' => [
                            'number' => 'SW10123.1',
                            'mainVariantId' => 256,
                            'id' => 122,
                            'variantId' => 256,
                        ],
                        'sort' => [
                            0 => 1342396800000,
                            1 => 122,
                        ],
                    ],
                    7 => [
                        '_index' => 'sw_shop1_product_20211020084630',
                        '_type' => '_doc',
                        '_id' => 'SW10007.1',
                        '_score' => null,
                        '_source' => [
                            'number' => 'SW10007.1',
                            'mainVariantId' => 249,
                            'id' => 7,
                            'variantId' => 249,
                        ],
                        'sort' => [
                            0 => 1341878400000,
                            1 => 7,
                        ],
                    ],
                    8 => [
                        '_index' => 'sw_shop1_product_20211020084630',
                        '_type' => '_doc',
                        '_id' => 'SW10010',
                        '_score' => null,
                        '_source' => [
                            'number' => 'SW10010',
                            'mainVariantId' => 16,
                            'id' => 10,
                            'variantId' => 16,
                        ],
                        'sort' => [
                            0 => 1341792000000,
                            1 => 10,
                        ],
                    ],
                    9 => [
                        '_index' => 'sw_shop1_product_20211020084630',
                        '_type' => '_doc',
                        '_id' => 'SW10008',
                        '_score' => null,
                        '_source' => [
                            'number' => 'SW10008',
                            'mainVariantId' => 14,
                            'id' => 8,
                            'variantId' => 14,
                        ],
                        'sort' => [
                            0 => 1341187200000,
                            1 => 8,
                        ],
                    ],
                    10 => [
                        '_index' => 'sw_shop1_product_20211020084630',
                        '_type' => '_doc',
                        '_id' => 'SW10004',
                        '_score' => null,
                        '_source' => [
                            'number' => 'SW10004',
                            'mainVariantId' => 4,
                            'id' => 4,
                            'variantId' => 4,
                        ],
                        'sort' => [
                            0 => 1339545600000,
                            1 => 4,
                        ],
                    ],
                    11 => [
                        '_index' => 'sw_shop1_product_20211020084630',
                        '_type' => '_doc',
                        '_id' => 'SW10005.1',
                        '_score' => null,
                        '_source' => [
                            'number' => 'SW10005.1',
                            'mainVariantId' => 252,
                            'id' => 5,
                            'variantId' => 252,
                        ],
                        'sort' => [
                            0 => 1339459200000,
                            1 => 5,
                        ],
                    ],
                ],
            ],
            'aggregations' => [
                'product_attribute_attr4' => [
                    'doc_count_error_upper_bound' => 0,
                    'sum_other_doc_count' => 0,
                    'buckets' => [
                        0 => [
                            'key' => 'Brooklyn',
                            'doc_count' => 2,
                        ],
                        1 => [
                            'key' => 'Mike',
                            'doc_count' => 2,
                        ],
                        2 => [
                            'key' => 'Robin',
                            'doc_count' => 2,
                        ],
                        3 => [
                            'key' => 'Trinity',
                            'doc_count' => 2,
                        ],
                        4 => [
                            'key' => 'Arnie',
                            'doc_count' => 1,
                        ],
                        5 => [
                            'key' => 'Batman',
                            'doc_count' => 1,
                        ],
                        6 => [
                            'key' => 'Cypher',
                            'doc_count' => 1,
                        ],
                        7 => [
                            'key' => 'Schokola',
                            'doc_count' => 1,
                        ],
                    ],
                ],
                'price' => [
                    'count' => 12,
                    'min' => 2.4900000000000002,
                    'max' => 49.950000000000003,
                    'avg' => 17.110833333333332,
                    'sum' => 205.32999999999998,
                ],
                'vote_average' => [
                    'doc_count_error_upper_bound' => 0,
                    'sum_other_doc_count' => 0,
                    'buckets' => [
                    ],
                ],
                'properties' => [
                    'doc_count_error_upper_bound' => 0,
                    'sum_other_doc_count' => 0,
                    'buckets' => [
                        0 => [
                            'key' => 40,
                            'doc_count' => 7,
                        ],
                        1 => [
                            'key' => 33,
                            'doc_count' => 6,
                        ],
                        2 => [
                            'key' => 24,
                            'doc_count' => 5,
                        ],
                        3 => [
                            'key' => 35,
                            'doc_count' => 5,
                        ],
                        4 => [
                            'key' => 23,
                            'doc_count' => 4,
                        ],
                        5 => [
                            'key' => 36,
                            'doc_count' => 4,
                        ],
                        6 => [
                            'key' => 22,
                            'doc_count' => 3,
                        ],
                        7 => [
                            'key' => 25,
                            'doc_count' => 3,
                        ],
                        8 => [
                            'key' => 26,
                            'doc_count' => 3,
                        ],
                        9 => [
                            'key' => 29,
                            'doc_count' => 3,
                        ],
                        10 => [
                            'key' => 31,
                            'doc_count' => 3,
                        ],
                        11 => [
                            'key' => 32,
                            'doc_count' => 3,
                        ],
                        12 => [
                            'key' => 39,
                            'doc_count' => 3,
                        ],
                        13 => [
                            'key' => 28,
                            'doc_count' => 2,
                        ],
                        14 => [
                            'key' => 27,
                            'doc_count' => 1,
                        ],
                        15 => [
                            'key' => 34,
                            'doc_count' => 1,
                        ],
                        16 => [
                            'key' => 41,
                            'doc_count' => 1,
                        ],
                    ],
                ],
                'shipping_free_filter' => [
                    'doc_count' => 0,
                    'shipping_free_count' => [
                        'value' => 0,
                    ],
                ],
                'manufacturer' => [
                    'doc_count_error_upper_bound' => 0,
                    'sum_other_doc_count' => 0,
                    'buckets' => [
                        0 => [
                            'key' => 2,
                            'doc_count' => 12,
                        ],
                    ],
                ],
                'has_available_variant_filter' => [
                    'doc_count' => 12,
                    'has_available_variant_count' => [
                        'value' => 12,
                    ],
                ],
            ],
        ];
    }
}
