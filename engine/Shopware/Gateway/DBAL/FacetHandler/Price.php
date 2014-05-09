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

    /**
     * @param Facet\Price $facet
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

        $graduations = $this->getGraduations($criteria);

        $this->joinPrices($query, $facet);

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

            $result = $statement->fetch(\PDO::FETCH_ASSOC);

            $total = (int) $result['total'];
            if ($total <= 0) {
                continue;
            }

            $max = $graduation['priceMax'];
            if ($max == null) {
                $max = $result['priceMax'];
            }

            $prices[] = array(
                'priceMin' => $graduation['priceMin'],
                'priceMax' => $max,
                'total' => $total
            );
        }

        $facet->prices = $prices;

        return $facet;
    }

    public function supportsFacet(Facet $facet)
    {
        return ($facet instanceof Facet\Price);
    }

    private function joinPrices(QueryBuilder $query, Facet\Price $facet)
    {
        $calculation = $this->priceHelper->getPriceSelection($facet->currentCustomerGroup);

        $query->innerJoin(
            'products',
            's_articles_prices',
            'prices',
            "prices.articledetailsID = variants.id
             AND prices.from = 1
             AND prices.pricegroup = :priceGroupFacet
             AND " . $calculation ." BETWEEN :priceMin AND :priceMax"
        );

        $query->addSelect('MAX('. $calculation .') as priceMax');

        /**@var $facet Facet\Price*/
        $query->setParameter(':priceGroupFacet', $facet->fallbackCustomerGroup->getKey());
    }

    private function getGraduations(Criteria $criteria)
    {
        /**@var $price \Shopware\Gateway\Search\Condition\Price*/
        if ($price = $criteria->getCondition('price')) {
            return array(
                array('priceMin' => $price->min, 'priceMax' => $price->max)
            );
        }

        return array(
            array('priceMin' => 0,   'priceMax' => 100),
            array('priceMin' => 101, 'priceMax' => 200),
            array('priceMin' => 201, 'priceMax' => 300),
            array('priceMin' => 301, 'priceMax' => 400),
            array('priceMin' => 401, 'priceMax' => null)
        );
    }
}