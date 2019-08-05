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
use Shopware\Bundle\OrderBundle\Service\StockServiceInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Detail;

class ProductStockSubscriber implements SubscriberInterface
{
    /**
     * @var StockServiceInterface
     */
    protected $stockService;

    public function __construct(
        StockServiceInterface $stockService
    ) {
        $this->stockService = $stockService;
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
     * If the position product has been changed, the old product stock must be increased based on the (old) ordering quantity.
     * The stock of the new product will be reduced by the (new) ordered quantity.
     */
    public function preUpdate(\Enlight_Event_EventArgs $arguments)
    {
        /** @var Detail $orderDetail */
        $orderDetail = $arguments->get('entity');

        /** @var ModelManager $entityManager */
        $entityManager = $arguments->get('entityManager');

        //returns a change set for the model, which contains all changed properties with the old and new value.
        $changeSet = $entityManager->getUnitOfWork()->getEntityChangeSet($orderDetail);

        $this->stockService->updateProductDetail(
            $orderDetail,
            isset($changeSet['articleNumber']) ? $changeSet['articleNumber'][0] : null,
            isset($changeSet['quantity']) ? $changeSet['quantity'][0] : null,
            isset($changeSet['articleNumber']) ? $changeSet['articleNumber'][1] : null,
            isset($changeSet['quantity']) ? $changeSet['quantity'][1] : null
        );
    }

    /**
     * If an position is added, the stock of the product will be reduced by the ordered quantity.
     */
    public function postPersist(\Enlight_Event_EventArgs $arguments)
    {
        /** @var Detail $orderDetail */
        $orderDetail = $arguments->get('entity');

        $this->stockService->addProductDetail($orderDetail);
    }

    /**
     * If the position is deleted, the product stock must be increased based on the ordering quantity.
     */
    public function preRemove(\Enlight_Event_EventArgs $arguments)
    {
        /** @var Detail $orderDetail */
        $orderDetail = $arguments->get('entity');

        $this->stockService->removeProductDetail($orderDetail);
    }
}
