<?php

namespace Shopware\Gateway\DBAL;

use Shopware\Components\Model\ModelManager;
use Shopware\Hydrator\DBAL as Hydrator;
use Shopware\Struct;

class Property implements \Shopware\Gateway\Property
{
    /**
     * @var \Shopware\Components\Model\ModelManager
     */
    private $entityManager;

    /**
     * @var \Shopware\Hydrator\DBAL\Property
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
     * Returns the property set for the passed product.
     *
     * The property has to be loaded with all property groups
     * and values of the product.
     *
     * @param Struct\ProductMini $product
     * @return Struct\PropertySet
     */
    public function getProductSet(Struct\ProductMini $product)
    {
        $set = $this->getSet($product);

        $set['options'] = $this->getPropertiesOfProduct($product, $set['id']);


        echo '<pre>';
        print_r($set);
        exit();
    }

    private function getPropertiesOfProduct(Struct\ProductMini $product, $setId)
    {
        $query = $this->entityManager->getDBALQueryBuilder();

        $query->select(array(
            'value.id as valueId',
            'value.value ',
            'value.value_numeric',

            'options.id as option_id',
            'options.name as option_name',
        ));

        $query->from('s_filter_values', 'value')
            ->innerJoin('value', 's_filter_articles', 'articles', 'value.id = articles.valueID')
            ->innerJoin('value', 's_filter_options', 'options', 'options.id = value.optionID')
            ->innerJoin(
                'value',
                's_filter_relations',
                'relations',
                'value.optionID = relations.optionID AND relations.groupID = :setId'
            )
            ->addOrderBy('value.position', 'ASC')
            ->addOrderBy('relations.position', 'ASC')
            ->where('articles.articleID = :productId')
            ->setParameter(':productId', $product->getId())
            ->setParameter(':setId', $setId)
        ;

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        echo '<pre>';
        print_r($data);
        exit();
        return $query;
    }


    private function getSet(Struct\ProductMini $product)
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
            ->where('entity.' . $column .' = :id')
            ->setParameter(':id', $id);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        return $statement->fetch(\PDO::FETCH_ASSOC);
    }
}