<?php

namespace Shopware\Gateway\DBAL\FacetHandler;

use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Gateway\Search\Criteria;
use Shopware\Gateway\Search\Facet;

class Manufacturer extends DBAL
{
    public function generateFacet(
        Facet $facet,
        QueryBuilder $query,
        Criteria $criteria
    ) {
        $query->resetQueryPart('groupBy');

        $query->resetQueryPart('orderBy');

        $query->select(
            array(
                'products.supplierID as id',
                'COUNT(DISTINCT products.id) as total'
            )
        );


        $query->groupBy('products.supplierID');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        /**@var $facet Facet\Manufacturer */
        $facet->manufacturers = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return $facet;
    }

    public function supportsFacet(Facet $facet)
    {
        return ($facet instanceof Facet\Manufacturer);
    }
}