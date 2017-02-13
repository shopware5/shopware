<?php

namespace Shopware\Bundle\CustomerSearchBundle\Gateway;

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
        $struct->setHasCanceledOrders((bool) $data['has_canceled_orders']);
        $struct->setPayments(explode(',', $data['selected_payments']));
        $struct->setShops(explode(',', $data['ordered_in_shops']));
        $struct->setDevices(explode(',', $data['ordered_with_devices']));
        $struct->setDispatches(explode(',', $data['selected_dispachtes']));
        $struct->setWeekdays(explode(',', $data['weekdays']));
        return $struct;
    }
}
