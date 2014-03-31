<?php

namespace Shopware\Gateway\ORM;

use Doctrine\ORM\AbstractQuery;
use Shopware\Components\Model\ModelManager;
use Shopware\Hydrator\ORM as Hydrator;
use Shopware\Struct as Struct;

/**
 * Class Product
 * @package Shopware\Gateway\ORM
 */
class Product
{
    /**
     * @var Hydrator\Product
     */
    private $hydrator;

    /**
     * @var \Shopware\Components\Model\ModelManager
     */
    private $entityManager;

    /**
     * @param Hydrator\Product $hydrator
     * @param \Shopware\Components\Model\ModelManager $entityManager
     */
    function __construct(Hydrator\Product $hydrator, ModelManager $entityManager)
    {
        $this->hydrator = $hydrator;
        $this->entityManager = $entityManager;
    }

    /**
     * @param $number
     */
    public function get($number)
    {

    }

    /**
     * Returns a minified product variant which contains only
     * simplify data of a variant.
     *
     * This product type is normally used for product overviews
     * like listings or sliders.
     *
     * To get the whole product data you can use the `get` function.
     *
     * @param string $number
     * @return Struct\ProductMini
     */
    public function getMini($number)
    {
        return $this->hydrator->hydrateMini(
            $this->getMiniData($number)
        );
    }

    /**
     * @param $number
     * @return array|mixed
     */
    private function getMiniData($number)
    {
        //selects the minified variant data for the passed number
        $builder = $this->getMiniQuery()
            ->where('detail.number = :number')
            ->setParameter('number', $number);

        $data = $builder->getQuery()->getOneOrNullResult(
            AbstractQuery::HYDRATE_ARRAY
        );

        $data['variantId'] = $data['id'];

        //merge article and variant data into one array level.
        $data = array_merge($data, $data['article']);
        unset($data['article']);

        return $data;
    }

    /**
     * @return \Shopware\Components\Model\QueryBuilder
     */
    private function getMiniQuery()
    {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select(array(
            'article', 'detail',
            'unit', 'supplier',
            'tax', 'attribute'
        ));

        $builder->from('Shopware\Models\Article\Detail', 'detail')
            ->innerJoin('detail.article', 'article')
            ->innerJoin('article.tax', 'tax')
            ->leftJoin('detail.attribute', 'attribute')
            ->leftJoin('article.supplier', 'supplier')
            ->leftJoin('detail.unit', 'unit');

        return $builder;
    }
}