<?php

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Struct as Struct;
use Shopware\Gateway\DBAL\Hydrator as Hydrator;

class GraduatedPrices
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

        $query->select($this->fieldHelper->getPriceFields());
        $query->addSelect('variants.ordernumber as number');

        $query->from('s_articles_prices', 'price')
            ->innerJoin('price', 's_articles_details', 'variants', 'variants.id = price.articledetailsID')
            ->leftJoin('price', 's_articles_prices_attributes', 'priceAttribute', 'priceAttribute.priceID = price.id');

        $query->where('price.articledetailsID IN (:products)')
            ->andWhere('price.pricegroup = :customerGroup')
            ->setParameter(':products', $ids, Connection::PARAM_INT_ARRAY)
            ->setParameter(':customerGroup', $customerGroup->getKey());

        $query->orderBy('price.articledetailsID', 'ASC')
            ->addOrderBy('price.from', 'ASC');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $prices = array();
        foreach ($data as $row) {
            $product = $row['number'];

            $prices[$product][] = $this->priceHydrator->hydratePriceRule($row);
        }

        return $prices;
    }
}