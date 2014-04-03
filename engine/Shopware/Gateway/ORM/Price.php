<?php

namespace Shopware\Gateway\ORM;

use Doctrine\ORM\AbstractQuery;
use Shopware\Components\Model\ModelManager;
use Shopware\Gateway\Exception\NoCustomerGroupPriceFoundException;
use Shopware\Struct as Struct;
use Shopware\Hydrator\ORM as Hydrator;

class Price
{
    /**
     * @var \Shopware\Components\Model\ModelManager
     */
    private $entityManager;

    /**
     * @var \Shopware\Hydrator\ORM\Price
     */
    private $priceHydrator;

    /**
     * @param ModelManager $entityManager
     * @param Hydrator\Price $priceHydrator
     */
    function __construct(ModelManager $entityManager, Hydrator\Price $priceHydrator)
    {
        $this->entityManager = $entityManager;
        $this->priceHydrator = $priceHydrator;
    }

    /**
     * This function returns the scaled customer group prices for the passed product.
     * If no prices found the function throws the NoCustomerGroupPriceFoundException exception.
     *
     * @param Struct\ProductMini $product
     * @param Struct\CustomerGroup $customerGroup
     * @return Struct\Price[]
     */
    public function getProductPrices(Struct\ProductMini $product, Struct\CustomerGroup $customerGroup)
    {
        $builder = $this->getPriceQuery()
            ->where('price.articleDetailsId = :variantId')
            ->andWhere('price.customerGroupKey = :customerGroupKey')
            ->orderBy('price.from', 'ASC')
            ->setParameter('variantId', $product->getVariantId());

        $builder->setParameter(
            'customerGroupKey',
            $customerGroup->getKey()
        );

        $data = $builder->getQuery()->getArrayResult();

        if (empty($data)) {
            return array();
        }

        $prices = array();
        foreach($data as $price) {
            $prices[] = $this->priceHydrator->hydrate($price);
        }

        return $prices;
    }

    /**
     * Returns the cheapest product price struct for the passed customer group.
     *
     * If no customer group found, the function throws the NoCustomerGroupPriceFoundException
     * exception.
     *
     * @param Struct\ProductMini $product
     * @param Struct\CustomerGroup $customerGroup
     * @return Struct\Price
     */
    public function getCheapestProductPrice(Struct\ProductMini $product, Struct\CustomerGroup $customerGroup)
    {
        $builder = $this->getCheapestPriceQuery()
            ->where('price.articleId = :productId')
            ->andWhere('price.customerGroupKey = :customerGroup')
            ->setParameter('productId', $product->getId())
            ->setParameter('customerGroup', $customerGroup->getKey());

        $data = $builder->getQuery()->getOneOrNullResult(
            AbstractQuery::HYDRATE_ARRAY
        );


        if (empty($data)) {
            return null;
        }

        return $this->priceHydrator->hydrateCheapestPrice($data);
    }

    /**
     * Creates a query builder which selects the basic image data
     * and restricts the query to select only the cheapest price.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getCheapestPriceQuery()
    {
        $builder = $this->getPriceQuery()
            ->addSelect(array('detail', 'unit'))
            ->innerJoin('price.detail', 'detail')
            ->leftJoin('detail.unit', 'unit')
            ->orderBy('price.price', 'ASC')
            ->setFirstResult(0)
            ->setMaxResults(1);

        return $builder;
    }


    /**
     * Helper function which creates the default price selection query.
     * This query is the base query for each price selection like cheapest price
     * or product scaled prices.
     *
     * @return \Shopware\Components\Model\QueryBuilder
     */
    private function getPriceQuery()
    {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select(array('price', 'attribute', 'customerGroup'))
            ->from('Shopware\Models\Article\Price', 'price')
            ->innerJoin('price.customerGroup', 'customerGroup')
            ->leftJoin('price.attribute', 'attribute');

        return $builder;
    }
}