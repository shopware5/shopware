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

namespace Shopware\Bundle\CustomerSearchBundleDBAL\Indexing;

class CustomerOrderHydrator
{
    public function hydrate(array $data)
    {
        $struct = new CustomerOrder();

        if (empty($data)) {
            return $struct;
        }

        $struct->setOrderCount((int) $data['count_orders']);
        $struct->setTotalAmount((float) $data['invoice_amount_sum']);
        $struct->setAvgAmount((float) $data['invoice_amount_avg']);
        $struct->setMinAmount((float) $data['invoice_amount_min']);
        $struct->setMaxAmount((float) $data['invoice_amount_max']);
        $struct->setAvgProductPrice((float) $data['product_avg']);
        $struct->setFirstOrderTime(new \DateTime($data['first_order_time']));
        $struct->setLastOrderTime(new \DateTime($data['last_order_time']));
        $struct->setPayments($this->explodeAndFilter($data['selected_payments']));
        $struct->setShops($this->explodeAndFilter($data['ordered_in_shops']));
        $struct->setDevices($this->explodeAndFilter($data['ordered_with_devices']));
        $struct->setDispatches($this->explodeAndFilter($data['selected_dispachtes']));
        $struct->setWeekdays($this->explodeAndFilter($data['weekdays']));

        if (array_key_exists('product_numbers', $data)) {
            $struct->setProducts($this->explodeAndFilter($data['product_numbers']));
        }
        if (array_key_exists('category_ids', $data)) {
            $struct->setCategories($this->explodeAndFilter($data['category_ids']));
        }
        if (array_key_exists('manufacturer_ids', $data)) {
            $struct->setManufacturers($this->explodeAndFilter($data['manufacturer_ids']));
        }

        return $struct;
    }

    private function explodeAndFilter($value)
    {
        return array_filter(explode(',', $value));
    }
}
