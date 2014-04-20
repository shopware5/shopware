<?php

namespace Shopware\Gateway\DBAL\FacetHandler;

use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Gateway\Search\Facet;

class Manufacturer extends DBAL
{
    public function generateFacet(Facet $facet, QueryBuilder $query)
    {
        $query->resetQueryPart('groupBy');

        $query->select(
            array(
                'products.supplierID',
                'COUNT(products.id) as total'
            )
        );

        $query->groupBy('products.supplierID');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        /**@var $facet Facet\Manufacturer */
        $facet->manufacturers = $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function supportsFacet(Facet $facet)
    {
        return ($facet instanceof Facet\Manufacturer);
    }
}