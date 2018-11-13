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
    public function getSubscribedEvents()
    {
        if (!$this->container->getParameter('shopware.es.backend.enabled')) {
            return [];
        }
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

    /**
     * @param mixed $entity
     *
     * @return array|null
     */
    private function getBacklog($entity)
    {
        switch (true) {
            // Article changes
            case $entity instanceof Article:
                return ['entity' => Article::class, 'entity_id' => $entity->getId()];

            // Variant changes
            case $entity instanceof Price:
                return ['entity' => Variant::class, 'entity_id' => $entity->getDetail()->getNumber()];
            case $entity instanceof Variant:
                return ['entity' => Variant::class, 'entity_id' => $entity->getNumber()];

            // Order changes
            case $entity instanceof Order:
                return ['entity' => Order::class, 'entity_id' => $entity->getId()];
            case $entity instanceof Detail:
                return ['entity' => Order::class, 'entity_id' => $entity->getOrder()->getId()];
            case $entity instanceof Billing:
                return ['entity' => Order::class, 'entity_id' => $entity->getOrder()->getId()];
            case $entity instanceof Shipping:
                return ['entity' => Order::class, 'entity_id' => $entity->getOrder()->getId()];

            // Customer changes
            case $entity instanceof Customer:
                return ['entity' => Customer::class, 'entity_id' => $entity->getId()];
            case $entity instanceof Address:
                return ['entity' => Customer::class, 'entity_id' => $entity->getCustomer()->getId()];
        }

        return null;
    }

    /**
     * @param OnFlushEventArgs $eventArgs
     */
    private function trace(OnFlushEventArgs $eventArgs)
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
            $queue[] = $backlog;
        }

        // Entity Insertions
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            $backlog = $this->getBacklog($entity);
            if (!$backlog) {
                continue;
            }
            $queue[] = $backlog;
        }

        // Entity updates
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $backlog = $this->getBacklog($entity);
            if (!$backlog) {
                continue;
            }
            $queue[] = $backlog;
        }

        $time = (new \DateTime())->format('Y-m-d H:i:s');
        foreach ($queue as $row) {
            $row['time'] = $time;
            $this->container->get('dbal_connection')->insert('s_es_backend_backlog', $row);
        }
    }
}
