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

namespace Shopware\Components\Model;

use Doctrine\Common\EventSubscriber as BaseEventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\PersistentCollection;
use Shopware\Models\Article\Article;
use Shopware\Models\Category\Category;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CategorySubscriber implements BaseEventSubscriber
{
    /**
     * @var CategoryDenormalization
     */
    protected $categoryDenormalization;

    /**
     * @var array
     */
    protected $pendingAddAssignments = [];

    /**
     * @var array
     */
    protected $pendingRemoveAssignments = [];

    /**
     * @var array
     */
    protected $pendingMoves = [];

    /**
     * @var bool
     */
    protected $disabledForNextFlush = false;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return CategoryDenormalization
     */
    public function getCategoryComponent()
    {
        $this->categoryDenormalization = $this->container->get('categorydenormalization');

        $this->categoryDenormalization->disableTransactions();

        return $this->categoryDenormalization;
    }

    /**
     * Disable events for next flush event
     */
    public function disableForNextFlush()
    {
        $this->disabledForNextFlush = true;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [Events::onFlush, Events::postFlush];
    }

    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        if ($this->disabledForNextFlush) {
            return;
        }

        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        $this->pendingAddAssignments = [];
        $this->pendingRemoveAssignments = [];

        // Entity deletions
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof Category) {
                $this->backlogRemoveCategory($entity->getId());
            }

            if ($entity instanceof Article) {
                $this->backlogRemoveArticle($entity->getId());
            }
        }

        // Entity Insertions
        foreach ($uow->getScheduledEntityInsertions() as $category) {
            if (!($category instanceof Category)) {
                continue;
            }

            $category = $this->setPathForCategory($category);

            $md = $em->getClassMetadata(\get_class($category));
            $uow->recomputeSingleEntityChangeSet($md, $category);
        }

        // Entity updates
        foreach ($uow->getScheduledEntityUpdates() as $category) {
            if (!($category instanceof Category)) {
                continue;
            }

            $changeSet = $uow->getEntityChangeSet($category);

            if (!isset($changeSet['parent'])) {
                continue;
            }

            $oldParentCategory = $changeSet['parent'][0];
            $newParentCategory = $changeSet['parent'][1];

            if (!($oldParentCategory instanceof Category) || (!($newParentCategory instanceof Category))) {
                continue;
            }

            if ((int) $oldParentCategory->getId() === (int) $newParentCategory->getId()) {
                continue;
            }

            $category = $this->setPathForCategory($category);

            $md = $em->getClassMetadata(\get_class($category));
            $uow->recomputeSingleEntityChangeSet($md, $category);

            $this->addPendingMove($category);
        }

        foreach ($uow->getScheduledCollectionDeletions() as $col) {
            if (!$col instanceof PersistentCollection) {
                continue;
            }

            if (!$col->getOwner() instanceof Article) {
                continue;
            }

            foreach ($col->toArray() as $category) {
                if (!$category instanceof Category) {
                    continue;
                }

                $product = $col->getOwner();
                $this->addPendingRemoveAssignment($product, $category);
            }
        }

        foreach ($uow->getScheduledCollectionUpdates() as $col) {
            if (!$col instanceof PersistentCollection) {
                continue;
            }

            if ($col->getOwner() instanceof Article) {
                $product = $col->getOwner();

                foreach ($col->getInsertDiff() as $category) {
                    if (!$category instanceof Category) {
                        continue;
                    }

                    $this->addPendingAddAssignment($product, $category);
                }

                foreach ($col->getDeleteDiff() as $category) {
                    if (!$category instanceof Category) {
                        continue;
                    }

                    $this->addPendingRemoveAssignment($product, $category);
                }
            }

            if ($col->getOwner() instanceof Category) {
                $category = $col->getOwner();

                foreach ($col->getInsertDiff() as $product) {
                    if (!$product instanceof Article) {
                        continue;
                    }

                    $this->addPendingAddAssignment($product, $category);
                }

                foreach ($col->getDeleteDiff() as $product) {
                    if (!$product instanceof Article) {
                        continue;
                    }

                    $this->addPendingRemoveAssignment($product, $category);
                }
            }
        }
    }

    public function postFlush(PostFlushEventArgs $eventArgs)
    {
        if ($this->disabledForNextFlush) {
            $this->disabledForNextFlush = false;

            return;
        }

        // Remove assignments that neutralize each other
        foreach ($this->pendingRemoveAssignments as $key => $pendingRemove) {
            if (isset($this->pendingAddAssignments[$key])) {
                unset($this->pendingAddAssignments[$key]);
                unset($this->pendingRemoveAssignments[$key]);
            }
        }

        foreach ($this->pendingRemoveAssignments as $pendingRemove) {
            $category = $pendingRemove['category'];
            $product = $pendingRemove['article'];

            $this->backlogRemoveAssignment($product->getId(), $category->getId());
        }

        foreach ($this->pendingAddAssignments as $pendingAdd) {
            $category = $pendingAdd['category'];
            $product = $pendingAdd['article'];

            $this->backlogAddAssignment($product->getId(), $category->getId());
        }

        foreach ($this->pendingMoves as $pendingMove) {
            $category = $pendingMove['category'];
            $this->backlogMoveCategory($category->getId());
        }
    }

    /**
     * Sets the internal path field for given category based on it's parents
     *
     * @return Category
     */
    public function setPathForCategory(Category $category)
    {
        $parentId = $category->getParent()->getId();

        $parents = $this->getCategoryComponent()->getParentCategoryIds($parentId);
        $path = implode('|', $parents);
        if (empty($path)) {
            $path = '';
        } else {
            $path = '|' . $path . '|';
        }

        $category->internalSetPath($path);

        return $category;
    }

    /**
     * @param int $articleId
     * @param int $categoryId
     */
    public function backlogRemoveAssignment($articleId, $categoryId)
    {
        $this->getCategoryComponent()->removeAssignment($articleId, $categoryId);
    }

    /**
     * @param int $articleId
     * @param int $categoryId
     */
    public function backlogAddAssignment($articleId, $categoryId)
    {
        $this->getCategoryComponent()->addAssignment($articleId, $categoryId);
    }

    /**
     * @param int $articleId
     */
    public function backlogRemoveArticle($articleId)
    {
        $this->getCategoryComponent()->removeArticleAssignmentments($articleId);
    }

    /**
     * @param int $categoryId
     */
    public function backlogRemoveCategory($categoryId)
    {
        $this->getCategoryComponent()->removeCategoryAssignmentments($categoryId);
    }

    /**
     * @param int $categoryId
     */
    public function backlogMoveCategory($categoryId)
    {
        $component = $this->getCategoryComponent();

        $component->rebuildCategoryPath($categoryId);
        $component->removeOldAssignments($categoryId);
        $component->rebuildAssignments($categoryId);
    }

    protected function addPendingAddAssignment(Article $article, Category $category)
    {
        $this->pendingAddAssignments[$category->getId() . '_' . $article->getId()] = [
            'category' => $category,
            'article' => $article,
        ];
    }

    protected function addPendingRemoveAssignment(Article $article, Category $category)
    {
        $this->pendingRemoveAssignments[$category->getId() . '_' . $article->getId()] = [
            'category' => $category,
            'article' => $article,
        ];
    }

    protected function addPendingMove(Category $category)
    {
        $this->pendingMoves[$category->getId()] = [
            'category' => $category,
        ];
    }
}
