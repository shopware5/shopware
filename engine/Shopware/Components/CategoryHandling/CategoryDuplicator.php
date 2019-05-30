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

use Shopware\Bundle\AttributeBundle\Service\DataPersister;
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
     * @var DataPersister
     */
    private $attributePersister;

    public function __construct(
        \PDO $connection,
        CategoryDenormalization $categoryDenormalization,
        DataPersister $attributePersister
    ) {
        $this->connection = $connection;
        $this->categoryDenormalization = $categoryDenormalization;
        $this->attributePersister = $attributePersister;
    }

    /**
     * Duplicates the provided category into the provided parent category
     *
     * @param int  $originalCategoryId
     * @param int  $parentId
     * @param bool $copyArticleAssociations
     *
     * @throws \RuntimeException
     *
     * @return int
     */
    public function duplicateCategory($originalCategoryId, $parentId, $copyArticleAssociations)
    {
        $originalCategoryStmt = $this->connection
            ->prepare('SELECT * FROM s_categories WHERE id = :id');
        $originalCategoryStmt->execute([':id' => $originalCategoryId]);
        $originalCategory = $originalCategoryStmt->fetch(\PDO::FETCH_ASSOC);

        if (empty($originalCategory)) {
            throw new \RuntimeException(sprintf('Category "%s" not found', $originalCategoryId));
        }

        $newPosStmt = $this->connection
            ->prepare('SELECT MAX(`position`) FROM s_categories WHERE parent = :parent');
        $newPosStmt->execute([':parent' => $parentId]);
        $newPos = (int) $newPosStmt->fetchColumn();
        $originalCategory['position'] = $newPos + 1;

        $originalCategory['parent'] = $parentId;

        unset($originalCategory['id'], $originalCategory['path']);

        $valuePlaceholders = array_fill(0, count($originalCategory), '?');
        $insertStmt = $this->connection->prepare(
            'INSERT INTO s_categories (`' . implode(array_keys($originalCategory), '`, `') . '`)
            VALUES (' . implode($valuePlaceholders, ', ') . ')'
        );
        $insertStmt->execute(array_values($originalCategory));
        $newCategoryId = (int) $this->connection->lastInsertId();

        $this->rebuildPath($newCategoryId);

        $this->duplicateCategoryAttributes($originalCategoryId, $newCategoryId);
        $this->duplicateCategoryRestrictions($originalCategoryId, $newCategoryId);

        if ($copyArticleAssociations) {
            $this->duplicateCategoryArticleAssociations($originalCategoryId, $newCategoryId);
        }

        return $newCategoryId;
    }

    /**
     * Duplicates the category restrictions from one category to another
     *
     * @param int $originalCategoryId
     * @param int $newCategoryId
     */
    public function duplicateCategoryRestrictions($originalCategoryId, $newCategoryId)
    {
        $stmt = $this->connection->prepare(
            'INSERT INTO s_categories_avoid_customergroups (`categoryID`, `customergroupID`)
            SELECT :newCategoryID, `customergroupID`
            FROM s_categories_avoid_customergroups WHERE categoryID = :categoryID'
        );
        $stmt->execute(
            [
                ':newCategoryID' => $newCategoryId,
                ':categoryID' => $originalCategoryId,
            ]
        );
    }

    /**
     * Duplicates the category attributes from one category to another
     *
     * @param int $originalCategoryId
     * @param int $newCategoryId
     */
    public function duplicateCategoryAttributes($originalCategoryId, $newCategoryId)
    {
        $this->attributePersister->cloneAttribute(
            's_categories_attributes',
            $originalCategoryId,
            $newCategoryId
        );
    }

    /**
     * Duplicates the category product associations from one category to another
     *
     * @param int $originalCategoryId
     * @param int $newCategoryId
     */
    public function duplicateCategoryArticleAssociations($originalCategoryId, $newCategoryId)
    {
        $assocProductsStmt = $this->connection->prepare(
            'SELECT articleID FROM s_articles_categories WHERE categoryID = :categoryID'
        );
        $assocProductsStmt->execute([':categoryID' => $originalCategoryId]);
        $products = $assocProductsStmt->fetchAll(\PDO::FETCH_COLUMN, 0);

        if ($products) {
            $insertStmt = $this->connection->prepare(
                'INSERT INTO s_articles_categories (categoryID, articleID)
            VALUES (' . $newCategoryId . ', ' . implode($products, '), (' . $newCategoryId . ', ') . ')'
            );
            $insertStmt->execute();

            foreach ($products as $productId) {
                $this->categoryDenormalization->addAssignment($productId, $newCategoryId);
            }
        }
    }

    /**
     * Rebuilds the path for a single category
     *
     * @param int    $categoryId
     * @param string $categoryPath
     *
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
            $path = '|' . $path . '|';
        }

        if ($categoryPath != $path) {
            $updateStmt->execute([':path' => $path, ':categoryId' => $categoryId]);

            return 1;
        }

        return 0;
    }
}
