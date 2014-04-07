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
        $query = $this->getPriceQuery()
            ->orderBy('prices.from', 'ASC')
            ->setParameter(':product', $product->getVariantId())
            ->setParameter(':customerGroup', $customerGroup->getKey());

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $prices = array();

        foreach($data as &$row) {
            $row['attribute'] = $this->getTableRow(
                's_articles_prices_attributes',
                $row['id'],
                'priceID'
            );

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
    public function getCheapestProductPrice(
        Struct\ProductMini $product,
        Struct\CustomerGroup $customerGroup
    ) {
        $query = $this->entityManager->getDBALQueryBuilder();
        $query->select(array('*'))
            ->from('s_articles_prices', 'prices')
            ->where('prices.articleID = :product')
            ->andWhere('prices.pricegroup = :customerGroup')
            ->orderBy('prices.price', 'ASC')
            ->setParameter(':product', $product->getId())
            ->setParameter(':customerGroup', $customerGroup->getKey())
            ->setFirstResult(0)
            ->setMaxResults(1);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetch(\PDO::FETCH_ASSOC);

        $data['detail'] = $this->getTableRow(
            's_articles_details',
            $data['articledetailsID']
        );

        if (isset($data['detail']['unitID'])) {
            $data['detail']['unit'] = $this->getTableRow(
                's_core_units',
                $data['detail']['unitID']
            );
        }

        $data['attribute'] = $this->getTableRow(
            's_articles_prices_attributes',
            $data['id'],
            'priceID'
        );

        return $this->priceHydrator->hydrateCheapestPrice($data);
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getPriceQuery()
    {
        $query = $this->entityManager->getDBALQueryBuilder();
        $query->select(array('*'))
            ->from('s_articles_prices', 'prices')
            ->where('prices.articledetailsID = :product')
            ->andWhere('prices.pricegroup = :customerGroup');

        return $query;
    }

    /**
     * Helper function which selects a whole table by a specify identifier.
     * @param $table
     * @param $id
     * @param string $column
     * @return mixed
     */
    private function getTableRow($table, $id, $column = 'id')
    {
        $query = $this->entityManager->getDBALQueryBuilder();
        $query->select(array('*'))
            ->from($table, 'entity')
            ->where('entity.' . $column .' = :id')
            ->setParameter(':id', $id);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        return $statement->fetch(\PDO::FETCH_ASSOC);
    }
}