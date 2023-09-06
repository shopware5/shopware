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

namespace Shopware\Bundle\SearchBundle\FacetResult;

use Shopware\Bundle\SearchBundle\Facet\CategoryFacet;
use Shopware\Bundle\StoreFrontBundle\Struct\Category;
use Shopware\Components\QueryAliasMapper;
use Shopware_Components_Snippet_Manager;

class CategoryTreeFacetResultBuilder
{
    /*
     * @var \Shopware_Components_Snippet_Manager
     */
    private $snippetManager;

    /**
     * @var QueryAliasMapper
     */
    private $queryAliasMapper;

    public function __construct(
        Shopware_Components_Snippet_Manager $snippetManager,
        QueryAliasMapper $queryAliasMapper
    ) {
        $this->snippetManager = $snippetManager;
        $this->queryAliasMapper = $queryAliasMapper;
    }

    /**
     * @param Category[] $categories
     * @param int[]      $activeIds
     * @param int        $systemCategoryId
     *
     * @return TreeFacetResult|null
     */
    public function buildFacetResult(array $categories, array $activeIds, $systemCategoryId, CategoryFacet $facet)
    {
        $items = $this->getCategoriesOfParent($categories, $systemCategoryId);

        if (!$items) {
            return null;
        }

        $values = [];
        foreach ($items as $item) {
            $values[] = $this->createTreeItem($categories, $item, $activeIds);
        }

        if (!empty($facet->getLabel())) {
            $label = $facet->getLabel();
        } else {
            $label = $this->snippetManager
                ->getNamespace('frontend/listing/facet_labels')
                ->get('category', 'Categories');
        }

        if (!$fieldName = $this->queryAliasMapper->getShortAlias('categoryFilter')) {
            $fieldName = 'categoryFilter';
        }

        return new TreeFacetResult(
            'category',
            $fieldName,
            !empty($activeIds),
            $label,
            $values
        );
    }

    /**
     * @param array<Category> $categories
     *
     * @return array<Category>
     */
    private function getCategoriesOfParent(array $categories, int $parentId): array
    {
        $result = [];

        foreach ($categories as $category) {
            if (!$category->getPath()) {
                continue;
            }

            $parents = $category->getPath();
            $lastParent = $parents[array_key_last($parents)];

            if ($lastParent === $parentId) {
                $result[] = $category;
            }
        }

        return $result;
    }

    /**
     * @param Category[] $categories
     * @param int[]      $actives
     *
     * @return \Shopware\Bundle\SearchBundle\FacetResult\TreeItem
     */
    private function createTreeItem(array $categories, Category $category, array $actives = [])
    {
        $children = $this->getCategoriesOfParent($categories, $category->getId());

        $values = [];
        foreach ($children as $child) {
            $values[] = $this->createTreeItem($categories, $child, $actives);
        }

        return new TreeItem(
            $category->getId(),
            $category->getName(),
            \in_array($category->getId(), $actives),
            $values,
            $category->getAttributes()
        );
    }
}
