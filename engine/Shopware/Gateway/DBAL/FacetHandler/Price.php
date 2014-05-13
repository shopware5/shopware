<?php

namespace Shopware\Gateway\DBAL\FacetHandler;

use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Gateway\DBAL\Search;
use Shopware\Gateway\DBAL\SearchPriceHelper;
use Shopware\Gateway\Search\Criteria;
use Shopware\Gateway\Search\Facet;

class Price extends DBAL
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

        /**@var $condition \Shopware\Gateway\Search\Condition\Price*/
        if ($condition = $criteria->getCondition('price')) {
            $facet->range = array(
                'min' => $condition->min,
                'max' => $condition->max
            );
            return $facet;
        }

        $this->priceHelper->joinPrices(
            $query,
            $facet->currentCustomerGroup,
            $facet->fallbackCustomerGroup
        );

        $selection = $this->priceHelper->getCheapestPriceSelection($facet->currentCustomerGroup);

        $query->select(array(
            $selection . ' as cheapest_price'
        ));

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

        $facet->range = array(
            'min' => $min,
            'max' => $max
        );

        return $facet;
    }
}