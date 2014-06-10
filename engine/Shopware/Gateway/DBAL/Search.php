<?php

namespace Shopware\Gateway\DBAL;

use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Components\Model\ModelManager;

use Shopware\Gateway\DBAL\FacetHandler as FacetHandler;
use Shopware\Gateway\DBAL\Hydrator;
use Shopware\Gateway\DBAL\QueryGenerator as QueryGenerator;

use Shopware\Gateway\Search\Condition;
use Shopware\Gateway\Search\Criteria;
use Shopware\Gateway\Search\Facet;
use Shopware\Gateway\Search\Product;
use Shopware\Gateway\Search\Result;
use Shopware\Gateway\Search\Sorting;
use Shopware\Struct\Context;
use Shopware\Struct\Customer\Group;

class Search
{
    /**
     * @var QueryGenerator\DBAL[]
     */
    private $queryGenerators;

    /**
     * @var FacetHandler\DBAL[]
     */
    private $facetHandlers;

    /**
     * @var Hydrator\Attribute
     */
    private $attributeHydrator;

    /**
     * @param ModelManager $entityManager
     * @param Hydrator\Attribute $attributeHydrator
     */
    function __construct(ModelManager $entityManager, Hydrator\Attribute $attributeHydrator)
    {
        $this->entityManager = $entityManager;
        $this->attributeHydrator = $attributeHydrator;
    }

    /**
     * @param \Shopware\Gateway\Search\Criteria $criteria
     * @param Context $context
     * @return Result
     */
    public function search(Criteria $criteria, Context $context)
    {
        $this->queryGenerators[] = new QueryGenerator\CoreGenerator(new SearchPriceHelper());
        $this->facetHandlers[] = Shopware()->Container()->get('manufacturer_facet_handler_dbal');
        $this->facetHandlers[] = Shopware()->Container()->get('category_facet_handler_dbal');
        $this->facetHandlers[] = Shopware()->Container()->get('price_facet_handler_dbal');
        $this->facetHandlers[] = Shopware()->Container()->get('property_facet_handler_dbal');

        $products = $this->getProducts($criteria, $context);

        $total = $this->getTotalCount($criteria, $context);

        $facets = $this->createFacets($criteria, $context);

        $result = new Result(
            $products,
            intval($total),
            $facets
        );

        return $result;
    }

    /**
     * Calculated the total count of the whole search result.
     *
     * @param Criteria $criteria
     * @param \Shopware\Struct\Context $context
     * @return mixed
     */
    private function getTotalCount(Criteria $criteria, Context $context)
    {
        $query = $this->getQuery($criteria, $context);

        $query->resetQueryPart('groupBy')
            ->resetQueryPart('orderBy');

        $query->select('COUNT(DISTINCT products.id) as count');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        return $statement->fetch(\PDO::FETCH_COLUMN);
    }

    /**
     * Executes the base query to select the products.
     *
     * @param Criteria $criteria
     * @param \Shopware\Struct\Context $context
     * @return array
     */
    private function getProducts(Criteria $criteria, Context $context)
    {
        $query = $this->getQuery($criteria, $context)
            ->addSelect(array('variants.articleID', 'variants.ordernumber'))
            ->addGroupBy('products.id')
            ->setFirstResult($criteria->offset)
            ->setMaxResults($criteria->limit);

        $this->addSorting($criteria, $query, $context);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $products = array();

        foreach ($data as $row) {
            $product = new Product();
            $product->setNumber($row['ordernumber']);

            unset($row['ordernumber']);

            if (!empty($row)) {
                $product->addAttribute(
                    'search',
                    $this->attributeHydrator->hydrate($row)
                );
            }
            $products[] = $product;
        }
        return $products;
    }

    /**
     * @param Criteria $criteria
     * @param \Shopware\Struct\Context $context
     * @return QueryBuilder
     */
    private function getQuery(Criteria $criteria, Context $context)
    {
        $query = $this->entityManager->getDBALQueryBuilder();

        $query->from('s_articles', 'products')
            ->innerJoin(
                'products',
                's_articles_details',
                'variants',
                'variants.id = products.main_detail_id AND variants.active = 1 AND products.active = 1'
            )
            ->innerJoin(
                'products',
                's_core_tax',
                'tax',
                'tax.id = products.taxID'
            );

        $this->addConditions($criteria, $query, $context);

        return $query;
    }

    private function createFacets(Criteria $criteria, Context $context)
    {
        $facets = array();

        foreach ($criteria->facets as $facet) {
            $query = $this->getQuery($criteria, $context);

            $handler = $this->getFacetHandler($facet);

            if ($handler === null) {
                throw new \Exception(sprintf("Facet %s not supported", get_class($facet)));
            }

            $facets[] = $handler->generateFacet($facet, $query, $criteria, $context);
        }

        return $facets;
    }

    /**
     * @param Criteria $criteria
     * @param QueryBuilder $query
     * @param \Shopware\Struct\Context $context
     *
     * @throws \Exception
     */
    private function addConditions(Criteria $criteria, QueryBuilder $query, Context $context)
    {
        foreach ($criteria->conditions as $condition) {
            $generator = $this->getConditionGenerator($condition);

            if ($generator === null) {
                throw new \Exception(sprintf("Condition %s not supported", get_class($condition)));
            }

            $generator->generateCondition($condition, $query, $context);
        }
    }

    /**
     * @param Criteria $criteria
     * @param QueryBuilder $query
     * @param \Shopware\Struct\Context $context
     * @throws \Exception
     */
    private function addSorting(Criteria $criteria, QueryBuilder $query, Context $context)
    {
        foreach ($criteria->sortings as $sorting) {

            $generator = $this->getSortingGenerator($sorting);

            if ($generator === null) {
                throw new \Exception(sprintf("Sorting %s not supported", get_class($sorting)));
            }

            $generator->generateSorting($sorting, $query, $context);
        }
    }

    private function getSortingGenerator(Sorting $sorting)
    {
        foreach ($this->queryGenerators as $generator) {
            if ($generator->supportsSorting($sorting)) {
                return $generator;
            }
        }

        return null;
    }

    private function getFacetHandler(Facet $facet)
    {
        foreach ($this->facetHandlers as $handler) {
            if ($handler->supportsFacet($facet)) {
                return $handler;
            }
        }
        return null;
    }

    private function getConditionGenerator(Condition $condition)
    {
        foreach ($this->queryGenerators as $generator) {
            if ($generator->supportsCondition($condition)) {
                return $generator;
            }
        }

        return null;
    }
}