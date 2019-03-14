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

namespace Shopware\Bundle\ESIndexingBundle\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs as EventArgs;
use Shopware\Bundle\ESIndexingBundle\Struct\Backlog;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DomainBacklogSubscriber implements SubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'product_stock_was_changed' => 'onProductStockWasChanged',
        ];
    }

    public function onProductStockWasChanged(EventArgs $eventArgs)
    {
        if (!$this->container->getParameter('shopware.es.enabled')) {
            return;
        }
        if (!$this->container->getParameter('shopware.es.write_backlog')) {
            return;
        }
        $backlog = new Backlog(ORMBacklogSubscriber::EVENT_VARIANT_UPDATED, ['number' => $eventArgs->get('number')]);
        $this->container->get('shopware_elastic_search.backlog_processor')->add([$backlog]);
    }
}
