<?php

namespace Shopware\Gateway\DBAL\FacetHandler;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Gateway\Search\Criteria;
use Shopware\Gateway\Search\Facet;

class Property extends DBAL
{
    /**
     * @param Facet $facet
     * @param QueryBuilder $query
     * @param \Shopware\Gateway\Search\Criteria $criteria
     * @return \Shopware\Gateway\Search\Facet\Category
     */
    public function generateFacet(
        Facet $facet,
        QueryBuilder $query,
        Criteria $criteria
    ) {
        $query->resetQueryPart('orderBy');

        $query->resetQueryPart('groupBy');

        $query->select(
            array(
                'productProperties.valueID as id',
                'COUNT(DISTINCT products.id) as total'
            )
        );

        $query->innerJoin(
            'products',
            's_filter',
            'propertySet',
            'propertySet.id = products.filtergroupID'
        );

        $query->innerJoin(
            'products',
            's_filter_articles',
            'productProperties',
            'productProperties.articleID = products.id'
        );

        $query->innerJoin(
            'productProperties',
            's_filter_values',
            'propertyOptions',
            'propertyOptions.id = productProperties.valueID'
        );

        $query->innerJoin(
            'propertyOptions',
            's_filter_options',
            'propertyGroups',
            'propertyGroups.id = propertyOptions.optionID
             AND propertyGroups.filterable = 1'
        );

        $query->innerJoin(
            'propertyOptions',
            's_filter_relations',
            'propertyRelations',
            'propertyRelations.optionID = propertyGroups.id'
        );

        $query->groupBy('propertyOptions.id');


        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $facet->properties = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

        return $facet;
    }

    public function supportsFacet(Facet $facet)
    {
        return ($facet instanceof Facet\Property);
    }
}
