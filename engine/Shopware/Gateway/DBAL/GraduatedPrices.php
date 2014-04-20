<?php

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Struct as Struct;
use Shopware\Gateway\DBAL\Hydrator as Hydrator;

class GraduatedPrices extends Gateway
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
     * This function returns the graduated customer group prices for the passed product.
     *
     * The graduated product prices are selected over the s_articles_prices.articledetailsID column.
     * The id is stored in the Struct\ListProduct::variantId property.
     * Additionally it is important that the prices are ordered ascending by the Struct\Price::from property.
     *
     * @param Struct\ListProduct $product
     * @param Struct\Customer\Group $customerGroup
     * @return Struct\Product\PriceRule[]
     */
    public function get(
        Struct\ListProduct $product,
        Struct\Customer\Group $customerGroup
    ) {
        $prices = $this->getList(array($product), $customerGroup);

        return array_shift($prices);
    }

    /**
     * @param Struct\ListProduct[] $products
     * @param Struct\Customer\Group $customerGroup
     * @return Struct\Product\PriceRule[]
     */
    public function getList(array $products, Struct\Customer\Group $customerGroup)
    {
        $ids = array();
        foreach ($products as $product) {
            $ids[] = $product->getVariantId();
        }

        $query = $this->entityManager->getDBALQueryBuilder();

        $query->select($this->getPriceFields());
        $query->addSelect($this->getTableFields('s_articles_prices_attributes', 'attribute'));

        $query->from('s_articles_prices', 'prices')
            ->leftJoin('prices', 's_articles_prices_attributes', 'attribute', 'attribute.priceID = prices.id');

        $query->where('prices.articledetailsID IN (:products)')
            ->andWhere('prices.pricegroup = :customerGroup')
            ->setParameter(':products', $ids, Connection::PARAM_INT_ARRAY)
            ->setParameter(':customerGroup', $customerGroup->getKey());

        $query->orderBy('prices.articledetailsID', 'ASC')
            ->addOrderBy('prices.from', 'ASC');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $prices = array();
        foreach ($data as $row) {
            $product = $row['articledetailsID'];

            $prices[$product][] = $this->priceHydrator->hydratePriceRule($row);
        }

        return $prices;
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
}