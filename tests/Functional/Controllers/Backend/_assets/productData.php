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

return $params = [
    'id' => 180,
    'mainDetailId' => 417,
    'configuratorSetId' => null,
    'supplierId' => 5,
    'supplierName' => '',
    'name' => 'Reisekoffer, in mehreren Farben',
    'added' => '16.07.2012',
    'changed' => '2012-08-21T15:30:23+02:00',
    'active' => true,
    'taxId' => 1,
    'template' => '',
    'autoNumber' => '',
    'avoidCustomerGroups' => [],
    'isConfigurator' => false,
    'categories' => [
        [
            'id' => 36,
            'name' => 'Deutsch>Sommerwelten>On World Tour',
            'allowDrag' => false,
            'parentId' => null,
            'leaf' => false,
        ],
    ],
    'customerGroups' => [],
    'mainPrices' => [
        [
            'id' => 483,
            'from' => 1,
            'to' => 'beliebig',
            'price' => 89.99000000000024,
            'pseudoPrice' => 99.9957,
            'regulationPrice' => 0,
            'percent' => 0,
            'cloned' => false,
            'customerGroupKey' => 'EK',
            'customerGroup' => [
                [
                    'id' => 1,
                    'key' => 'EK',
                    'name' => 'Shopkunden',
                    'tax' => true,
                    'taxInput' => true,
                    'mode' => false,
                    'discount' => 0,
                ],
            ],
        ],
    ],
    'mainDetail' => [
        [
            'id' => 417,
            'articleId' => 180,
            'number' => 'SW10180.1',
            'additionalText' => 'blau',
            'supplierNumber' => '',
            'active' => true,
            'inStock' => 124,
            'stockMin' => 0,
            'lastStock' => false,
            'kind' => 1,
            'position' => 0,
            'minPurchase' => 1,
            'maxPurchase' => null,
            'purchasePrice' => 0,
            'price' => 0,
            'standard' => true,
            'prices' => [],
            'configuratorOptions' => [],
        ],
    ],
    'configuratorSet' => [],
    'configuratorTemplate' => [],
    'dependencies' => [],
];
