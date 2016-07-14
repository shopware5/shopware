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

use Doctrine\DBAL\Connection;
use ONGR\ElasticsearchDSL\Aggregation\TermsAggregation;
use ONGR\ElasticsearchDSL\Search;
use Shopware\Bundle\SearchBundleES\HandlerInterface;
use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\Facet\CategoryFacet;
use Shopware\Bundle\SearchBundle\CriteriaPartInterface;
use Shopware\Bundle\SearchBundle\FacetResult\TreeFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\TreeItem;
use Shopware\Bundle\SearchBundle\ProductNumberSearchResult;
use Shopware\Bundle\SearchBundleES\ResultHydratorInterface;
use Shopware\Bundle\StoreFrontBundle\Service\CategoryServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Category;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\QueryAliasMapper;

class CategoryFacetHandler implements HandlerInterface, ResultHydratorInterface
{
    const AGGREGATION_SIZE = 1000;

    /**
     * @var CategoryServiceInterface
     */
    private $categoryService;

    /**
     * @var \Shopware_Components_Snippet_Manager
     */
    private $snippetManager;

    /**
     * @var QueryAliasMapper
     */
    private $queryAliasMapper;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param CategoryServiceInterface $categoryService
     * @param \Shopware_Components_Snippet_Manager $snippetManager
     * @param QueryAliasMapper $queryAliasMapper
     * @param Connection $connection
     */
    public function __construct(
        CategoryServiceInterface $categoryService,
        \Shopware_Components_Snippet_Manager $snippetManager,
        QueryAliasMapper $queryAliasMapper,
        Connection $connection
    ) {
        $this->categoryService = $categoryService;
        $this->snippetManager = $snippetManager;
        $this->queryAliasMapper = $queryAliasMapper;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(CriteriaPartInterface $criteriaPart)
    {
        return ($criteriaPart instanceof CategoryFacet);
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

        $ids = $this->getCategoryIds($criteria, $data);

        $categories = $this->categoryService->getList($ids, $context);

        $active = [];
        if ($criteria->hasCondition('category')) {
            /**@var $condition CategoryCondition*/
            $condition = $criteria->getCondition('category');
            $active = $condition->getCategoryIds();
        }

        $criteriaPart = $this->createTreeFacet($categories, $active);
        $result->addFacet($criteriaPart);
    }

    /**
     * @param Category[] $categories
     * @param int[] $active
     * @return TreeFacetResult
     */
    private function createTreeFacet($categories, $active)
    {
        $items = $this->getCategoriesOfParent($categories, null);

        $values = [];
        foreach ($items as $item) {
            $values[] = $this->createTreeItem($categories, $item, $active);
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
            empty($active),
            $label,
            $values,
            [],
            null
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
            $values
        );
    }

    /**
     * @param Criteria $criteria
     * @param $data
     * @return array
     */
    private function getCategoryIds(Criteria $criteria, $data)
    {
        $ids = array_column($data, 'key');

        if ($criteria->hasCondition('category')) {
            /**@var $condition CategoryCondition */
            $condition = $criteria->getCondition('category');
            $parentIds = $condition->getCategoryIds();
        } else {
            $parentIds = [1];
        }

        $query = $this->connection->createQueryBuilder();
        $query->select(['category.id', 'category.path'])
            ->from('s_categories', 'category')
            ->where('category.parent IN (:parent) OR category.id IN (:parent)')
            ->andWhere('category.id IN (:ids)')
            ->andWhere('category.active = 1')
            ->setParameter(':parent', $parentIds, Connection::PARAM_INT_ARRAY)
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $paths = $query->execute()->fetchAll(\PDO::FETCH_KEY_PAIR);

        $ids = array_keys($paths);
        $plain = array_values($paths);

        if (count($plain) > 0 && strpos($plain[0], '|') !== false) {
            $rootPath = explode('|', $plain[0]);
            $rootPath = array_filter(array_unique($rootPath));
            $ids = array_merge($ids, $rootPath);
            return $ids;
        }

        return $ids;
    }
}
