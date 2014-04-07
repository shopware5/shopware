<?php

namespace Shopware\Gateway\DBAL;


use Shopware\Components\Model\ModelManager;
use Shopware\Hydrator\DBAL as Hydrator;
use Shopware\Struct;

/**
 * Class Product gateway.
 *
 * @package Shopware\Gateway\DBAL
 */
class Product implements \Shopware\Gateway\Product
{
    /**
     * @var \Shopware\Hydrator\DBAL\Product
     */
    private $hydrator;

    /**
     * @var \Shopware\Components\Model\ModelManager
     */
    private $entityManager;

    /**
     * @param $hydrator
     * @param ModelManager $entityManager
     */
    function __construct(ModelManager $entityManager, Hydrator\Product $hydrator)
    {
        $this->hydrator = $hydrator;
        $this->entityManager = $entityManager;
    }

    /**
     * The get function returns a full product struct.
     *
     * This function should only be used if all product data
     * are required, like on the article detail page.
     *
     * @param $number
     * @return Struct\Product
     */
    public function get($number)
    {
        // TODO: Implement get() method.
    }

    /**
     * Returns a mini struct of a product.
     *
     * This function is used for listings, search results
     * or sliders.
     *
     * @param $number
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
     * @return array
     */
    private function getMiniData($number)
    {
        $data = $this->getTableRow(
            's_articles_details',
            $number,
            'ordernumber'
        );

        $data['variantId'] = $data['id'];

        $article = $this->getTableRow('s_articles', $data['articleID']);
        $data = array_merge($data, $article);

        if ($data['unitID']) {
            $data['unit'] = $this->getTableRow('s_core_units', $data['unitID']);
        }

        if ($data['supplierID']) {
            $data['supplier'] = $this->getTableRow('s_articles_supplier', $data['supplierID']);

            $data['supplier']['attribute'] = $this->getTableRow(
                's_articles_supplier_attributes',
                $data['supplierID'],
                'supplierID'
            );
        }

        $data['attribute'] = $this->getTableRow(
            's_articles_attributes',
            $data['variantId'],
            'articledetailsID'
        );

        if ($data['taxID']) {
            $data['tax'] = $this->getTableRow('s_core_tax', $data['taxID']);
        }

        return $data;
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