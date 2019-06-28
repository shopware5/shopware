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

namespace Shopware\Bundle\SearchBundleDBAL\FacetHandler;

use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet\CategoryFacet;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\SearchBundle\FacetResult\CategoryTreeFacetResultBuilder;
use Shopware\Bundle\SearchBundle\FacetResultInterface;
use Shopware\Bundle\SearchBundleDBAL\PartialFacetHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactoryInterface;
use Shopware\Bundle\StoreFrontBundle\Service\CategoryDepthServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\CategoryServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class CategoryFacetHandler implements PartialFacetHandlerInterface
{
    /**
     * @var CategoryServiceInterface
     */
    private $categoryService;

    /**
     * @var QueryBuilderFactoryInterface
     */
    private $queryBuilderFactory;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var CategoryDepthServiceInterface
     */
    private $categoryDepthService;

    /**
     * @var CategoryTreeFacetResultBuilder
     */
    private $categoryTreeFacetResultBuilder;

    public function __construct(
        CategoryServiceInterface $categoryService,
        QueryBuilderFactoryInterface $queryBuilderFactory,
        \Shopware_Components_Config $config,
        CategoryDepthServiceInterface $categoryDepthService,
        CategoryTreeFacetResultBuilder $categoryTreeFacetResultBuilder
    ) {
        $this->categoryService = $categoryService;
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->config = $config;
        $this->categoryDepthService = $categoryDepthService;
        $this->categoryTreeFacetResultBuilder = $categoryTreeFacetResultBuilder;
    }

    /**
     * @param FacetInterface|CategoryFacet $facet
     *
     * @return FacetResultInterface|null
     */
    public function generatePartialFacet(
        FacetInterface $facet,
        Criteria $reverted,
        Criteria $criteria,
        ShopContextInterface $context
    ) {
        $ids = $this->fetchCategoriesOfProducts($reverted, $context);

        if (empty($ids)) {
            return null;
        }

        $ids = $this->filterSystemCategories($ids, $context);

        $ids = $this->categoryDepthService->get(
            $context->getShop()->getCategory(),
            $facet->getDepth(),
            $ids
        );

        $categories = $this->categoryService->getList($ids, $context);

        return $this->categoryTreeFacetResultBuilder->buildFacetResult(
            $categories,
            $this->getFilteredIds($criteria),
            $context->getShop()->getCategory()->getId(),
            $facet
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsFacet(FacetInterface $facet)
    {
        return $facet instanceof CategoryFacet;
    }

    /**
     * @return array
     */
    private function filterSystemCategories(array $ids, ShopContextInterface $context)
    {
        $system = array_merge(
            [$context->getShop()->getCategory()->getId()],
            $context->getShop()->getCategory()->getPath()
        );

        return array_filter($ids, function ($id) use ($system) {
            return !in_array($id, $system);
        });
    }

    /**
     * @return int[]
     */
    private function fetchCategoriesOfProducts(Criteria $reverted, ShopContextInterface $context)
    {
        $query = $this->queryBuilderFactory->createQuery($reverted, $context);
        $query->resetQueryPart('orderBy');
        $query->resetQueryPart('groupBy');
        $query->select(['productCategoryFacet.categoryID']);
        $query->innerJoin('product', 's_articles_categories_ro', 'productCategoryFacet', 'productCategoryFacet.articleID = product.id');
        $query->groupBy('productCategoryFacet.categoryID');

        return $query->execute()->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * @return int[]
     */
    private function getFilteredIds(Criteria $criteria)
    {
        $active = [];
        foreach ($criteria->getUserConditions() as $condition) {
            if ($condition instanceof CategoryCondition) {
                $active = array_merge($active, $condition->getCategoryIds());
            }
        }

        return $active;
    }
}
