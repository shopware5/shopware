<?php

namespace Shopware\Gateway\DBAL\FacetHandler;

use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Gateway\Search\Criteria;
use Shopware\Gateway\Search\Facet;
use Shopware\Struct\Context;

interface DBAL
{
    /**
     * Generates the facet data for the passed query, criteria and context object.
     *
     * @param Facet $facet
     * @param QueryBuilder $query
     * @param Criteria $criteria
     * @param Context $context
     * @return Facet
     */
    public function generateFacet(
        Facet $facet,
        QueryBuilder $query,
        Criteria $criteria,
        Context $context
    );

    /**
     * Checks if the passed facet can be handled by this class.
     * @param Facet $facet
     * @return bool
     */
    public function supportsFacet(Facet $facet);
}
