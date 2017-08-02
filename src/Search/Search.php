<?php

namespace Shopware\Search;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Context\TranslationContext;

abstract class Search
{
    /**
     * @var HandlerInterface[]
     */
    protected $handlers;

    /**
     * @var Connection
     */
    protected $connection;

    public function __construct(Connection $connection, array $handlers)
    {
        $this->handlers = $handlers;
        $this->connection = $connection;
    }

    abstract protected function createQuery(): QueryBuilder;

    public function search(Criteria $criteria, TranslationContext $context): SearchResult
    {
        $query = $this->createQuery();

        if ($criteria->fetchCount()) {
            $selects = $query->getQueryPart('select');
            $selects[0] = 'SQL_CALC_FOUND_ROWS ' . $selects[0];
            $query->select($selects);
        }

        $this->addCriteriaPartToQuery($query, $criteria, $criteria->getConditions(), $context);
        $this->addCriteriaPartToQuery($query, $criteria, $criteria->getSortings(), $context);

        if ($criteria->getOffset()) {
            $query->setFirstResult($criteria->getOffset());
        }
        if ($criteria->getLimit()) {
            $query->setMaxResults($criteria->getLimit());
        }

        $rows = $query->execute()->fetchAll();

        if ($criteria->fetchCount()) {
            $total = $this->connection->fetchColumn('SELECT FOUND_ROWS()');
        } else {
            $total = count($rows);
        }

        return $this->createResult($rows, $total);
    }

    public function aggregate(Criteria $criteria, TranslationContext $context): AggregationResult
    {
        $facetResults = [];
        foreach ($criteria->getFacets() as $facet) {
            $query = $this->buildFacetQuery($criteria, $context);

            $handler = $this->getHandler($facet);

            $facetResults[] = $handler->aggregate($facet, $query, $criteria, $context);
        }

        return new AggregationResult($facetResults);
    }

    protected function addCriteriaPartToQuery(QueryBuilder $query, Criteria $criteria, array $parts, TranslationContext $context): void
    {
        foreach ($parts as $part) {
            $handler = $this->getHandler($part);
            $handler->handle($part, $query, $criteria, $context);
        }
    }

    protected  function getHandler(CriteriaPartInterface $criteriaPart)
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($criteriaPart)) {
                return $handler;
            }
        }
        throw new \RuntimeException(sprintf('No handler supports class %s', get_class($criteriaPart)));
    }

    protected function buildFacetQuery(Criteria $criteria, TranslationContext $context): QueryBuilder
    {
        $query = $this->createQuery();

        if ($criteria->generatePartialFacets()) {
            $this->addCriteriaPartToQuery($query, $criteria, $criteria->getConditions(), $context);
        } else {
            $this->addCriteriaPartToQuery($query, $criteria, $criteria->getBaseConditions(), $context);
        }

        return $query;
    }

    protected function createResult(array $rows, int $total): SearchResult
    {
        return new SearchResult($rows, $total);
    }
}
