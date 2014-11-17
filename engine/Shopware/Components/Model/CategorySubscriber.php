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

namespace Shopware\Components\Model;

use Doctrine\Common\EventSubscriber as BaseEventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Shopware\Models\Article\Article;
use Shopware\Models\Category\Category;

/**
 * CategorySubscriber
 *
 * @category  Shopware
 * @package   Shopware\Components\Model
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class CategorySubscriber implements BaseEventSubscriber
{
    /**
     * @var ModelManager
     */
    protected $em;

    /**
     * @var CategoryDenormalization
     */
    protected $categoryDenormalization;

    /**
     * @var array
     */
    protected $pendingAddAssignments = array();

    /**
     * @var array
     */
    protected $pendingRemoveAssignments = array();

    /**
     * @var array
     */
    protected $pendingMoves = array();

    /**
     * @var bool
     */
    protected $disabledForNextFlush = false;

    /**
     * @param CategoryDenormalization $categoryDenormalization
     */
    public function __construct(CategoryDenormalization $categoryDenormalization)
    {
        $this->categoryDenormalization = $categoryDenormalization;
    }

    /**
     * @return CategoryDenormalization
     */
    public function getCategoryComponent()
    {
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
     * @param Article  $article
     * @param Category $category
     */
    protected function addPendingAddAssignment(Article $article, Category $category)
    {
        $this->pendingAddAssignments[$category->getId() . '_' . $article->getId()] = array(
            'category' => $category,
            'article'  => $article
        );
    }

    /**
     * @param Article  $article
     * @param Category $category
     */
    protected function addPendingRemoveAssignment(Article $article, Category $category)
    {
        $this->pendingRemoveAssignments[$category->getId() . '_' . $article->getId()] = array(
            'category' => $category,
            'article'  => $article
        );
    }

    /**
     * @param Category $category
     */
    protected function addPendingMove(Category $category)
    {
        $this->pendingMoves[$category->getId()] = array(
            'category' => $category,
        );
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(Events::onFlush, Events::postFlush);
    }

     /**
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        if ($this->disabledForNextFlush) {
            return;
        }

        /** @var $em ModelManager */
        $em  = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        $this->em = $em;

        $this->pendingAddAssignments = array();
        $this->pendingRemoveAssignments = array();

        // Entity deletions
        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            if ($entity instanceof Category) {
                /* @var $entity Category */
                $this->backlogRemoveCategory($entity->getId());
            }

            if ($entity instanceof Article) {
                /* @var $entity Article */
                $this->backlogRemoveArticle($entity->getId());
            }
        }

        // Entity Insertions
        foreach ($uow->getScheduledEntityInsertions() as $category) {

            /* @var $category Category */
            if (!($category instanceof Category)) {
                continue;
            }

            $category = $this->setPathForCategory($category);

            $md = $em->getClassMetadata(get_class($category));
            $uow->recomputeSingleEntityChangeSet($md, $category);
        }

        // Entity updates
        foreach ($uow->getScheduledEntityUpdates() as $category) {

            /* @var $category Category */
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

            if ($oldParentCategory->getId() == $newParentCategory->getId()) {
                continue;
            }

            $category = $this->setPathForCategory($category);

            $md = $em->getClassMetadata(get_class($category));
            $uow->recomputeSingleEntityChangeSet($md, $category);

            $this->addPendingMove($category);
        }

        /* @var $col \Doctrine\ORM\PersistentCollection */
        foreach ($uow->getScheduledCollectionDeletions() as $col) {
            if (!$col->getOwner() instanceof Article) {
                continue;
            }

            foreach ($col->toArray() as $category) {
                if (!$category instanceof Category) {
                    continue;
                }

                /** @var $article Article */
                $article = $col->getOwner();
                $this->addPendingRemoveAssignment($article, $category);
            }
        }

        /* @var $col \Doctrine\ORM\PersistentCollection */
        foreach ($uow->getScheduledCollectionUpdates() as $col) {
            if ($col->getOwner() instanceof Article) {
                /** @var $article Article */
                $article = $col->getOwner();

                foreach ($col->getInsertDiff() as $category) {
                    if (!$category instanceof Category) {
                        continue;
                    }

                    $this->addPendingAddAssignment($article, $category);
                }

                foreach ($col->getDeleteDiff() as $category) {
                    if (!$category instanceof Category) {
                        continue;
                    }

                    $this->addPendingRemoveAssignment($article, $category);
                }
            }

            if ($col->getOwner() instanceof Category) {
                /* @var $category Category */
                $category = $col->getOwner();

                foreach ($col->getInsertDiff() as $article) {
                    if (!$article instanceof Article) {
                        continue;
                    }

                    $this->addPendingAddAssignment($article, $category);
                }

                foreach ($col->getDeleteDiff() as $article) {
                    if (!$article instanceof Article) {
                        continue;
                    }

                    $this->addPendingRemoveAssignment($article, $category);
                }
            }
        }
    }

    /**
     * @param PostFlushEventArgs $eventArgs
     */
    public function postFlush(/** @noinspection PhpUnusedParameterInspection */ PostFlushEventArgs $eventArgs)
    {
        if ($this->disabledForNextFlush) {
            $this->disabledForNextFlush = false;
            return;
        }

        // Remove assignments that noutralize each other
        foreach ($this->pendingRemoveAssignments as $key => $pendingRemove) {
            if (isset($this->pendingAddAssignments[$key])) {
                unset($this->pendingAddAssignments[$key]);
                unset($this->pendingRemoveAssignments[$key]);
            }
        }

        foreach ($this->pendingRemoveAssignments as $pendingRemove) {
            /** @var $category Category */
            $category = $pendingRemove['category'];
            /** @var $article Article */
            $article  = $pendingRemove['article'];

            $this->backlogRemoveAssignment($article->getId(), $category->getId());
        }

        foreach ($this->pendingAddAssignments as $pendingAdd) {
            /** @var $category Category */
            $category = $pendingAdd['category'];
            /** @var $article Article */
            $article  = $pendingAdd['article'];

            $this->backlogAddAssignment($article->getId(), $category->getId());
        }

        foreach ($this->pendingMoves as $pendingMove) {
            /** @var $category Category */
            $category = $pendingMove['category'];
            $this->backlogMoveCategory($category->getId());
        }
    }

    /**
     * Sets the internal path field for given category based on it's parents
     *
     * @param Category $category
     * @return Category
     */
    public function setPathForCategory(Category $category)
    {
        $parent = $category->getParent();
        $parentId = $parent->getId();

        $parents = $this->getCategoryComponent()->getParentCategoryIds($parentId);
        $path = implode('|', $parents);
        if (empty($path)) {
            $path = null;
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
}
