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

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Shopware\Bundle\ESIndexingBundle\Struct\Backlog;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Article as ProductModel;
use Shopware\Models\Article\Detail as VariantModel;
use Shopware\Models\Article\Price as PriceModel;
use Shopware\Models\Article\Supplier as SupplierModel;
use Shopware\Models\Article\Unit as UnitModel;
use Shopware\Models\Article\Vote as VoteModel;
use Shopware\Models\Property\Option as PropertyGroupModel;
use Shopware\Models\Property\Value as PropertyOptionModel;
use Shopware\Models\Tax\Tax as TaxModel;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ORMBacklogSubscriber implements EventSubscriber
{
    const EVENT_ARTICLE_DELETED = 'article_deleted';
    const EVENT_ARTICLE_INSERTED = 'article_inserted';
    const EVENT_ARTICLE_UPDATED = 'article_updated';
    const EVENT_VARIANT_DELETED = 'variant_deleted';
    const EVENT_VARIANT_INSERTED = 'variant_inserted';
    const EVENT_VARIANT_UPDATED = 'variant_updated';
    const EVENT_PRICE_DELETED = 'variant_price_deleted';
    const EVENT_PRICE_INSERTED = 'variant_price_inserted';
    const EVENT_PRICE_UPDATED = 'variant_price_updated';
    const EVENT_VOTE_DELETED = 'vote_deleted';
    const EVENT_VOTE_INSERTED = 'vote_inserted';
    const EVENT_VOTE_UPDATED = 'vote_updated';
    const EVENT_SUPPLIER_DELETED = 'supplier_deleted';
    const EVENT_SUPPLIER_INSERTED = 'supplier_inserted';
    const EVENT_SUPPLIER_UPDATED = 'supplier_updated';
    const EVENT_TAX_DELETED = 'tax_deleted';
    const EVENT_TAX_INSERTED = 'tax_inserted';
    const EVENT_TAX_UPDATED = 'tax_updated';
    const EVENT_UNIT_DELETED = 'article_unit_deleted';
    const EVENT_UNIT_INSERTED = 'article_unit_inserted';
    const EVENT_UNIT_UPDATED = 'article_unit_updated';
    const EVENT_PROPERTY_GROUP_DELETED = 'property_group_deleted';
    const EVENT_PROPERTY_GROUP_INSERTED = 'property_group_inserted';
    const EVENT_PROPERTY_GROUP_UPDATED = 'property_group_updated';
    const EVENT_PROPERTY_OPTION_DELETED = 'property_option_deleted';
    const EVENT_PROPERTY_OPTION_INSERTED = 'property_option_inserted';
    const EVENT_PROPERTY_OPTION_UPDATED = 'property_option_updated';

    /**
     * @var Backlog[]
     */
    private $queue = [];

    /**
     * @var array
     */
    private $inserts = [];

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
        if (!$this->container->getParameter('shopware.es.enabled')) {
            return [];
        }
        if (!$this->container->getParameter('shopware.es.write_backlog')) {
            return [];
        }

        return [Events::onFlush, Events::postFlush];
    }

    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        /** @var ModelManager $em */
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        // Entity deletions
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            $backlog = $this->getDeleteBacklog($entity);
            if (!$backlog) {
                continue;
            }
            $this->queue[] = $backlog;
        }

        // Entity Insertions
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            $this->inserts[] = $entity;
        }

        // Entity updates
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $backlog = $this->getUpdateBacklog($entity);
            if (!$backlog) {
                continue;
            }
            $this->queue[] = $backlog;
        }
    }

    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        foreach ($this->inserts as $entity) {
            $backlog = $this->getInsertBacklog($entity);
            if (!$backlog) {
                continue;
            }
            $this->queue[] = $backlog;
        }
        $this->inserts = [];
    }

    public function processQueue()
    {
        if (empty($this->queue)) {
            return;
        }
        $this->container->get('shopware_elastic_search.backlog_processor')->add($this->queue);
        $this->queue = [];
    }

    /**
     * @param object $entity
     *
     * @return Backlog|null
     */
    private function getDeleteBacklog($entity)
    {
        switch (true) {
            case $entity instanceof ProductModel:
                return new Backlog(self::EVENT_ARTICLE_DELETED, ['id' => $entity->getId()]);
            case $entity instanceof VariantModel:
                return new Backlog(self::EVENT_VARIANT_DELETED, ['number' => $entity->getNumber()]);
            case $entity instanceof PriceModel:
                return new Backlog(self::EVENT_PRICE_DELETED, ['number' => $entity->getDetail()->getNumber()]);
            case $entity instanceof VoteModel:
                return new Backlog(self::EVENT_VOTE_DELETED, ['articleId' => $entity->getArticle()->getId()]);
            case $entity instanceof SupplierModel:
                return new Backlog(self::EVENT_SUPPLIER_DELETED, ['id' => $entity->getId()]);
            case $entity instanceof UnitModel:
                return new Backlog(self::EVENT_UNIT_DELETED, ['id' => $entity->getId()]);
            case $entity instanceof TaxModel:
                return new Backlog(self::EVENT_TAX_DELETED, ['id' => $entity->getId()]);
            case $entity instanceof PropertyGroupModel:
                return new Backlog(self::EVENT_PROPERTY_GROUP_DELETED, ['id' => $entity->getId()]);
            case $entity instanceof PropertyOptionModel:
                return new Backlog(self::EVENT_PROPERTY_OPTION_DELETED, ['id' => $entity->getId(), 'groupId' => $entity->getOption()->getId()]);
        }
    }

    private function getInsertBacklog($entity)
    {
        switch (true) {
            case $entity instanceof ProductModel:
                return new Backlog(self::EVENT_ARTICLE_INSERTED, ['id' => $entity->getId()]);
            case $entity instanceof VariantModel:
                return new Backlog(self::EVENT_VARIANT_INSERTED, ['number' => $entity->getNumber()]);
            case $entity instanceof PriceModel:
                return new Backlog(self::EVENT_PRICE_INSERTED, ['number' => $entity->getDetail()->getNumber()]);
            case $entity instanceof VoteModel:
                return new Backlog(self::EVENT_VOTE_INSERTED, ['articleId' => $entity->getArticle()->getId()]);
            case $entity instanceof SupplierModel:
                return new Backlog(self::EVENT_SUPPLIER_INSERTED, ['id' => $entity->getId()]);
            case $entity instanceof UnitModel:
                return new Backlog(self::EVENT_UNIT_INSERTED, ['id' => $entity->getId()]);
            case $entity instanceof TaxModel:
                return new Backlog(self::EVENT_TAX_INSERTED, ['id' => $entity->getId()]);
            case $entity instanceof PropertyGroupModel:
                return new Backlog(self::EVENT_PROPERTY_GROUP_INSERTED, ['id' => $entity->getId()]);
            case $entity instanceof PropertyOptionModel:
                return new Backlog(self::EVENT_PROPERTY_OPTION_INSERTED, ['id' => $entity->getId(), 'groupId' => $entity->getOption()->getId()]);
        }
    }

    /**
     * @param object $entity
     *
     * @return Backlog|null
     */
    private function getUpdateBacklog($entity)
    {
        switch (true) {
            case $entity instanceof ProductModel:
                return new Backlog(self::EVENT_ARTICLE_UPDATED, ['id' => $entity->getId()]);
            case $entity instanceof VariantModel:
                return new Backlog(self::EVENT_VARIANT_UPDATED, ['number' => $entity->getNumber()]);
            case $entity instanceof PriceModel:
                return new Backlog(self::EVENT_PRICE_UPDATED, ['number' => $entity->getDetail()->getNumber()]);
            case $entity instanceof VoteModel:
                return new Backlog(self::EVENT_VOTE_UPDATED, ['articleId' => $entity->getArticle()->getId()]);
            case $entity instanceof SupplierModel:
                return new Backlog(self::EVENT_SUPPLIER_UPDATED, ['id' => $entity->getId()]);
            case $entity instanceof UnitModel:
                return new Backlog(self::EVENT_UNIT_UPDATED, ['id' => $entity->getId()]);
            case $entity instanceof TaxModel:
                return new Backlog(self::EVENT_TAX_UPDATED, ['id' => $entity->getId()]);
            case $entity instanceof PropertyGroupModel:
                return new Backlog(self::EVENT_PROPERTY_GROUP_UPDATED, ['id' => $entity->getId()]);
            case $entity instanceof PropertyOptionModel:
                return new Backlog(self::EVENT_PROPERTY_OPTION_UPDATED, ['id' => $entity->getId(), 'groupId' => $entity->getOption()->getId()]);
        }
    }
}
