<?php

namespace Shopware\Gateway\DBAL\FacetHandler;

use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Gateway\Search\Criteria;
use Shopware\Gateway\Search\Facet;

abstract class DBAL
{
    /**
     * @param Facet $facet
     * @param QueryBuilder $query
     * @param Criteria $criteria
     * @return Facet
     */
    abstract public function generateFacet(
        Facet $facet,
        QueryBuilder $query,
        Criteria $criteria
    );

    public function supportsFacet(Facet $facet)
    {
        return false;
    }
}
