<?php

namespace Shopware\Gateway\DBAL\FacetHandler;

use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Gateway\Search\Criteria;
use Shopware\Gateway\Search\Facet;

class Price extends DBAL
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
        $query->removeTableInclude('s_articles_prices');

        $query->resetQueryPart('orderBy');

        $query->resetQueryPart('groupBy');

        $query->select(array('COUNT(DISTINCT products.id) as total'));

        /**@var $price \Shopware\Gateway\Search\Condition\Price*/
        if ($price = $criteria->getCondition('price')) {
            $graduations = array(
                array('priceMin' => $price->min, 'priceMax' => $price->max)
            );
        } else {
            $graduations = array(
                array('priceMin' => 0,   'priceMax' => 100),
                array('priceMin' => 101, 'priceMax' => 200),
                array('priceMin' => 201, 'priceMax' => 300),
                array('priceMin' => 301, 'priceMax' => 400),
                array('priceMin' => 401, 'priceMax' => null)
            );
        }

        $query->innerJoin(
            'products',
            's_articles_prices',
            'prices',
            "prices.articledetailsID = variants.id
             AND prices.from = 1
             AND prices.pricegroup = :priceGroupFacet
             AND (prices.price * variants.minpurchase) BETWEEN :priceMin AND :priceMax"
        );

        /**@var $facet Facet\Price*/
        $query->setParameter(':priceGroupFacet', $facet->customerGroupKey);

        $prices = array();
        foreach($graduations as $graduation) {
            $query->setParameter(':priceMin', $graduation['priceMin']);

            if ($graduation['priceMax'] === null) {
                $query->setParameter(':priceMax', 999999999, \PDO::PARAM_INT);
            } else {
                $query->setParameter(':priceMax', $graduation['priceMax']);
            }

            /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
            $statement = $query->execute();

            $prices[] = array(
                'priceMin' => $graduation['priceMin'],
                'priceMax' => $graduation['priceMax'],
                'total' => intval($statement->fetch(\PDO::FETCH_COLUMN))
            );
        }

        $facet->prices = $prices;

        return $facet;
    }

    public function supportsFacet(Facet $facet)
    {
        return ($facet instanceof Facet\Price);
    }
}