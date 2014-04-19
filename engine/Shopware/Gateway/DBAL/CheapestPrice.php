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
    )
    {
        $this->entityManager = $entityManager;
        $this->priceHydrator = $priceHydrator;
    }

    /**
     * @param Struct\ListProduct[] $products
     * @param Struct\Customer\Group $customerGroup
     * @return Struct\Product\PriceRule
     */
    public function getList(array $products, Struct\Customer\Group $customerGroup)
    {
        $ids = array();
        foreach($products as $product) {
            $ids[] = $product->getId();
        }

        $query = $this->entityManager->getDBALQueryBuilder();

        $query->select($this->getPriceFields())
            ->addSelect($this->getUnitFields())
            ->addSelect($this->getTableFields('s_articles_prices_attributes', 'attribute'))
            ->addSelect(array(
                '(prices.price * variant.minpurchase) as calculated',
                '(prices.pseudoprice * variant.minpurchase) as calculatedPseudo'
            ));

        $query->from('s_articles_prices', 'prices')
            ->innerJoin('prices', 's_articles_details', 'variant', 'variant.id = prices.articledetailsID')
            ->innerJoin('variant', 's_articles', 'product', 'product.id = variant.articleID')
            ->leftJoin('variant', 's_core_units', 'unit', 'unit.id = variant.unitID')
            ->leftJoin('prices', 's_articles_prices_attributes', 'attribute', 'attribute.priceID = prices.id');

        $query->andWhere('prices.articleID IN (:products)')
            ->andWhere('prices.pricegroup = :customerGroup')
            ->andWhere('prices.from = 1')
            ->andWhere('product.active = 1')
            ->andWhere('variant.active = 1')
            ->setParameter(':products', $ids, Connection::PARAM_INT_ARRAY)
            ->setParameter(':customerGroup', $customerGroup->getKey());

        /**
         * This part of the query handles the closeout products.
         *
         * The laststock column contains "1" if the product is a closeout product.
         * In the case that the product contains the closeout flag,
         * the stock and minpurchase are used as the defined in the database
         *
         * In the case that the product isn't a closeout product,
         * the stock and minpurchase are set to 0
         */
        $query->andWhere(
            '(product.laststock * variant.instock) >= (product.laststock * variant.minpurchase)'
        );

        $query->orderBy('calculated', 'ASC')
            ->groupBy('product.id');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $prices = array();
        foreach($data as $row) {
            $product = $row['articledetailsID'];
            $row['price'] = $row['calculated'];
            $row['pseudoprice'] = $row['calculatedPseudo'];

            $prices[$product] = $this->priceHydrator->hydrateCheapestPrice($row);
        }

        return $prices;
    }

    /**
     * Returns the cheapest product price struct for the passed customer group.
     *
     * The cheapest product price is selected over all product variations.
     *
     * This means that the query uses the s_articles_prices.articleID column for the where condition.
     * The articleID is stored in the Struct\ListProduct::id property.
     *
     * It is important that the cheapest price contains the associated product Struct\Unit of the
     * associated product variation.
     *
     * For example:
     *  - Current product variation is the SW2000
     *    - This product variation contains no associated Struct\Unit
     *  - The cheapest variant price is associated to the SW2000.2
     *    - This product variation contains an associated Struct\Unit
     *  - The unit of SW2000.2 has to be set into the Struct\Price::unit property!
     *
     * @param \Shopware\Struct\ListProduct $product
     * @param Struct\Customer\Group $customerGroup
     * @return Struct\Product\PriceRule
     */
    public function get(Struct\ListProduct $product, Struct\Customer\Group $customerGroup)
    {
        $query = $this->entityManager->getDBALQueryBuilder();

        $query->select($this->getPriceFields())
            ->addSelect($this->getUnitFields())
            ->addSelect($this->getTableFields('s_articles_prices_attributes', 'attribute'))
            ->addSelect(array(
                '(prices.price * variant.minpurchase) as calculated',
                '(prices.pseudoprice * variant.minpurchase) as calculatedPseudo'
            ));

        $query->from('s_articles_prices', 'prices')
            ->innerJoin('prices', 's_articles_details', 'variant', 'variant.id = prices.articledetailsID')
            ->innerJoin('variant', 's_articles', 'product', 'product.id = variant.articleID')
            ->leftJoin('variant', 's_core_units', 'unit', 'unit.id = variant.unitID')
            ->leftJoin('prices', 's_articles_prices_attributes', 'attribute', 'attribute.priceID = prices.id');

        $query->andWhere('prices.articleID = :product')
            ->andWhere('prices.pricegroup = :customerGroup')
            ->andWhere('prices.from = 1')
            ->andWhere('product.active = 1')
            ->andWhere('variant.active = 1')
            ->setParameter(':product', $product->getId())
            ->setParameter(':customerGroup', $customerGroup->getKey());

        if ($product->isCloseouts()) {
            $query->andWhere('variant.instock >= variant.minpurchase');
        }

        $query->orderBy('calculated', 'ASC')
            ->setFirstResult(0)
            ->setMaxResults(1);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetch(\PDO::FETCH_ASSOC);

        $data['price'] = $data['calculated'];
        $data['pseudoprice'] = $data['calculatedPseudo'];

        return $this->priceHydrator->hydrateCheapestPrice($data);
    }

    /**
     * Returns the highest percentage discount for the
     * customer group of the passed price group and quantity.
     *
     * @param Struct\Product\PriceGroup $priceGroup
     * @param Struct\Customer\Group $customerGroup
     * @param $quantity
     * @return float
     */
    public function getPriceGroupDiscount(
        Struct\Product\PriceGroup $priceGroup,
        Struct\Customer\Group $customerGroup,
        $quantity
    )
    {
        $query = $this->entityManager->getDBALQueryBuilder();
        $query->select(array('discounts.discount'))
            ->from('s_core_pricegroups_discounts', 'discounts')
            ->andWhere('discounts.groupID = :priceGroup')
            ->andWhere('discounts.customergroupID = :customerGroup')
            ->andWhere('discounts.discountstart <= :quantity')
            ->orderBy('discounts.discount', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1);

        $query->setParameter(':priceGroup', $priceGroup->getId())
            ->setParameter(':customerGroup', $customerGroup->getId())
            ->setParameter(':quantity', $quantity);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        return $statement->fetch(\PDO::FETCH_COLUMN);
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