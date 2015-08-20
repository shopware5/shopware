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

namespace Shopware\Components\CategoryHandling;

use Shopware\Components\Model\CategoryDenormalization;

class CategoryDuplicator
{
    /**
     * @var \PDO
     */
    protected $connection;

    /**
     * @var CategoryDenormalization
     */
    protected $categoryDenormalization;

    /**
     * @param \PDO $connection
     * @param CategoryDenormalization $categoryDenormalization
     */
    public function __construct(\PDO $connection, CategoryDenormalization $categoryDenormalization)
    {
        $this->connection = $connection;
        $this->categoryDenormalization = $categoryDenormalization;
    }
    
    /**
     * Duplicates the provided category into the provided parent category
     *
     * @param int $originalCategoryId
     * @param int $parentId
     * @return int
     */
    public function duplicateCategory($originalCategoryId, $parentId)
    {
        $originalCategoryStmt = $this->connection
            ->prepare('SELECT * FROM s_categories WHERE id = :id');
        $originalCategoryStmt->execute([':id' => $originalCategoryId]);
        $originalCategory = $originalCategoryStmt->fetch(\PDO::FETCH_ASSOC);

        if (empty($originalCategory)) {
            return;
        }

        if ($parentId == $originalCategory['parent']) {
            $newPosStmt = $this->connection
                ->prepare('SELECT MAX(`position`) FROM s_categories WHERE parent = :parent');
            $newPosStmt->execute([':parent' => $parentId]);
            $newPos = $newPosStmt->fetchColumn(0);
            $originalCategory['position'] = $newPos+1;
        }

        $originalCategory['parent'] = $parentId;

        unset($originalCategory['id']);
        unset($originalCategory['path']);

        $valuePlaceholders = array_fill(0, count($originalCategory),  '?');
        $insertStmt = $this->connection->prepare(
            "INSERT INTO s_categories (`" . implode(array_keys($originalCategory), "`, `") . "`)
            VALUES (" . implode($valuePlaceholders, ", ") . ")"
        );
        $insertStmt->execute(array_values($originalCategory));
        $newCategoryId = $this->connection->lastInsertId();

        $this->rebuildPath($newCategoryId);
        $this->categoryDenormalization->rebuildAssignments($newCategoryId);

        return $newCategoryId;
    }

    /**
     * Duplicates the category article associations from one category to another
     *
     * @param $originalCategoryId
     * @param $newCategoryId
     */
    public function duplicateCategoryArticleAssociations($originalCategoryId, $newCategoryId)
    {
        $assocArticlesStmt = $this->connection->prepare(
            'SELECT articleID FROM s_articles_categories WHERE categoryID = :categoryID'
        );
        $assocArticlesStmt->execute([':categoryID' => $originalCategoryId]);
        $articles = $assocArticlesStmt->fetchAll(\PDO::FETCH_COLUMN, 0);

        if ($articles) {
            $insertStmt = $this->connection->prepare(
                "INSERT INTO s_articles_categories (categoryID, articleID)
            VALUES (" .$newCategoryId .", " . implode($articles, "), (" . $newCategoryId . ", ") . ")"
            );
            $insertStmt->execute();
        }
    }


    /**
     * Sets path for categories with empty paths
     *
     * @param  int $count
     * @return int
     */
    public function buildEmptyCategoryPath($count = null)
    {
        $sql = "
            SELECT id, path
            FROM  s_categories
            WHERE (path LIKE '' OR path IS NULL)
            AND parent > 1
        ";


        if ($count !== null) {
            $sql = $this->limit($sql, $count);
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        $categories = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $count = 0;

        $this->beginTransaction();

        foreach ($categories as $category) {
            $count += $this->rebuildPath($category['id'], $category['path']);
        }

        $this->commit();

        return $count;
    }

    /**
     * Rebuilds the path for a single category
     *
     * @param $categoryId
     * @param $categoryPath
     * @return int
     */
    public function rebuildPath($categoryId, $categoryPath = null)
    {
        $updateStmt = $this->connection->prepare('UPDATE s_categories set path = :path WHERE id = :categoryId');

        $parents = $this->categoryDenormalization->getParentCategoryIds($categoryId);
        array_shift($parents);

        if (empty($parents)) {
            $path = null;
        } else {
            $path = implode('|', $parents);
            $path = '|'.$path.'|';
        }

        if ($categoryPath != $path) {
            $updateStmt->execute(array(':path' => $path, ':categoryId' => $categoryId));
            return 1;
        }

        return 0;
    }

}
