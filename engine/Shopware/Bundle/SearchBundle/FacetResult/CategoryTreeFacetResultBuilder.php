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

use Shopware\Bundle\SearchBundle\Facet\CategoryFacet;
use Shopware\Bundle\StoreFrontBundle\Category\Category;
use Shopware\Bundle\StoreFrontBundle\Category\CategoryCollection;
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
     * @param QueryAliasMapper                     $queryAliasMapper
     */
    public function __construct(
        \Shopware_Components_Snippet_Manager $snippetManager,
        QueryAliasMapper $queryAliasMapper
    ) {
        $this->snippetManager = $snippetManager;
        $this->queryAliasMapper = $queryAliasMapper;
    }

    /**
     * @param Category[]    $categories
     * @param int[]         $activeIds
     * @param int           $systemCategoryId
     * @param CategoryFacet $facet
     *
     * @return null|TreeFacetResult
     */
    public function buildFacetResult(array $categories, array $activeIds, $systemCategoryId, CategoryFacet $facet)
    {
        $collection = new CategoryCollection($categories);

        $values = $this->convertItems(
            $collection->getTree($systemCategoryId),
            $activeIds
        );

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
     * @param Category[] $categories
     * @param int[]      $activeIds
     *
     * @return TreeItem[]
     */
    private function convertItems(array $categories, $activeIds)
    {
        $items = [];

        foreach ($categories as $category) {
            $children = $this->convertItems($category->getChildren(), $activeIds);

            $items[] = new TreeItem(
                $category->getId(),
                $category->getName(),
                in_array($category->getId(), $activeIds),
                $children,
                $category->getAttributes()
            );
        }

        return $items;
    }
}
