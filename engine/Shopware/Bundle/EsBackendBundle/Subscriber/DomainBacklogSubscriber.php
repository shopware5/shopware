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

namespace Shopware\Bundle\EsBackendBundle\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\Bundle\EsBackendBundle\BacklogServiceInterface;
use Shopware\Bundle\EsBackendBundle\Struct\Backlog;
use Shopware\Models\Order\Order;

class DomainBacklogSubscriber implements SubscriberInterface
{
    /**
     * @var bool
     */
    private $writeBacklog;

    /**
     * @var BacklogServiceInterface
     */
    private $backlogService;

    public function __construct(bool $writeBacklog, BacklogServiceInterface $backlogService)
    {
        $this->writeBacklog = $writeBacklog;
        $this->backlogService = $backlogService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'Shopware_Modules_Order_SaveOrder_OrderCreated' => 'onOrderCreated',
        ];
    }

    public function onOrderCreated(\Enlight_Event_EventArgs $args): void
    {
        if (!$this->writeBacklog) {
            return;
        }

        $this->backlogService->write([new Backlog(Order::class, $args->get('orderId'))]);
    }
}
