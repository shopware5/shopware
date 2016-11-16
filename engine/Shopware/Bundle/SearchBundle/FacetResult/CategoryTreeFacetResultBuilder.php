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

namespace Shopware\Bundle\SearchBundle\FacetResult;

use Shopware\Bundle\StoreFrontBundle\Struct\Category;
use Shopware\Components\QueryAliasMapper;

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

    /**
     * @param \Shopware_Components_Snippet_Manager $snippetManager
     * @param QueryAliasMapper $queryAliasMapper
     */
    public function __construct(
        \Shopware_Components_Snippet_Manager $snippetManager,
        QueryAliasMapper $queryAliasMapper
    ) {
        $this->snippetManager = $snippetManager;
        $this->queryAliasMapper = $queryAliasMapper;
    }

    /**
     * @param Category[] $categories
     * @param int[] $activeIds
     * @param int $systemCategoryId
     * @return null|TreeFacetResult
     */
    public function buildFacetResult(array $categories, $activeIds, $systemCategoryId)
    {
        $items = $this->getCategoriesOfParent($categories, $systemCategoryId);

        if (!$items) {
            return null;
        }

        $values = [];
        foreach ($items as $item) {
            $values[] = $this->createTreeItem($categories, $item, $activeIds);
        }

        $label = $this->snippetManager
            ->getNamespace('frontend/listing/facet_labels')
            ->get('category', 'Categories');

        if (!$fieldName = $this->queryAliasMapper->getShortAlias('sCategory')) {
            $fieldName = 'sCategory';
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
     * @param Category[] $categories
     * @param $parentId
     * @return array
     */
    private function getCategoriesOfParent($categories, $parentId)
    {
        $result = [];

        foreach ($categories as $category) {
            if (!$category->getPath() && $parentId !== null) {
                continue;
            }

            if ($category->getPath() == $parentId) {
                $result[] = $category;
                continue;
            }

            $parents = $category->getPath();
            $lastParent = $parents[count($parents) - 1];

            if ($lastParent == $parentId) {
                $result[] = $category;
            }
        }

        return $result;
    }

    /**
     * @param Category[] $categories
     * @param Category $category
     * @param int[] $active
     * @return \Shopware\Bundle\SearchBundle\FacetResult\TreeItem
     */
    private function createTreeItem($categories, Category $category, $active)
    {
        $children = $this->getCategoriesOfParent($categories, $category->getId());

        $values = [];
        foreach ($children as $child) {
            $values[] = $this->createTreeItem($categories, $child, $active);
        }

        return new TreeItem(
            $category->getId(),
            $category->getName(),
            in_array($category->getId(), $active),
            $values,
            $category->getAttributes()
        );
    }
}
