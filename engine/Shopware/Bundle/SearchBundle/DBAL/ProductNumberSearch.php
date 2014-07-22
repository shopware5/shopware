<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

namespace Shopware\Bundle\SearchBundle\DBAL;

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Bundle\SearchBundle;
use Shopware\Bundle\StoreFrontBundle\Struct\Context;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\AttributeHydrator;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\SearchBundle\DBAL
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ProductNumberSearch implements SearchBundle\ProductNumberSearchInterface
{
    /**
     * @var \Shopware\Bundle\SearchBundle\DBAL\QueryBuilderFactory
     */
    private $queryBuilderFactory;

    /**
     * @var ConditionHandlerInterface[]
     */
    private $conditionHandlers;

    /**
     * @var FacetHandlerInterface[]
     */
    private $facetHandlers;

    /**
     * @var SortingHandlerInterface[]
     */
    private $sortingHandlers;

    /**
     * @var AttributeHydrator
     */
    private $attributeHydrator;

    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @param QueryBuilderFactory         $queryBuilderFactory
     * @param AttributeHydrator           $attributeHydrator
     * @param \Enlight_Event_EventManager $eventManager
     * @param ConditionHandlerInterface[] $conditionHandlers
     * @param SortingHandlerInterface[]   $sortingHandlers
     * @param FacetHandlerInterface[]     $facetHandlers
     */
    public function __construct(
        QueryBuilderFactory $queryBuilderFactory,
        AttributeHydrator $attributeHydrator,
        \Enlight_Event_EventManager $eventManager,
        $conditionHandlers = array(),
        $sortingHandlers = array(),
        $facetHandlers = array()
    ) {
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->attributeHydrator = $attributeHydrator;
        $this->eventManager = $eventManager;
        $this->conditionHandlers = $conditionHandlers;
        $this->sortingHandlers = $sortingHandlers;
        $this->facetHandlers = $facetHandlers;
    }

    /**
     * Creates a product search result for the passed criteria object.
     * The criteria object contains different core conditions and plugin conditions.
     * This conditions has to be handled over the different condition handlers.
     *
     * The search gateway has to implement an event which plugin can be listened to,
     * to add their own handler classes.
     *
     * @param SearchBundle\Criteria $criteria
     * @param Context $context
     * @return SearchBundle\ProductNumberSearchResult
     */
    public function search(SearchBundle\Criteria $criteria, Context $context)
    {
        $this->conditionHandlers = $this->registerConditionHandlers();
        $this->facetHandlers     = $this->registerFacetHandlers();
        $this->sortingHandlers   = $this->registerSortingHandlers();

        $products = $this->getProducts($criteria, $context);

        $total = $this->getTotalCount($criteria, $context);

        $facets = $this->createFacets($criteria, $context);

        $result = new SearchBundle\ProductNumberSearchResult(
            $products,
            intval($total),
            $facets
        );

        return $result;
    }

    /**
     * @return ConditionHandlerInterface[]
     */
    private function registerConditionHandlers()
    {
        $conditionHandlers = new ArrayCollection();
        $conditionHandlers = $this->eventManager->collect(
            'Shopware_Search_Gateway_DBAL_Collect_Condition_Handlers',
            $conditionHandlers
        );

        return array_merge($conditionHandlers->toArray(), $this->conditionHandlers);
    }

    /**
     * @return FacetHandlerInterface[]
     */
    private function registerFacetHandlers()
    {
        $facetHandlers = new ArrayCollection();
        $facetHandlers = $this->eventManager->collect(
            'Shopware_Search_Gateway_DBAL_Collect_Facet_Handlers',
            $facetHandlers
        );

        return array_merge($facetHandlers->toArray(), $this->facetHandlers);
    }

    /**
     * @return SortingHandlerInterface[]
     */
    private function registerSortingHandlers()
    {
        $sortingHandlers = new ArrayCollection();
        $sortingHandlers = $this->eventManager->collect(
            'Shopware_Search_Gateway_DBAL_Collect_Sorting_Handlers',
            $sortingHandlers
        );

        return array_merge($sortingHandlers->toArray(), $this->sortingHandlers);
    }

    /**
     * Calculated the total count of the whole search result.
     *
     * @param SearchBundle\Criteria $criteria
     * @param Context $context
     * @return int
     */
    private function getTotalCount(SearchBundle\Criteria $criteria, Context $context)
    {
        $query = $this->getQuery($criteria, $context);

        if ($query->getQueryPart('having')) {
            return $query->getConnection()->fetchColumn('SELECT FOUND_ROWS()');
        }

        $query->resetQueryPart('groupBy')
            ->resetQueryPart('orderBy');

        $query->select('COUNT(DISTINCT product.id) as count');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        return $statement->fetch(\PDO::FETCH_COLUMN);
    }

    /**
     * Executes the base query to select the products.
     *
     * @param SearchBundle\Criteria $criteria
     * @param Context $context
     * @return array
     */
    private function getProducts(SearchBundle\Criteria $criteria, Context $context)
    {
        $query = $this->getQuery($criteria, $context);

        $this->addSorting($criteria, $query, $context);

        if ($query->getQueryPart('having')) {
            $select = $query->getQueryPart('select');

            $query->select(array(
                'SQL_CALC_FOUND_ROWS variant.ordernumber'
            ));

            foreach ($select as $selection) {
                $query->addSelect($selection);
            }
        } else {
            $query->addSelect(array(
                'variant.ordernumber'
            ));
        }

        $query->addGroupBy('product.id')
            ->setFirstResult($criteria->getOffset())
            ->setMaxResults($criteria->getLimit());

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $products = array();

        foreach ($data as $row) {
            $product = new SearchBundle\SearchProduct();
            $product->setNumber($row['ordernumber']);

            unset($row['ordernumber']);

            if (!empty($row)) {
                $product->addAttribute(
                    'search',
                    $this->attributeHydrator->hydrate($row)
                );
            }
            $products[$product->getNumber()] = $product;
        }

        return $products;
    }

    /**
     * @param SearchBundle\Criteria $criteria
     * @param Context $context
     * @return \Shopware\Bundle\SearchBundle\DBAL\QueryBuilder
     */
    private function getQuery(SearchBundle\Criteria $criteria, Context $context)
    {
        $query = $this->queryBuilderFactory->createQueryBuilder();

        $query->from('s_articles', 'product')
            ->innerJoin(
                'product',
                's_articles_details',
                'variant',
                'variant.id = product.main_detail_id
                 AND variant.active = 1
                 AND product.active = 1'
            )
            ->innerJoin(
                'product',
                's_core_tax',
                'tax',
                'tax.id = product.taxID'
            )
            ->innerJoin(
                'variant',
                's_articles_attributes',
                'productAttribute',
                'productAttribute.articledetailsID = variant.id'
            );

        $this->addConditions($criteria, $query, $context);

        $query->includesTable('s_articles_details');

        return $query;
    }

    /**
     * @param SearchBundle\Criteria $criteria
     * @param Context $context
     * @return SearchBundle\FacetInterface[]
     * @throws \Exception
     */
    private function createFacets(SearchBundle\Criteria $criteria, Context $context)
    {
        $facets = array();

        foreach ($criteria->getFacets() as $facet) {
            $query = $this->getQuery($criteria, $context);
            $handler = $this->getFacetHandler($facet);
            $facets[] = $handler->generateFacet($facet, $query, $criteria, $context);
        }

        return $facets;
    }

    /**
     * @param SearchBundle\Criteria $criteria
     * @param QueryBuilder $query
     * @param Context $context
     *
     * @throws \Exception
     */
    private function addConditions(SearchBundle\Criteria $criteria, QueryBuilder $query, Context $context)
    {
        foreach ($criteria->getConditions() as $condition) {
            $handler = $this->getConditionHandler($condition);
            $handler->generateCondition($condition, $query, $context);
        }
    }

    /**
     * @param SearchBundle\Criteria $criteria
     * @param QueryBuilder $query
     * @param Context $context
     * @throws \Exception
     */
    private function addSorting(SearchBundle\Criteria $criteria, QueryBuilder $query, Context $context)
    {
        foreach ($criteria->getSortings() as $sorting) {
            $handler = $this->getSortingHandler($sorting);
            $handler->generateSorting($sorting, $query, $context);
        }
    }

    /**
     * @param SearchBundle\SortingInterface $sorting
     * @throws \Exception
     * @return SortingHandlerInterface
     */
    private function getSortingHandler(SearchBundle\SortingInterface $sorting)
    {
        foreach ($this->sortingHandlers as $handler) {
            if ($handler->supportsSorting($sorting)) {
                return $handler;
            }
        }

        throw new \Exception(sprintf("Sorting %s not supported", get_class($sorting)));
    }

    /**
     * @param SearchBundle\FacetInterface $facet
     * @throws \Exception
     * @return FacetHandlerInterface
     */
    private function getFacetHandler(SearchBundle\FacetInterface $facet)
    {
        foreach ($this->facetHandlers as $handler) {
            if ($handler->supportsFacet($facet)) {
                return $handler;
            }
        }

        throw new \Exception(sprintf("Facet %s not supported", get_class($facet)));
    }

    /**
     * @param SearchBundle\ConditionInterface $condition
     * @throws \Exception
     * @return ConditionHandlerInterface
     */
    private function getConditionHandler(SearchBundle\ConditionInterface $condition)
    {
        foreach ($this->conditionHandlers as $handler) {
            if ($handler->supportsCondition($condition)) {
                return $handler;
            }
        }

        throw new \Exception(sprintf("Condition %s not supported", get_class($condition)));
    }
}
