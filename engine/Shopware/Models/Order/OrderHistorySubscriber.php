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

namespace Shopware\Models\Order;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

class OrderHistorySubscriber implements EventSubscriber
{
    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [Events::preUpdate];
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $order = $eventArgs->getEntity();

        if (!($order instanceof Order)) {
            return;
        }

        // Order or payment status changed?
        if (
            !$eventArgs->hasChangedField('paymentStatus')
            && !$eventArgs->hasChangedField('orderStatus')
        ) {
            return;
        }

        $historyData = [
            'userID' => null,
            'change_date' => date('Y-m-d H:i:s'),
            'orderID' => $order->getId(),
        ];

        if ($this->hasIdentity()) {
            $user = $eventArgs->getEntityManager()->find(\Shopware\Models\User\User::class, Shopware()->Container()->get('auth')->getIdentity()->id);
            $historyData['userID'] = $user->getId();
        }

        // Order status changed?
        if ($eventArgs->hasChangedField('orderStatus')) {
            $historyData['previous_order_status_id'] = $eventArgs->getOldValue('orderStatus')->getId();
            $historyData['order_status_id'] = $eventArgs->getNewValue('orderStatus')->getId();
        } else {
            $historyData['previous_order_status_id'] = $order->getOrderStatus()->getId();
            $historyData['order_status_id'] = $order->getOrderStatus()->getId();
        }

        // Payment status changed?
        if ($eventArgs->hasChangedField('paymentStatus')) {
            $historyData['previous_payment_status_id'] = $eventArgs->getOldValue('paymentStatus')->getId();
            $historyData['payment_status_id'] = $eventArgs->getNewValue('paymentStatus')->getId();
        } else {
            $historyData['previous_payment_status_id'] = $order->getPaymentStatus()->getId();
            $historyData['payment_status_id'] = $order->getPaymentStatus()->getId();
        }

        $eventArgs->getEntityManager()->getConnection()->insert('s_order_history', $historyData);
    }

    /**
     * @return bool
     */
    private function hasIdentity()
    {
        return Shopware()->Container()->initialized('auth')
            && Shopware()->Container()->get('auth')->getIdentity()
            && Shopware()->Container()->get('auth')->getIdentity()->id;
    }
}
