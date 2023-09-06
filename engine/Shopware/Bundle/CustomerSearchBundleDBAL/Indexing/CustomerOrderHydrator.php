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

namespace Shopware\Bundle\CustomerSearchBundleDBAL\Indexing;

use DateTime;

class CustomerOrderHydrator
{
    /**
     * @param array<string, mixed> $data
     *
     * @return CustomerOrder
     */
    public function hydrate(array $data)
    {
        $struct = new CustomerOrder();

        if (empty($data)) {
            return $struct;
        }

        $struct->setOrderCount((int) ($data['count_orders'] ?? 0));
        $struct->setTotalAmount((float) ($data['invoice_amount_sum'] ?? 0.0));
        $struct->setAvgAmount((float) ($data['invoice_amount_avg'] ?? 0.0));
        $struct->setMinAmount((float) ($data['invoice_amount_min'] ?? 0.0));
        $struct->setMaxAmount((float) ($data['invoice_amount_max'] ?? 0.0));
        $struct->setAvgProductPrice((float) ($data['product_avg'] ?? 0.0));
        $struct->setFirstOrderTime(isset($data['first_order_time']) ? new DateTime($data['first_order_time']) : null);
        $struct->setLastOrderTime(isset($data['last_order_time']) ? new DateTime($data['last_order_time']) : null);
        $struct->setPayments(array_map('\intval', $this->explodeAndFilter($data['selected_payments'] ?? '')));
        $struct->setShops(array_map('\intval', $this->explodeAndFilter($data['ordered_in_shops'] ?? '')));
        $struct->setDevices($this->explodeAndFilter($data['ordered_with_devices'] ?? ''));
        $struct->setDispatches(array_map('\intval', $this->explodeAndFilter($data['selected_dispachtes'] ?? '')));
        $struct->setWeekdays($this->explodeAndFilter($data['weekdays'] ?? ''));

        if (\array_key_exists('product_numbers', $data)) {
            $struct->setProducts($this->explodeAndFilter($data['product_numbers'] ?? ''));
        }
        if (\array_key_exists('category_ids', $data)) {
            $struct->setCategories(array_map('\intval', $this->explodeAndFilter($data['category_ids'] ?? '')));
        }
        if (\array_key_exists('manufacturer_ids', $data)) {
            $struct->setManufacturers(array_map('\intval', $this->explodeAndFilter($data['manufacturer_ids'] ?? '')));
        }

        return $struct;
    }

    /**
     * @return array<string|numeric-string>
     */
    private function explodeAndFilter(string $value): array
    {
        return array_filter(explode(',', $value));
    }
}
