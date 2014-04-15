<?php

namespace Shopware\Gateway\DBAL;

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Components\Model\ModelManager;
use Shopware\Gateway\DBAL\FacetHandler as FacetHandler;
use Shopware\Gateway\DBAL\QueryGenerator as QueryGenerator;
use Shopware\Gateway\Search\Criteria;
use Shopware\Gateway\Search\Result;

class Search implements \Shopware\Gateway\Search
{
    /**
     * @var ModelManager
     */
    private $entityManager;

    /**
     * @var QueryGenerator\DBAL[]
     */
    private $queryGenerators;

    /**
     * @var FacetHandler\DBAL[]
     */
    private $facetHandlers;

    /**
     * @param ModelManager $entityManager
     */
    function __construct(ModelManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param \Shopware\Gateway\Search\Criteria $criteria
     * @return Result
     */
    public function search(Criteria $criteria)
    {
        $this->queryGenerators[] = new QueryGenerator\CoreGenerator();

        $this->facetHandlers[] = new FacetHandler\Manufacturer();
        $this->facetHandlers[] = new FacetHandler\Category();

        $products = $this->getProducts($criteria);

        $this->createFacets($criteria);

        $result = new Result(
            array_column($products, 'ordernumber'),
            100,
            $criteria->facets
        );

        return $result;
    }

    private function getProducts(Criteria $criteria)
    {
        $query = $this->getQuery($criteria)
            ->select(array('variants.ordernumber'))
            ->setFirstResult($criteria->offset)
            ->setMaxResults($criteria->limit);

        $this->addSorting($criteria, $query);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param Criteria $criteria
     * @return QueryBuilder
     */
    private function getQuery(Criteria $criteria)
    {
        $query = $this->entityManager->getDBALQueryBuilder();

        $query->from('s_articles', 'products')
            ->innerJoin('products', 's_articles_details', 'variants', 'variants.id = products.main_detail_id')
            ->innerJoin('products', 's_core_tax', 'tax', 'tax.id = products.taxID');

        $this->addConditions($criteria, $query);

        return $query;
    }

    private function createFacets(Criteria $criteria)
    {
        foreach($criteria->facets as $facet) {

            $query = $this->getQuery($criteria);

            $supported = false;

            foreach($this->facetHandlers as $facetHandler) {

                if ($facetHandler->supportsFacet($facet)) {

                    $facetHandler->generateFacet($facet, $query);

                    $supported = true;

                    break;
                }
            }

            if (!$supported) {
                throw new \Exception(sprintf("Facet %s not supported", get_class($facet)));
            }
        }
    }

    /**
     * @param Criteria $criteria
     * @param QueryBuilder $query
     * @throws \Exception
     */
    private function addConditions(Criteria $criteria, QueryBuilder $query)
    {
        foreach($criteria->conditions as $condition) {
            $supported = false;

            foreach($this->queryGenerators as $generator) {

                if ($generator->supportsCondition($condition)) {
                    $supported = true;

                    $generator->generateCondition($condition, $query);

                    break;
                }
            }

            if (!$supported) {
                throw new \Exception(sprintf("Condition %s not supported", get_class($condition)));
            }
        }
    }

    /**
     * @param Criteria $criteria
     * @param QueryBuilder $query
     * @throws \Exception
     */
    private function addSorting(Criteria $criteria, QueryBuilder $query)
    {
        foreach($criteria->sortings as $sorting) {
            $supported = false;

            foreach($this->queryGenerators as $generator) {

                if ($generator->supportsSorting($sorting)) {
                    $supported = true;
                    $generator->generateSorting($sorting, $query);
                    break;
                }
            }

            if (!$supported) {
                throw new \Exception(sprintf("Sorting %s not supported", get_class($sorting)));
            }
        }
    }

}