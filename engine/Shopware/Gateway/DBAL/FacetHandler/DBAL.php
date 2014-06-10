<?php

namespace Shopware\Gateway\DBAL\FacetHandler;

use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Gateway\Search\Criteria;
use Shopware\Gateway\Search\Facet;
use Shopware\Struct\Context;

abstract class DBAL
{
    /**
     * @param Facet $facet
     * @param QueryBuilder $query
     * @param Criteria $criteria
     * @param Context $context
     * @return Facet
     */
    abstract public function generateFacet(
        Facet $facet,
        QueryBuilder $query,
        Criteria $criteria,
        Context $context
    );

    public function supportsFacet(Facet $facet)
    {
        return false;
    }
}
