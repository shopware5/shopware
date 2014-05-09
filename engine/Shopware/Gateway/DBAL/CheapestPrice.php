<?php

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Struct;
use Shopware\Gateway\DBAL\Hydrator as Hydrator;


class CheapestPrice extends Gateway
{
    /**
     * @var \Shopware\Gateway\DBAL\Hydrator\Price
     */
    private $priceHydrator;

    /**
     * @param ModelManager $entityManager
     * @param Hydrator\Price $priceHydrator
     */
    function __construct(
        ModelManager $entityManager,
        Hydrator\Price $priceHydrator
    ) {
        $this->entityManager = $entityManager;
        $this->priceHydrator = $priceHydrator;
    }

    /**
     * Returns a Struct\Product\PriceRule list which contains the definition of the
     * cheapest product prices.
     *
     * The cheapest product price has to be selected for the passed customer group.
     *
     * If no specify price defined for the customer group the function should return null.
     *
     * The cheapest price service handles the fallback on the default shop customer group.
     *
     * The cheapest price should be selected with the following conditions:
     *  - Only the first graduated price
     *  - Select prices variant across
     *  - Select the purchase data of the cheapest variant.
     *  - Select the unit of the cheapest variant
     *  - The variants has to be active
     *  - Closeout variants can only be selected if the stock > min purchase
     *
     * @param Struct\ListProduct[] $products
     * @param Struct\Customer\Group $customerGroup
     * @return Struct\Product\PriceRule[]
     */
    public function getList(array $products, Struct\Customer\Group $customerGroup)
    {
        /**
         * Contains the cheapest price logic which product price should be selected.
         */
        $ids = $this->getCheapestPriceIds($products, $customerGroup);

        $query = $this->entityManager->getDBALQueryBuilder();

        $query->select($this->getPriceFields())
            ->addSelect($this->getUnitFields())
            ->addSelect($this->getTableFields('s_articles_prices_attributes', 'attribute'));

        $query->from('s_articles_prices', 'prices')
            ->innerJoin('prices', 's_articles_details', 'variant', 'variant.id = prices.articledetailsID')
            ->innerJoin('variant', 's_articles', 'product', 'product.id = variant.articleID')
            ->leftJoin('variant', 's_core_units', 'unit', 'unit.id = variant.unitID')
            ->leftJoin('prices', 's_articles_prices_attributes', 'attribute', 'attribute.priceID = prices.id');

        $query->andWhere('prices.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $prices = array();
        foreach ($data as $row) {
            $product = $row['articleID'];

            $prices[$product] = $this->priceHydrator->hydrateCheapestPrice($row);
        }

        return $prices;
    }

    /**
     * Pre selection of the cheapest prices ids.
     *
     * @param Struct\ListProduct[] $products
     * @param Struct\Customer\Group $customerGroup
     * @return mixed
     */
    private function getCheapestPriceIds(array $products, Struct\Customer\Group $customerGroup)
    {
        $ids = array();
        foreach ($products as $product) {
            $ids[] = $product->getId();
        }

        $subQuery = $this->entityManager->getDBALQueryBuilder();

        $subQuery->select('prices.id')
            ->from('s_articles_prices', 'prices');

        /**
         * joins the product variants for the min purchase calculation.
         * The cheapest price is defined by prices.price * variant.minpurchase (the real basket price)
         */
        $subQuery->innerJoin(
            'prices',
            's_articles_details',
            'variant',
            'variant.id = prices.articledetailsID'
        );

        /**
         * Joins the products for the closeout validation.
         * Required to select only product prices which product variant can be added to the basket and purchased
         */
        $subQuery->innerJoin(
            'variant',
            's_articles',
            'product',
            'product.id = variant.articleID'
        );

        $subQuery->where('prices.pricegroup = :customerGroup')
            ->andWhere('prices.from = 1')
            ->andWhere('variant.active = 1')
            ->andWhere('prices.articleID = outerPrices.articleID');

        /**
         * This part of the query handles the closeout products.
         *
         * The `laststock` column contains "1" if the product is a closeout product.
         * In the case that the product contains the closeout flag,
         * the stock and minpurchase are used as they defined in the database
         *
         * In the case that the product isn't a closeout product,
         * the stock and minpurchase are set to 0
         */
        $subQuery->andWhere(
            '(product.laststock * variant.instock) >= (product.laststock * variant.minpurchase)'
        );

        $subQuery->setMaxResults(1);

        /**
         * Sorting of the cheapest available product price.
         */
        $subQuery->orderBy('(prices.price * variant.minpurchase)');

        /**
         * Creates an outer query which allows to
         * select multiple cheapest product prices.
         */
        $query = $this->entityManager->getDBALQueryBuilder();
        $query->setParameter(':customerGroup', $customerGroup->getKey());

        $query->select('('. $subQuery->getSQL() .') as priceId')
            ->from('s_articles_prices', 'outerPrices')
            ->where('outerPrices.articleID IN (:products)')
            ->setParameter(':products', $ids, Connection::PARAM_INT_ARRAY)
            ->groupBy('outerPrices.articleID')
            ->having('priceId IS NOT NULL');

        $statement = $query->execute();

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Returns a Struct\Product\PriceRule which contains the definition of the
     * cheapest product price.
     *
     * The cheapest product price has to be selected for the passed customer group.
     *
     * If no specify price defined for the customer group the function should return null.
     *
     * The cheapest price service handles the fallback on the default shop customer group.
     *
     * The cheapest price should be selected with the following conditions:
     *  - Only the first graduated price
     *  - Select prices variant across
     *  - Select the unit of the cheapest variant
     *  - The variants has to be active
     *  - Closeout variants can only be selected if the stock > min purchase
     *
     * @param \Shopware\Struct\ListProduct $product
     * @param Struct\Customer\Group $customerGroup
     * @return Struct\Product\PriceRule
     */
    public function get(Struct\ListProduct $product, Struct\Customer\Group $customerGroup)
    {
        $prices = $this->getList(array($product), $customerGroup);

        return array_shift($prices);
    }


    private function getPriceFields()
    {
        return array(
            'prices.id',
            'prices.pricegroup',
            'prices.from',
            'prices.to',
            'prices.articleID',
            'prices.articledetailsID',
            'prices.price',
            'prices.pseudoprice',
            'prices.baseprice',
            'prices.percent'
        );
    }

    private function getUnitFields()
    {
        return array(
            'unit.id    as __unit_id',
            'unit.description    as __unit_description',
            'unit.unit    as __unit_unit',
            'variant.packunit as __unit_packunit',
            'variant.purchaseunit as __unit_purchaseunit',
            'variant.referenceunit as __unit_referenceunit',
            'variant.purchasesteps as __unit_purchasesteps',
            'variant.minpurchase as __unit_minpurchase',
            'variant.maxpurchase as __unit_maxpurchase',
        );
    }

}