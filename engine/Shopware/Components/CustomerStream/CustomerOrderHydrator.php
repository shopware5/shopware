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

namespace Shopware\Components\CustomerStream;



class CustomerOrderHydrator
{
    public function hydrate(array $data)
    {
        $struct = new CustomerOrderStruct();
        $struct->setOrderCount((int) $data['count_orders']);
        $struct->setTotalAmount((float) $data['invoice_amount_sum']);
        $struct->setAvgAmount((float) $data['invoice_amount_avg']);
        $struct->setMinAmount((float) $data['invoice_amount_min']);
        $struct->setMaxAmount((float) $data['invoice_amount_max']);
        $struct->setAvgProductPrice((float) $data['product_avg']);
        $struct->setFirstOrderTime(new \DateTime($data['first_order_time']));
        $struct->setLastOrderTime(new \DateTime($data['last_order_time']));
        $struct->setPayments(explode(',', $data['selected_payments']));
        $struct->setShops(explode(',', $data['ordered_in_shops']));
        $struct->setDevices(explode(',', $data['ordered_with_devices']));
        $struct->setDispatches(explode(',', $data['selected_dispachtes']));
        $struct->setWeekdays(explode(',', $data['weekdays']));

        return $struct;
    }
}
