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

/**
 * @category  Shopware
 * @package   Shopware\Models\Order
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class OrderHistorySubscriber implements EventSubscriber
{
    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(Events::preUpdate);
    }

    /**
     * @param PreUpdateEventArgs $eventArgs
     */
    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $order = $eventArgs->getEntity();

        if (!($order instanceof Order)) {
            return;
        }

        //order or payment status changed?
        if (
            !$eventArgs->hasChangedField('paymentStatus') &&
            !$eventArgs->hasChangedField('orderStatus')
        ) {
            return;
        }

        $historyData = array(
            'userID'      => null,
            'change_date' => date('Y-m-d H:i:s'),
            'orderID'     => $order->getId(),
        );

        if ($this->hasIdentity()) {
            $user = $eventArgs->getEntityManager()->find('Shopware\Models\User\User', Shopware()->Auth()->getIdentity()->id);
            $historyData['userID'] = $user->getId();
        }

        //order status changed?
        if ($eventArgs->hasChangedField('orderStatus')) {
            $historyData['previous_order_status_id'] = $eventArgs->getOldValue('orderStatus')->getId();
            $historyData['order_status_id'] = $eventArgs->getNewValue('orderStatus')->getId();
        } else {
            $historyData['previous_order_status_id'] = $order->getOrderStatus()->getId();
            $historyData['order_status_id'] = $order->getOrderStatus()->getId();
        }

        //payment status changed?
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
        return Shopware()->Container()->initialized('auth') &&
            Shopware()->Auth()->getIdentity() &&
            Shopware()->Auth()->getIdentity()->id;
    }
}
