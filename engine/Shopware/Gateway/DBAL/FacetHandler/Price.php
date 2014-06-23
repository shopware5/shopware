<?php

namespace Shopware\Gateway\DBAL\FacetHandler;

use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Gateway\DBAL\Search;
use Shopware\Gateway\DBAL\SearchPriceHelper;
use Shopware\Gateway\Search\Criteria;
use Shopware\Gateway\Search\Facet;
use Shopware\Struct\Context;

class Price implements DBAL
{
    /**
     * @var SearchPriceHelper
     */
    private $priceHelper;

    /**
     * @param SearchPriceHelper $priceHelper
     */
    function __construct(SearchPriceHelper $priceHelper)
    {
        $this->priceHelper = $priceHelper;
    }

    public function supportsFacet(Facet $facet)
    {
        return ($facet instanceof Facet\Price);
    }

    /**
     * @param \Shopware\Gateway\Search\Facet|\Shopware\Gateway\Search\Facet\Price $facet
     * @param \Shopware\Components\Model\DBAL\QueryBuilder $query
     * @param \Shopware\Gateway\Search\Criteria $criteria
     * @param Context $context
     * @return \Shopware\Gateway\Search\Facet\Category
     */
    public function generateFacet(
        Facet $facet,
        QueryBuilder $query,
        Criteria $criteria,
        Context $context
    ) {
        $query->resetQueryPart('orderBy');
        $query->resetQueryPart('groupBy');

        /**@var $condition \Shopware\Gateway\Search\Condition\Price */
        if ($condition = $criteria->getCondition('price')) {
            $facet->setMinPrice($condition->getMinPrice());
            $facet->setMaxPrice($condition->getMaxPrice());
            return $facet;
        }

        $this->priceHelper->joinPrices(
            $query,
            $context->getCurrentCustomerGroup(),
            $context->getFallbackCustomerGroup()
        );

        $selection = $this->priceHelper->getCheapestPriceSelection(
            $context->getCurrentCustomerGroup()
        );

        $query->select(
            array(
                $selection . ' as cheapest_price'
            )
        );

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $min = $statement->fetch(\PDO::FETCH_COLUMN);

        $query->groupBy('products.id')
            ->orderBy('cheapest_price', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $max = $statement->fetch(\PDO::FETCH_COLUMN);

        $facet->setMinPrice($min);
        $facet->setMaxPrice($max);

        return $facet;
    }
}
