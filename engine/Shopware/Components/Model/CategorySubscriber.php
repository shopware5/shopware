<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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

namespace Shopware\Components\Model;

use Doctrine\Common\EventSubscriber as BaseEventSubscriber;
use Doctrine\ORM\Events;
use Shopware\Models\Article\Article;
use Shopware\Models\Category\Category;

/**
 * CategorySubscriber
 *
 * @category  Shopware
 * @package   Shopware\Components\Model
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class CategorySubscriber implements BaseEventSubscriber
{
    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    function getSubscribedEvents()
    {
        return array(Events::onFlush);
    }

    public function onFlush(\Doctrine\ORM\Event\OnFlushEventArgs $eventArgs)
    {
        $em  = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        // Update/Set Path for
        $entities = array_merge(
            $uow->getScheduledEntityInsertions(),
            $uow->getScheduledEntityUpdates()
        );

        foreach ($entities as $entity) {
            /* @var $entity Category */
            if (!($entity instanceof Category)) {
                continue;
            }

            $parent = $entity->getParent();
            $parentId = $parent->getId();

            $parents = $em->getParentCategories($parentId);
            $path = '|' . implode('|', $parents);

            $entity->internalSetPath($path);

            $md = $em->getClassMetadata(get_class($entity));
            $uow->recomputeSingleEntityChangeSet($md, $entity);
        }

        /* @var $col \Doctrine\ORM\PersistentCollection */
        foreach ($uow->getScheduledCollectionUpdates() AS $col) {

            if ($col->getOwner() instanceof Article) {
                foreach ($col->getInsertDiff() as $category) {
                    if (!$category instanceof Category) {
                        continue;
                    }

                    $parents = $em->getParentCategories($category->getId());
                    foreach ($parents as $parentId) {
                        $item = $em->getReference('\Shopware\Models\Category\Category', $parentId);

                        if (!$col->contains($item)) {
                            $col->add($item);
                        }
                    }
                }
            }

            if ($col->getOwner() instanceof Category) {
                $category = $col->getOwner();

                foreach ($col->getInsertDiff() as $article) {
                    if (!$article instanceof Article) {
                        continue;
                    }

                    $col = $article->getCategories();
                    $parents = $em->getParentCategories($category->getParentId());

                    foreach ($parents as $parentId) {
                        $item = $em->getReference('\Shopware\Models\Category\Category', $parentId);
                        if (!$col->contains($item)) {
                            $col->add($item);
                        }
                    }

                    $article->setCategories($col);

                    $uow->computeChangeSet(
                        $em->getClassMetadata(get_class($article)),
                        $article
                    );
                }
            }
        }
    }
}
