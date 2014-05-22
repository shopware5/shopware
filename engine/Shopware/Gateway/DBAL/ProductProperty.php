<?php

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Gateway\DBAL\Hydrator as Hydrator;
use Shopware\Struct;

class ProductProperty extends Gateway
{
    /**
     * @var \Shopware\Gateway\DBAL\Hydrator\Property
     */
    private $propertyHydrator;

    /**
     * @param ModelManager $entityManager
     * @param Hydrator\Property $propertyHydrator
     */
    function __construct(
        ModelManager $entityManager,
        Hydrator\Property $propertyHydrator
    ) {
        $this->propertyHydrator = $propertyHydrator;
        $this->entityManager = $entityManager;
    }

    /**
     * @param Struct\Product[] $products
     * @return Struct\Property\Set[]
     */
    public function getList(array $products)
    {
        $ids = array();
        foreach ($products as $product) {
            $ids[] = $product->getId();
        }

        $query = $this->entityManager->getDBALQueryBuilder();

        $query->addSelect('products.id as productId')
            ->addSelect($this->getSetFields())
            ->addSelect($this->getGroupFields())
            ->addSelect($this->getOptionFields())
            ->addSelect($this->getTableFields('s_filter_attributes', 'attribute'));

        $query->from('s_filter_articles', 'filterArticles');

        $query->innerJoin(
            'filterArticles',
            's_articles',
            'products',
            'products.id = filterArticles.articleID'
        );

        $query->innerJoin(
            'filterArticles',
            's_filter_values',
            'options',
            'options.id = filterArticles.valueID'
        );

        $query->innerJoin(
            'products',
            's_filter',
            'sets',
            'sets.id = products.filtergroupID'
        );

        $query->leftJoin(
            'sets',
            's_filter_attributes',
            'attribute',
            'attribute.filterID = sets.id'
        );

        $query->innerJoin(
            'sets',
            's_filter_relations',
            'relations',
            'relations.groupID = sets.id'
        );

        $query->innerJoin(
            'options',
            's_filter_options',
            'groups',
            'groups.id = options.optionID AND relations.optionID = groups.id'
        );

        $query->where('products.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $query->orderBy('filterArticles.articleID')
            ->addOrderBy('sets.position')
            ->addOrderBy('relations.position')
            ->addOrderBy('options.position')
            ->addOrderBy('options.id');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_GROUP);

        $properties = array();
        foreach ($data as $productId => $values) {
            $properties[$productId] = $this->propertyHydrator->hydrateValues($values);
        }

        $result = array();
        foreach ($products as $product) {
            $sets = $properties[$product->getId()];
            $result[$product->getNumber()] = array_shift($sets);
        }

        return $result;
    }

    private function getSetFields()
    {
        return array(
            'sets.id',
            'sets.name',
            'sets.position',
            'sets.comparable',
            'sets.sortmode'
        );
    }

    private function getGroupFields()
    {
        return array(
            'groups.id as __groups_id',
            'groups.name as __groups_name',
            'groups.filterable as __groups_filterable',
            'groups.default as __groups_default'
        );
    }

    private function getOptionFields()
    {
        return array(
            'options.id as __options_id',
            'options.optionID as __options_optionID',
            'options.value as __options_value',
            'options.position as __options_position',
            'options.value_numeric as __options_value_numeric'
        );
    }
}