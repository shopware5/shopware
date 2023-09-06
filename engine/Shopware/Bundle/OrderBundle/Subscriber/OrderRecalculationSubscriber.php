<?php
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

namespace Shopware\Bundle\OrderBundle\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use Shopware\Bundle\OrderBundle\Service\CalculationServiceInterface;
use Shopware\Models\Order\Detail;
use Shopware\Models\Order\Order;

class OrderRecalculationSubscriber implements SubscriberInterface
{
    /**
     * @var CalculationServiceInterface
     */
    protected $calculationService;

    public function __construct(CalculationServiceInterface $calculationService)
    {
        $this->calculationService = $calculationService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware\Models\Order\Detail::preUpdate' => 'preUpdate',
            'Shopware\Models\Order\Detail::preRemove' => 'preRemove',
            'Shopware\Models\Order\Detail::postPersist' => 'postPersist',
        ];
    }

    /**
     * If a product position get updated, the order totals must be recalculated
     *
     * @return void
     */
    public function preUpdate(Enlight_Event_EventArgs $arguments)
    {
        $orderDetail = $arguments->get('entity');
        // returns a change set for the model, which contains all changed properties with the old and new value.
        $changeSet = $arguments->get('entityManager')->getUnitOfWork()->getEntityChangeSet($orderDetail);

        $productChange = \array_key_exists('articleNumber', $changeSet) && $changeSet['articleNumber'][0] !== $changeSet['articleNumber'][1];
        $quantityChange = \array_key_exists('quantity', $changeSet) && $changeSet['quantity'][0] !== $changeSet['quantity'][1];
        $priceChanged = \array_key_exists('price', $changeSet) && $changeSet['price'][0] !== $changeSet['price'][1];
        $taxChanged = \array_key_exists('taxRate', $changeSet) && $changeSet['taxRate'][0] !== $changeSet['taxRate'][1];

        // If anything in the order position has been changed, we must recalculate the totals of the order
        if ($quantityChange || $productChange || $priceChanged || $taxChanged) {
            $this->calculationService->recalculateOrderTotals($orderDetail->getOrder());
        }
    }

    /**
     * If a product position got added to the order, the order totals must be recalculated
     *
     * @return void
     */
    public function postPersist(Enlight_Event_EventArgs $arguments)
    {
        $orderDetail = $arguments->get('entity');
        if (!$orderDetail instanceof Detail) {
            return;
        }

        $order = $orderDetail->getOrder();
        if (!$order instanceof Order) {
            return;
        }

        $this->calculationService->recalculateOrderTotals($order);
    }

    /**
     * If a product position get removed from the order, the order totals must be recalculated
     *
     * @return void
     */
    public function preRemove(Enlight_Event_EventArgs $arguments)
    {
        $orderDetail = $arguments->get('entity');
        if (!$orderDetail instanceof Detail) {
            return;
        }

        $order = $orderDetail->getOrder();
        if (!$order instanceof Order) {
            return;
        }

        $this->calculationService->recalculateOrderTotals($order);
    }
}
