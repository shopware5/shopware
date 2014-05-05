<?php

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Gateway\DBAL\Hydrator as Hydrator;
use Shopware\Struct;

class Property extends Gateway
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
     * @param array $ids
     * @return Struct\Property\Set[]
     */
    public function getList(array $ids)
    {
        $query = $this->entityManager->getDBALQueryBuilder();

        $query->addSelect($this->getSetFields())
            ->addSelect($this->getGroupFields())
            ->addSelect($this->getOptionFields())
            ->addSelect($this->getTableFields('s_filter_attributes', 'attribute'));

        $query->from('s_filter', 'sets');

        $query->innerJoin(
            'sets',
            's_filter_relations',
            'relations',
            'relations.groupID = sets.id'
        );

        $query->leftJoin(
            'sets',
            's_filter_attributes',
            'attribute',
            'attribute.filterID = sets.id'
        );

        $query->innerJoin(
            'relations',
            's_filter_options',
            'groups',
            'relations.optionID = groups.id'
        );

        $query->innerJoin(
            'groups',
            's_filter_values',
            'options',
            'options.optionID = groups.id'
        );

        $query->where('options.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $query->addOrderBy('sets.position')
            ->addOrderBy('relations.position')
            ->addOrderBy('options.position')
            ->addOrderBy('options.id');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $rows = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return $this->propertyHydrator->hydrateValues($rows);
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


    /**
     * Returns the property set for the passed product.
     *
     * The property has to be loaded with all property groups
     * and values of the product.
     *
     * @param Struct\ListProduct $product
     * @return Struct\Property\Set
     */
    public function getProductSet(Struct\ListProduct $product)
    {
        $set = $this->getSet($product);

        $set['attribute'] = $this->getTableRow(
            's_filter_attributes',
            $set['id'],
            'filterID'
        );

        $set['options'] = $this->getPropertiesOfProduct($product, $set);

        return $this->propertyHydrator->hydrate($set);
    }

    private function getPropertiesOfProduct(Struct\ListProduct $product, $setData)
    {
        $query = $this->entityManager->getDBALQueryBuilder();

        $query->select(
            array(
                'value.id as value_id',
                'value.value ',
                'value.value_numeric',
                'options.id as option_id',
                'options.name as option_name',
            )
        );

        $query->from('s_filter_values', 'value')
            ->innerJoin('value', 's_filter_articles', 'articles', 'value.id = articles.valueID')
            ->innerJoin('value', 's_filter_options', 'options', 'options.id = value.optionID')
            ->innerJoin(
                'value',
                's_filter_relations',
                'relations',
                'value.optionID = relations.optionID AND relations.groupID = :setId'
            )
            ->addOrderBy('relations.position', 'ASC')
            ->where('articles.articleID = :productId')
            ->setParameter(':productId', $product->getId())
            ->setParameter(':setId', $setData['id']);

        switch ($setData['sortmode']) {
            case self::FILTERS_SORT_ALPHANUMERIC:
                $query->addOrderBy('value.value');
                break;

            case self::FILTERS_SORT_NUMERIC:
                $query->addOrderBy('value.value_numeric');
                break;

            case self::FILTERS_SORT_ARTICLE_COUNT:
            case self::FILTERS_SORT_POSITION:
            default:
                $query->addOrderBy('value.position');
                break;
        }

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }


    private function getSet(Struct\ListProduct $product)
    {
        $query = $this->entityManager->getDBALQueryBuilder();

        $query->select('sets.*')
            ->from('s_filter', 'sets')
            ->innerJoin('sets', 's_articles', 'articles', 'articles.filtergroupID = sets.id')
            ->where('articles.id = :productId')
            ->setParameter(':productId', $product->getId());

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        return $statement->fetch(\PDO::FETCH_ASSOC);
    }


    /**
     * Helper function which selects a whole table by a specify identifier.
     *
     * @param $table
     * @param $id
     * @param string $column
     * @return mixed
     */
    protected function getTableRow($table, $id, $column = 'id')
    {
        $query = $this->entityManager->getDBALQueryBuilder();
        $query->select(array('*'))
            ->from($table, 'entity')
            ->where('entity.' . $column . ' = :id')
            ->setParameter(':id', $id);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        return $statement->fetch(\PDO::FETCH_ASSOC);
    }
}