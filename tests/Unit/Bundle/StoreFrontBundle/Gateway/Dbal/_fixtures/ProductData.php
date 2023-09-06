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

return [
    'blocked_customer_groups_is_empty_string' => [
        '__product_name' => 'First product',
        '__product_description' => 'First product description',
        '__product_description_long' => 'First product description long',
        '__product_laststock' => false,
        '__product_metaTitle' => 'First product',
        '__product_filtergroupID' => 0,
        '__product_topseller' => false,
        '__product_notification' => false,
        '__product_keywords' => 'first fast loud',
        '__product_template' => '',
        '__product_configurator_set_id' => 0,
        '__product_has_esd' => false,
        '__product_pricegroupActive' => false,
        '__topSeller_sales' => 0,
        '__variant_shippingfree' => false,
        '__variant_instock' => 1000,
        '__variant_suppliernumber' => '100',
        '__product_main_detail_id' => 1,
        '__variant_shippingtime' => 3,
        '__product_shippingtime' => 3,
        '__variant_releasedate' => null,
        '__product_datum' => '1970-01-01',
        '__product_changetime' => '1970-01-01',
        '__variant_additionaltext' => 'First product variant additionaltext',
        '__variant_ean' => 'EAN-SW00001',
        '__variant_height' => 0.0,
        '__variant_length' => 0.0,
        '__variant_stockmin' => 10,
        '__variant_weight' => 0.0,
        '__variant_width' => 0.0,
        '__product_blocked_customer_groups' => '',
        'EXPECTED__product_blocked_customer_groups' => [],
        '__product_has_available_variants' => true,
        '__product_fallback_price_count' => 10,
        '__product_custom_price_count' => 11,
    ],
    'blocked_customer_groups_is_null' => [
        '__product_name' => 'Second product',
        '__product_description' => 'Second product description',
        '__product_description_long' => 'Second product description long',
        '__product_laststock' => false,
        '__product_metaTitle' => 'Second product',
        '__product_filtergroupID' => 0,
        '__product_topseller' => false,
        '__product_notification' => false,
        '__product_keywords' => 'second fast loud',
        '__product_template' => '',
        '__product_configurator_set_id' => 0,
        '__product_has_esd' => false,
        '__product_pricegroupActive' => false,
        '__topSeller_sales' => 0,
        '__variant_shippingfree' => false,
        '__variant_instock' => 1000,
        '__variant_suppliernumber' => '100',
        '__product_main_detail_id' => 1,
        '__variant_shippingtime' => 3,
        '__product_shippingtime' => 3,
        '__variant_releasedate' => null,
        '__product_datum' => '1970-01-01',
        '__product_changetime' => '1970-01-01',
        '__variant_additionaltext' => 'Second product variant additionaltext',
        '__variant_ean' => 'EAN-SW00001',
        '__variant_height' => 0.0,
        '__variant_length' => 0.0,
        '__variant_stockmin' => 10,
        '__variant_weight' => 0.0,
        '__variant_width' => 0.0,
        '__product_blocked_customer_groups' => null,
        'EXPECTED__product_blocked_customer_groups' => [],
        '__product_has_available_variants' => true,
        '__product_fallback_price_count' => 10,
        '__product_custom_price_count' => 11,
    ],
    'with_blocked_customer_groups' => [
        '__product_name' => 'Third product',
        '__product_description' => 'Third product description',
        '__product_description_long' => 'Third product description long',
        '__product_laststock' => false,
        '__product_metaTitle' => 'Third product',
        '__product_filtergroupID' => 0,
        '__product_topseller' => false,
        '__product_notification' => false,
        '__product_keywords' => 'Third fast loud',
        '__product_template' => '',
        '__product_configurator_set_id' => 0,
        '__product_has_esd' => false,
        '__product_pricegroupActive' => false,
        '__topSeller_sales' => 0,
        '__variant_shippingfree' => false,
        '__variant_instock' => 1000,
        '__variant_suppliernumber' => '100',
        '__product_main_detail_id' => 2,
        '__variant_shippingtime' => 3,
        '__product_shippingtime' => 3,
        '__variant_releasedate' => null,
        '__product_datum' => '1970-01-01',
        '__product_changetime' => '1970-01-01',
        '__variant_additionaltext' => 'Third product variant additionaltext',
        '__variant_ean' => 'EAN-SW00002',
        '__variant_height' => 0.0,
        '__variant_length' => 0.0,
        '__variant_stockmin' => 10,
        '__variant_weight' => 0.0,
        '__variant_width' => 0.0,
        '__product_blocked_customer_groups' => '1|2|3',
        'EXPECTED__product_blocked_customer_groups' => [
            '1',
            '2',
            '3',
        ],
        '__product_has_available_variants' => true,
        '__product_fallback_price_count' => 10,
        '__product_custom_price_count' => 11,
    ],
];
