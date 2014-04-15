<?php

namespace Shopware\Gateway\DBAL;

use Shopware\Components\Model\ModelManager;
use Shopware\Gateway\DBAL\Hydrator as Hydrator;
use Shopware\Struct;

class Property implements \Shopware\Gateway\Property
{
    /**
     * @var \Shopware\Components\Model\ModelManager
     */
    private $entityManager;

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

        $set['attribute'] = $this->getTableRow(
            's_filter_attributes',
            $set['id'],
            'filterID'
        );

        $set['options'] = $this->getPropertiesOfProduct($product, $set);

        return $this->propertyHydrator->hydrate($set);
    }

    private function getPropertiesOfProduct(Struct\ProductMini $product, $setData)
    {
        $query = $this->entityManager->getDBALQueryBuilder();

        $query->select(array(
            'value.id as value_id',
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