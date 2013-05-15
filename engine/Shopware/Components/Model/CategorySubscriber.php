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
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class CategorySubscriber implements BaseEventSubscriber
{
    /**
     * @var ModelManager
     */
    protected $em;

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

        // Entity inserts/updates
        $entities = array_merge(
            $uow->getScheduledEntityInsertions(),
            $uow->getScheduledEntityUpdates()
        );

        foreach ($entities as $entity) {

            if (!($entity instanceof Category)) {
                continue;
            }

            /* @var $category Category */
            $category = $entity;

            $changeSet = $uow->getEntityChangeSet($category);
            if (isset($changeSet['parent'])) {
                $oldParentCategory = $changeSet['parent'][0];
                $newParentCategory = $changeSet['parent'][1];

                if (($oldParentCategory instanceof Category && $newParentCategory instanceof Category)
                    && ($oldParentCategory->getId() != $newParentCategory->getId())
                ) {
                    $this->addPendingMove($category);
                }
            }

            $parent = $category->getParent();
            $parentId = $parent->getId();

            $parents = $em->getParentCategories($parentId);
            $path = implode('|', $parents);
            if (empty($path)) {
                $path = null;
            } else {
                $path = '|' . $path . '|';
            }

            $category->internalSetPath($path);

            $md = $em->getClassMetadata(get_class($category));
            $uow->recomputeSingleEntityChangeSet($md, $category);
        }

        /* @var $col \Doctrine\ORM\PersistentCollection */
        foreach ($uow->getScheduledCollectionDeletions() AS $col) {
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
        foreach ($uow->getScheduledCollectionUpdates() AS $col) {

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
        // Remove assignments that noutralize each other
        foreach ($this->pendingRemoveAssignments as $key => $pendingRemove) {
            if (isset($this->pendingAddAssignments[$key])) {
                unset($this->pendingAddAssignments[$key]);
                unset($this->pendingRemoveAssignments[$key]);
            }
        }

        foreach ($this->pendingRemoveAssignments as $pendingRemove) {
            /** @var $category Category */
            $category =  $pendingRemove['category'];
            /** @var $article Article */
            $article  =  $pendingRemove['article'];

            $this->backlogRemoveAssignment($article->getId(), $category->getId());
        }

        foreach ($this->pendingAddAssignments as $pendingAdd) {
            /** @var $category Category */
            $category =  $pendingAdd['category'];
            /** @var $article Article */
            $article  =  $pendingAdd['article'];

            $this->backlogAddAssignment($article->getId(), $category->getId());
        }

        foreach ($this->pendingMoves as $pendingMove) {
            /** @var $category Category */
            $category = $pendingMove['category'];
            $this->backlogMoveCategory($category->getId());
        }
    }

    /**
     * @param int $articleId
     * @param int $categoryId
     */
    public function backlogRemoveAssignment($articleId, $categoryId)
    {
        $deleteQuery = "
            DELETE FROM s_articles_categories_ro
            WHERE parentCategoryID = :categoryId
            AND articleId = :articleId
        ";

        Shopware()->Db()
                  ->query($deleteQuery, array('categoryId' => $categoryId, 'articleId' => $articleId))
                  ->execute();
    }

    /**
     * @param int $articleId
     * @param int $categoryId
     */
    public function backlogAddAssignment($articleId, $categoryId)
    {
        $parents = $this->em->getParentCategories($categoryId);

        foreach ($parents as $parent) {
            Shopware()->Db()->insert('s_articles_categories_ro', array(
                'articleID'        => $articleId,
                'categoryID'       => $parent,
                'parentCategoryID' => $categoryId,
            ));
        }
    }

    /**
     * @param int $articleId
     */
    public function backlogRemoveArticle($articleId)
    {
        $deleteQuery = "
            DELETE
            FROM s_articles_categories_ro
            WHERE articleID = :articleId
        ";

        Shopware()->Db()
                  ->query($deleteQuery, array('articleId' => $articleId))
                  ->execute();
    }

    /**
     * @param int $categoryId
     */
    public function backlogRemoveCategory($categoryId)
    {
        $deleteQuery = "
            DELETE ac1
            FROM s_articles_categories_ro ac0
            INNER JOIN s_articles_categories_ro ac1
                ON ac0.parentCategoryID = ac1.parentCategoryID
                AND ac0.id != ac1.id
            WHERE ac0.categoryID = :categoryId
        ";

        Shopware()->Db()
                  ->query($deleteQuery, array('categoryId' => $categoryId))
                  ->execute();
    }

    /**
     * @param $categoryIds
     */
    public function fixPathForCategories($categoryIds)
    {
        Shopware()->Db()->beginTransaction();
        foreach ($categoryIds as $categoryId) {
            $parents = $this->em->getParentCategories($categoryId);

            array_shift($parents);
            $path = '|' . implode('|', $parents) . '|';

            Shopware()->Db()
                      ->query('UPDATE s_categories set path = :path WHERE id = :categoryId',array('path' => $path, 'categoryId' => $categoryId)                      )
                      ->execute();

        }
        Shopware()->Db()->commit();
    }

    /**
     * @param int $categoryId
     */
    public function backlogMoveCategory($categoryId)
    {
        // Fix path for child-categories
        $sql = "
            SELECT c.id
            FROM  `s_categories` c
            WHERE c.path LIKE :categoryId
            GROUP BY c.id
        ";

        $childCategories = Shopware()->Db()->fetchCol($sql, array('categoryId' => '%|' . $categoryId . '|%'));
        $this->fixPathForCategories($childCategories);

        // delete assignments
        $deleteQuery = "
            DELETE ac1
            FROM s_articles_categories_ro ac0
            INNER JOIN s_articles_categories_ro ac1
                ON ac0.parentCategoryID = ac1.parentCategoryID
                AND ac0.id != ac1.id
            WHERE ac0.categoryID = :categoryId
                AND ac1.categoryID <> ac1.parentCategoryID
        ";

        Shopware()->Db()
                  ->query($deleteQuery, array('categoryId' => $categoryId))
                  ->execute();

        // Fetch affected categories
        $sql = "
            SELECT c.id
            FROM  `s_categories` c
            INNER JOIN s_articles_categories ac ON ac.categoryID = c.id
            WHERE c.path LIKE :categoryId
            GROUP BY c.id
        ";

        $affectedCategories = Shopware()->Db()->fetchCol($sql, array('categoryId' => '%|' . $categoryId . '|%'));

        $selectQuery = "SELECT articleID, categoryID FROM `s_articles_categories` WHERE categoryID = :categoryId";
        $assignmentSql = "SELECT id FROM s_articles_categories_ro c WHERE c.categoryID = :categoryId AND c.articleID = :articleId AND c.parentCategoryID = :parentCategoryId";
        $assignmentStmt = Shopware()->Db()->prepare($assignmentSql);

        Shopware()->Db()->beginTransaction();
        foreach ($affectedCategories as $categoryId) {
            $assignments = Shopware()->Db()->query($selectQuery, array('categoryId' => $categoryId));
            while ($assignment = $assignments->fetch()) {
                $parents = $this->em->getParentCategories($assignment['categoryID']);

                foreach ($parents as $parent) {

                    $assignmentStmt->execute(array(
                        'categoryId'       => $parent,
                        'articleId'        => $assignment['articleID'],
                        'parentCategoryId' => $assignment['categoryID'],
                    ));

                    if ($assignmentStmt->fetchColumn() === false) {
                        Shopware()->Db()->insert('s_articles_categories_ro', array(
                            'articleID'        => $assignment['articleID'],
                            'categoryID'       => $parent,
                            'parentCategoryID' => $assignment['categoryID'],
                        ));
                    }
                }
            }
        }
        Shopware()->Db()->commit();
    }
}
