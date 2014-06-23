<?php

namespace Shopware\Gateway\DBAL\FacetHandler;

use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Gateway\Search\Criteria;
use Shopware\Gateway\Search\Facet;
use Shopware\Struct\Context;

class ShippingFree implements DBAL
{
    /**
     * Generates the facet data for the passed query, criteria and context object.
     *
     * @param Facet|Facet\ShippingFree $facet
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
    ) {
        $query->resetQueryPart('orderBy');

        $query->resetQueryPart('groupBy');

        $query->select(array(
            'COUNT(DISTINCT products.id) as total'
        ));

        $query->andWhere('variants.shippingfree = 1');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $total = $statement->fetch(\PDO::FETCH_COLUMN);

        $facet->setTotal($total);

        if ($criteria->getCondition('shipping_free')) {
            $facet->setIsFiltered(true);
        }

        return $facet;
    }

    /**
     * Checks if the passed facet can be handled by this class.
     * @param Facet $facet
     * @return bool
     */
    public function supportsFacet(Facet $facet)
    {
        return ($facet instanceof Facet\ShippingFree);
    }

}