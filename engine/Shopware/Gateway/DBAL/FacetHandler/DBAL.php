<?php

namespace Shopware\Gateway\DBAL\FacetHandler;

use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Gateway\Search\Facet;

abstract class DBAL
{
    abstract public function generateFacet(Facet $facet, QueryBuilder $query);

    public function supportsFacet(Facet $facet)
    {
        return false;
    }
}
