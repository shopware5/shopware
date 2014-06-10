<?php

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Struct;
use Shopware\Gateway\DBAL\Hydrator as Hydrator;


class CheapestPrice
{
    /**
     * @var \Shopware\Gateway\DBAL\Hydrator\Price
     */
    private $priceHydrator;

    /**
     * The FieldHelper class is used for the
     * different table column definitions.
     *
     * This class helps to select each time all required
     * table data for the store front.
     *
     * Additionally the field helper reduce the work, to
     * select in a second step the different required
     * attribute tables for a parent table.
     *
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @param ModelManager $entityManager
     * @param FieldHelper $fieldHelper
     * @param Hydrator\Price $priceHydrator
     */
    function __construct(
        ModelManager $entityManager,
        FieldHelper $fieldHelper,
        Hydrator\Price $priceHydrator
    ) {
        $this->entityManager = $entityManager;
        $this->priceHydrator = $priceHydrator;
        $this->fieldHelper = $fieldHelper;
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
     * @param \Shopware\Struct\Context $context
     * @param Struct\Customer\Group $customerGroup
     * @return Struct\Product\PriceRule[]
     */
    public function getList(array $products, Struct\Context $context, Struct\Customer\Group $customerGroup)
    {
        /**
         * Contains the cheapest price logic which product price should be selected.
         */
        $ids = $this->getCheapestPriceIds($products, $customerGroup);

        $query = $this->entityManager->getDBALQueryBuilder();

        $query->select($this->fieldHelper->getPriceFields())
            ->addSelect($this->fieldHelper->getUnitFields())
            ->addSelect(array(
                'unitTranslation.objectdata as __unit_translation',
                'variantTranslation.objectdata as __variant_translation',
            ));

        $query->from('s_articles_prices', 'price')
            ->innerJoin('price', 's_articles_details', 'variant', 'variant.id = price.articledetailsID')
            ->innerJoin('variant', 's_articles', 'product', 'product.id = variant.articleID')
            ->leftJoin('variant', 's_core_units', 'unit', 'unit.id = variant.unitID')
            ->leftJoin('price', 's_articles_prices_attributes', 'priceAttribute', 'priceAttribute.priceID = price.id');

        $query->leftJoin(
            'unit',
            's_core_translations',
            'unitTranslation',
            'unitTranslation.objecttype = :unitType AND
             unitTranslation.objectkey = 1 AND
             unitTranslation.objectlanguage = :language'
        );

        $query->leftJoin(
            'variant',
            's_core_translations',
            'variantTranslation',
            'variantTranslation.objecttype = :variantType AND
             variantTranslation.objectkey = variant.id AND
             variantTranslation.objectlanguage = :language'
        );

        $query->andWhere('price.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY)
            ->setParameter(':unitType', 'config_units')
            ->setParameter(':variantType', 'variant')
            ->setParameter(':language', $context->getShop()->getId());

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $prices = array();
        foreach ($data as $row) {
            $product = $row['__price_articleID'];

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

        $query->select('(' . $subQuery->getSQL() . ') as priceId')
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
}