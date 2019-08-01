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

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Shopware\Bundle\EsBackendBundle\Struct\Backlog;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail as Variant;
use Shopware\Models\Article\Price;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Order\Billing;
use Shopware\Models\Order\Detail;
use Shopware\Models\Order\Order;
use Shopware\Models\Order\Shipping;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OrmBacklogSubscriber implements EventSubscriber
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
    public function getSubscribedEvents(): array
    {
        if (!$this->container->getParameter('shopware.es.backend.write_backlog')) {
            return [];
        }

        return [Events::onFlush];
    }

    /**
     * {@inheritdoc}
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        try {
            $this->trace($eventArgs);
        } catch (\Exception $e) {
            $this->container->get('corelogger')->error($e->getMessage());
        }
    }

    private function getBacklog(object $entity): ?Backlog
    {
        switch (true) {
            // Article changes
            case $entity instanceof Article:
                return new Backlog(Article::class, $entity->getId());

            // Variant changes
            case $entity instanceof Price:
                return new Backlog(Article::class, $entity->getDetail()->getArticleId());
            case $entity instanceof Variant:
                return new Backlog(Article::class, $entity->getArticleId());

            // Order changes
            case $entity instanceof Order:
                return new Backlog(Order::class, $entity->getId());
            case $entity instanceof Detail:
                return new Backlog(Order::class, $entity->getOrder()->getId());
            case $entity instanceof Billing:
                return new Backlog(Order::class, $entity->getOrder()->getId());
            case $entity instanceof Shipping:
                return new Backlog(Order::class, $entity->getOrder()->getId());

            // Customer changes
            case $entity instanceof Customer:
                return new Backlog(Customer::class, $entity->getId());
            case $entity instanceof Address:
                return new Backlog(Customer::class, $entity->getCustomer()->getId());
        }

        return null;
    }

    private function getBacklogKey(Backlog $backlog): string
    {
        return $backlog->entity . '_' . $backlog->entity_id;
    }

    private function trace(OnFlushEventArgs $eventArgs): void
    {
        /** @var ModelManager $em */
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        $queue = [];
        // Entity deletions
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $backlog = $this->getBacklog($entity);
            if (!$backlog) {
                continue;
            }
            $queue[$this->getBacklogKey($backlog)] = $backlog;
        }

        // Entity Insertions
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            $backlog = $this->getBacklog($entity);
            if (!$backlog) {
                continue;
            }
            $queue[$this->getBacklogKey($backlog)] = $backlog;
        }

        // Entity updates
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $backlog = $this->getBacklog($entity);
            if (!$backlog) {
                continue;
            }
            $queue[$this->getBacklogKey($backlog)] = $backlog;
        }

        $this->container->get('shopware_bundle_es_backend.backlog_service')->write(array_values($queue));
    }
}
