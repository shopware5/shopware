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

namespace Shopware\Bundle\OrderBundle\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\Bundle\OrderBundle\Service\CalculationServiceInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Detail;
use Shopware\Models\Order\Order;

class OrderRecalculationSubscriber implements SubscriberInterface
{
    /**
     * @var CalculationServiceInterface
     */
    protected $calculationService;

    public function __construct(
        CalculationServiceInterface $calculationService
    ) {
        $this->calculationService = $calculationService;
    }

    /**
     * @return array
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
     */
    public function preUpdate(\Enlight_Event_EventArgs $arguments)
    {
        /** @var Detail $orderDetail */
        $orderDetail = $arguments->get('entity');
        /** @var ModelManager $entityManager */
        $entityManager = $arguments->get('entityManager');

        //returns a change set for the model, which contains all changed properties with the old and new value.
        $changeSet = $entityManager->getUnitOfWork()->getEntityChangeSet($orderDetail);

        $productChange = $changeSet['articleNumber'][0] !== $changeSet['articleNumber'][1];
        $quantityChange = $changeSet['quantity'][0] !== $changeSet['quantity'][1];
        $priceChanged = $changeSet['price'][0] !== $changeSet['price'][1];
        $taxChanged = $changeSet['taxRate'][0] !== $changeSet['taxRate'][1];

        // If anything in the order position has been changed, we must recalculate the totals of the order
        if ($quantityChange || $productChange || $priceChanged || $taxChanged) {
            $this->calculationService->recalculateOrderTotals($orderDetail->getOrder());
        }
    }

    /**
     * If a product position got added to the order, the order totals must be recalculated
     */
    public function postPersist(\Enlight_Event_EventArgs $arguments)
    {
        /** @var Detail $orderDetail */
        $orderDetail = $arguments->get('entity');

        /** @var Order $order */
        $order = $orderDetail->getOrder();

        $this->calculationService->recalculateOrderTotals($order);
    }

    /**
     * If a product position get removed from the order, the order totals must be recalculated
     */
    public function preRemove(\Enlight_Event_EventArgs $arguments)
    {
        /** @var Detail $orderDetail */
        $orderDetail = $arguments->get('entity');

        /** @var Order $order */
        $order = $orderDetail->getOrder();

        $this->calculationService->recalculateOrderTotals($order);
    }
}
