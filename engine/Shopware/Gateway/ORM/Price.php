<?php

namespace Shopware\Gateway\ORM;

use Shopware\Components\Model\ModelManager;
use Shopware\Struct as Struct;
use Shopware\Hydrator\ORM as Hydrator;

class Price
{
    /**
     * @var \Shopware\Components\Model\ModelManager
     */
    private $entityManager;

    private $priceHydrator;

    function __construct(ModelManager $entityManager, Hydrator\Price $priceHydrator)
    {
        $this->entityManager = $entityManager;
        $this->priceHydrator = $priceHydrator;
    }

    public function getProductPrices(Struct\ProductMini $product, Struct\GlobalState $state)
    {
        $builder = $this->getPriceQuery()
            ->where('price.articleDetailId = :variantId')
            ->orderBy('price.from', 'ASC')
            ->setParameter('variantId', $product->getVariantId());

        $data = $builder->getQuery()->getArrayResult();

        return $this->priceHydrator->hydrate($data);
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

    /**
     * @param Struct\ProductMini $product
     * @param Struct\GlobalState $state
     */
    public function getCheapestPrice(Struct\ProductMini $product, Struct\GlobalState $state)
    {
        $builder = $this->entityManager->createQueryBuilder();

        $builder->select(array('price'))
            ->from('Shopware\Models\Article\Price', 'price')
            ->where('price.articleId = :productId')
            ->andWhere('price.customerGroupKey = :customerGroup')
            ->setParameter('productId', $product->getId())
            ->orderBy('MIN(price.price)')
            ->setFirstResult(0)
            ->setMaxResults(1);



    }
}