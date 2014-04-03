<?php

namespace Shopware\Gateway\ORM;

use Doctrine\ORM\AbstractQuery;
use Shopware\Components\Model\ModelManager;
use Shopware\Struct as Struct;
use Shopware\Hydrator\ORM as Hydrator;

class Property
{

    /**
     * Constant for the alphanumeric sort configuration of the category filters
     */
    const FILTERS_SORT_ALPHANUMERIC = 0;

    /**
     * Constant for the numeric sort configuration of the category filters
     */
    const FILTERS_SORT_NUMERIC = 1;

    /**
     * Constant for the article count sort configuration of the category filters
     */
    const FILTERS_SORT_ARTICLE_COUNT = 2;

    /**
     * Constant for the position sort configuration of the category filters
     */
    const FILTERS_SORT_POSITION = 3;

    /**
     * @var ModelManager
     */
    private $entityManager;

    /**
     * @var Hydrator\Property
     */
    private $propertyHydrator;

    /**
     * @param ModelManager $entityManager
     * @param \Shopware\Hydrator\ORM\Property $propertyHydrator
     */
    function __construct(
        ModelManager $entityManager,
        Hydrator\Property $propertyHydrator)
    {
        $this->entityManager = $entityManager;
        $this->propertyHydrator = $propertyHydrator;
    }

    public function getProductSet(Struct\ProductMini $product)
    {
        $builder = $this->getProductSetQuery($product);

        $data = $builder->getQuery()->getOneOrNullResult(
            AbstractQuery::HYDRATE_ARRAY
        );

        if ($data === null) {
            return array();
        }

        $data['options'] = array_column($data['relations'], 'option');
        unset($data['relations']);

        return $this->propertyHydrator->hydrateSet($data);
    }

    /**
     * @param Struct\ProductMini $product
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getProductSetQuery(Struct\ProductMini $product)
    {
        $sortMode = $this->getSortModeOfProductSet($product);

        $builder = $this->getPropertySetQuery()
            ->innerJoin('values.articles', 'articles')
            ->where('articles.id = :productId')
            ->setParameter('productId', $product->getId());

        switch ($sortMode) {
            case self::FILTERS_SORT_ALPHANUMERIC:
                $builder->addOrderBy('values.value');
                break;

            case self::FILTERS_SORT_NUMERIC:
                $builder->addOrderBy('values.valueNumeric');
                break;

            case self::FILTERS_SORT_ARTICLE_COUNT:
            case self::FILTERS_SORT_POSITION:
            default:
                $builder->addOrderBy('values.position');
                break;
        }

        return $builder;
    }

    /**
     * @return \Shopware\Components\Model\QueryBuilder
     */
    private function getPropertySetQuery()
    {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select(array('sets', 'relations', 'options', 'values'))
            ->from('Shopware\Models\Property\Group', 'sets')
            ->innerJoin('sets.relations', 'relations')
            ->innerJoin('relations.option', 'options')
            ->innerJoin('options.values', 'values')
            ->addOrderBy('relations.position');

        return $builder;
    }

    /**
     * Returns the sort mode for the filter selection.
     *
     * @param Struct\ProductMini $product
     * @return int
     */
    private function getSortModeOfProductSet(Struct\ProductMini $product)
    {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select(array('sets.sortMode'))
            ->from('Shopware\Models\Article\Article', 'articles')
            ->innerJoin('articles.propertyGroup', 'sets')
            ->where('articles.id = :productId')
            ->setParameter('productId', $product->getId());

        $data = $builder->getQuery()->getOneOrNullResult(
            AbstractQuery::HYDRATE_ARRAY
        );

        if ($data == null) {
            return 1;
        }

        return $data['sortMode'];
    }

}
