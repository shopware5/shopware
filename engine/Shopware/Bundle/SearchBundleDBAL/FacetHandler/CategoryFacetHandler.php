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

use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundle\Condition\CategoryCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\FacetResult\TreeFacetResult;
use Shopware\Bundle\SearchBundle\FacetResult\TreeItem;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactory;
use Shopware\Bundle\SearchBundle\Facet;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\SearchBundleDBAL\FacetHandlerInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Category;
use Shopware\Bundle\StoreFrontBundle\Service\CategoryServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\QueryAliasMapper;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundleDBAL\FacetHandler
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class CategoryFacetHandler implements FacetHandlerInterface
{
    /**
     * @var CategoryServiceInterface
     */
    private $categoryService;

    /**
     * @var QueryBuilderFactory
     */
    private $queryBuilderFactory;

    /**
     * @var \Enlight_Components_Snippet_Namespace
     */
    private $snippetNamespace;

    /**
     * @var string
     */
    private $fieldName;
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param CategoryServiceInterface $categoryService
     * @param QueryBuilderFactory $queryBuilderFactory
     * @param \Shopware_Components_Snippet_Manager $snippetManager
     * @param QueryAliasMapper $queryAliasMapper
     * @param Connection $connection
     */
    public function __construct(
        CategoryServiceInterface $categoryService,
        QueryBuilderFactory $queryBuilderFactory,
        \Shopware_Components_Snippet_Manager $snippetManager,
        QueryAliasMapper $queryAliasMapper,
        Connection $connection
    ) {
        $this->categoryService = $categoryService;
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->snippetNamespace = $snippetManager->getNamespace('frontend/listing/facet_labels');
        $this->connection = $connection;

        if (!$this->fieldName = $queryAliasMapper->getShortAlias('sCategory')) {
            $this->fieldName = 'sCategory';
        }
    }

    /**
     * Generates the facet for the \Shopware\Bundle\SearchBundle\Facet;\Category class.
     * Displays how many products are assigned to the children categories.
     *
     * The handler use the category ids of the \Shopware\Bundle\SearchBundle\Condition\Category.
     * If no \Shopware\Bundle\SearchBundle\Condition\Category is set, the handler uses as default the id 1.
     *
     * @param FacetInterface|Facet\CategoryFacet $facet
     * @param Criteria $criteria
     * @param ShopContextInterface $context
     * @return TreeFacetResult
     */
    public function generateFacet(
        FacetInterface $facet,
        Criteria $criteria,
        ShopContextInterface $context
    ) {
        $ids = $this->getCategoryIds($criteria, $context);
        $categories = $this->categoryService->getList($ids, $context);

        $active = [];
        if ($criteria->hasCondition('category')) {
            /**@var $condition CategoryCondition*/
            $condition = $criteria->getCondition('category');
            $active = $condition->getCategoryIds();
        }

        return $this->createTreeFacet($categories, $facet, $active);
    }

    /**
     * @param Category[] $categories
     * @param Facet\CategoryFacet $facet
     * @param int[] $active
     * @return TreeFacetResult
     */
    private function createTreeFacet($categories, $facet, $active)
    {
        $items = $this->getCategoriesOfParent($categories, null);
        if (!$items) {
            return null;
        }

        $values = [];
        foreach ($items as $item) {
            $values[] = $this->createTreeItem($categories, $item, $active);
        }

        return new TreeFacetResult(
            $facet->getName(),
            $this->fieldName,
            empty($active),
            $this->snippetNamespace->get($facet->getName(), 'Categories'),
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
        $children = $this->getCategoriesOfParent(
            $categories,
            $category->getId()
        );

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
     * {@inheritdoc}
     */
    public function supportsFacet(FacetInterface $facet)
    {
        return ($facet instanceof Facet\CategoryFacet);
    }

    /**
     * @param Criteria $criteria
     * @param ShopContextInterface $context
     * @return array
     */
    private function getCategoryIds(Criteria $criteria, ShopContextInterface $context)
    {
        $queryCriteria = clone $criteria;
        $queryCriteria->resetConditions();
        $queryCriteria->resetSorting();

        $queryCriteria->removeBaseCondition('category');
        $queryCriteria->removeCondition('category');

        $query = $this->queryBuilderFactory->createQuery($queryCriteria, $context);

        $query->resetQueryPart('orderBy');
        $query->resetQueryPart('groupBy');

        $query->select(['productCategory.categoryID']);

        $query->innerJoin(
            'product',
            's_articles_categories_ro',
            'productCategory',
            'productCategory.articleID = product.id'
        );

        $query->groupBy('productCategory.categoryID');

        if ($criteria->hasCondition('category')) {
            /**@var $condition CategoryCondition */
            $condition = $criteria->getCondition('category');
            $parentIds = $condition->getCategoryIds();
        } else {
            $parentIds = [1];
        }

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        /**@var $facet Facet\CategoryFacet */
        $ids = $statement->fetchAll(\PDO::FETCH_COLUMN);

        $query = $this->connection->createQueryBuilder();
        $query->select(['category.id', 'category.path'])
            ->from('s_categories', 'category')
            ->where('category.parent IN (:parent) OR category.id IN (:parent)')
            ->andWhere('category.id IN (:ids)')
            ->andWhere('category.active = 1')
            ->setParameter(':parent', $parentIds, Connection::PARAM_INT_ARRAY)
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY)
        ;

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
