<?php

namespace Shopware\Gateway\DBAL;

use Shopware\Components\Model\ModelManager;
use Shopware\Struct as Struct;
use Shopware\Hydrator\DBAL as Hydrator;

class Price implements \Shopware\Gateway\Price
{
    /**
     * @var \Shopware\Components\Model\ModelManager
     */
    private $entityManager;

    /**
     * @var \Shopware\Hydrator\DBAL\Price
     */
    private $priceHydrator;

    /**
     * @var CustomerGroup
     */
    private $customerGroupGateway;

    /**
     * @param ModelManager $entityManager
     * @param Hydrator\Price $priceHydrator
     * @param CustomerGroup $customerGroupGateway
     */
    function __construct(
        ModelManager $entityManager,
        Hydrator\Price $priceHydrator,
        CustomerGroup $customerGroupGateway
    )
    {
        $this->entityManager = $entityManager;
        $this->priceHydrator = $priceHydrator;
        $this->customerGroupGateway = $customerGroupGateway;
    }

    /**
     * This function returns the scaled customer group prices for the passed product.
     *
     * The scaled product prices are selected over the s_articles_prices.articledetailsID column.
     * The id is stored in the Struct\ProductMini::variantId property.
     * Additionally it is important that the prices are ordered ascending by the Struct\Price::from property.
     *
     * @param Struct\ProductMini $product
     * @param Struct\CustomerGroup $customerGroup
     * @return Struct\Price[]
     */
    public function getProductPrices(
        Struct\ProductMini $product,
        Struct\CustomerGroup $customerGroup
    ) {
        $query = $this->entityManager->getDBALQueryBuilder();

        $query->select($this->getPriceFields());
        $query->addSelect($this->getTableFields('s_articles_prices_attributes', 'attribute'));

        $query->from('s_articles_prices', 'prices')
            ->leftJoin('prices', 's_articles_prices_attributes', 'attribute', 'attribute.priceID = prices.id');

        $query->where('prices.articledetailsID = :product')
            ->andWhere('prices.pricegroup = :customerGroup')
            ->setParameter(':product', $product->getVariantId())
            ->setParameter(':customerGroup', $customerGroup->getKey());

        $query->orderBy('prices.from', 'ASC');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $prices = array();

        foreach ($data as $row) {
            $price = $this->priceHydrator->hydrate($row);

            $prices[] = $price;
        }

        return $prices;
    }

    /**
     * Returns the cheapest product price struct for the passed customer group.
     *
     * The cheapest product price is selected over all product variations.
     *
     * This means that the query uses the s_articles_prices.articleID column for the where condition.
     * The articleID is stored in the Struct\ProductMini::id property.
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
     * @param Struct\ProductMini $product
     * @param Struct\CustomerGroup $customerGroup
     * @return Struct\Price
     */
    public function getCheapestPrice(
        Struct\ProductMini $product,
        Struct\CustomerGroup $customerGroup
    ) {
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
     * @param Struct\PriceGroup $priceGroup
     * @param Struct\CustomerGroup $customerGroup
     * @param $quantity
     * @return int
     */
    public function getPriceGroupDiscount(
        Struct\PriceGroup $priceGroup,
        Struct\CustomerGroup $customerGroup,
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


    private function getTableFields($table, $alias)
    {
        $schemaManager = $this->entityManager->getConnection()->getSchemaManager();

        $tableColumns = $schemaManager->listTableColumns($table);
        $columns = array();

        foreach ($tableColumns as $column) {
            $columns[] = $alias . '.' . $column->getName() . ' as __' . $alias . '_' . $column->getName();
        }

        return $columns;
    }


}