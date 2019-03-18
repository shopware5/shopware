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

namespace Shopware\Bundle\SearchBundleES\FacetHandler;

use ONGR\ElasticsearchDSL\Aggregation\Bucketing\TermsAggregation;
use ONGR\ElasticsearchDSL\Search;
use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;
use Shopware\Bundle\SearchBundle\Facet\CategoryFacet;
use Shopware\Bundle\SearchBundle\FacetResult\CategoryTreeFacetResultBuilder;
use Shopware\Bundle\SearchBundle\FacetResultInterface;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\SearchBundleES\HandlerInterface;
use Shopware\Bundle\SearchBundleES\ResultHydratorInterface;
use Shopware\Bundle\StoreFrontBundle\Service\CategoryServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\Core\CategoryDepthService;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class CategoryFacetHandler implements HandlerInterface, ResultHydratorInterface
{
    const AGGREGATION_SIZE = 1000;

    /**
     * @var CategoryServiceInterface
     */
    private $categoryService;

    /**
     * @var CategoryDepthService
     */
    private $categoryDepthService;

    /**
     * @var CategoryTreeFacetResultBuilder
     */
    private $categoryTreeFacetResultBuilder;

    public function __construct(
        CategoryServiceInterface $categoryService,
        CategoryDepthService $categoryDepthService,
        CategoryTreeFacetResultBuilder $categoryTreeFacetResultBuilder
    ) {
        $this->categoryService = $categoryService;
        $this->categoryDepthService = $categoryDepthService;
        $this->categoryTreeFacetResultBuilder = $categoryTreeFacetResultBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CriteriaPartInterface $criteriaPart)
    {
        return $criteriaPart instanceof CategoryFacet;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(
        CriteriaPartInterface $criteriaPart,
        Criteria $criteria,
        Search $search,
        ShopContextInterface $context
    ) {
        $aggregation = new TermsAggregation('category');
        $aggregation->setField('categoryIds');
        $aggregation->addParameter('size', self::AGGREGATION_SIZE);
        $search->addAggregation($aggregation);
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate(
        array $elasticResult,
        ProductNumberSearchResult $result,
        Criteria $criteria,
        ShopContextInterface $context
    ) {
        if (!isset($elasticResult['aggregations']) || !isset($elasticResult['aggregations']['category'])) {
            return;
        }

        $data = $elasticResult['aggregations']['category']['buckets'];
        $ids = array_column($data, 'key');

        if (empty($ids)) {
            return;
        }

        /** @var CategoryFacet $categoryFacet */
        $categoryFacet = $criteria->getFacet('category');

        $ids = $this->filterSystemCategories($ids, $context);
        $ids = $this->categoryDepthService->get(
            $context->getShop()->getCategory(),
            $categoryFacet->getDepth(),
            $ids
        );

        $categories = $this->categoryService->getList($ids, $context);

        $facet = $this->categoryTreeFacetResultBuilder->buildFacetResult(
            $categories,
            $this->getFilteredIds($criteria),
            $context->getShop()->getCategory()->getId(),
            $categoryFacet
        );

        if (!$facet instanceof FacetResultInterface) {
            return;
        }
        $result->addFacet($facet);
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
