<?php

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Gateway\DBAL\Hydrator as Hydrator;
use Shopware\Struct;

class Manufacturer extends Gateway
{
    /**
     * @var Hydrator\Manufacturer
     */
    private $manufacturerHydrator;

    function __construct(ModelManager $entityManager, Hydrator\Manufacturer $manufacturerHydrator)
    {
        $this->entityManager = $entityManager;
        $this->manufacturerHydrator = $manufacturerHydrator;
    }


    /**
     * @param $id
     * @return Struct\Product\Manufacturer
     */
    public function get($id)
    {
        $manufacturers = $this->getList(array($id));

        return array_shift($manufacturers);
    }

    /**
     * @param array $ids
     * @return Struct\Product\Manufacturer[]
     */
    public function getList(array $ids)
    {
        $query = $this->entityManager->getDBALQueryBuilder();

        $query->addSelect($this->getManufacturerFields())
            ->addSelect($this->getTableFields('s_articles_supplier_attributes', 'manufacturerAttribute'));

        $query->from('s_articles_supplier', 'manufacturer')
            ->leftJoin(
                'manufacturer',
                's_articles_supplier_attributes',
                'manufacturerAttribute',
                'manufacturerAttribute.supplierID = manufacturer.id'
            );

        $query->where('manufacturer.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $manufacturers = array();
        foreach($data as $row) {
            $manufacturers[$row['id']] = $this->manufacturerHydrator->hydrate($row);
        }

        return $manufacturers;
    }

    private function getManufacturerFields()
    {
        return array(
            'manufacturer.id',
            'manufacturer.name',
            'manufacturer.img',
            'manufacturer.link',
            'manufacturer.description',
            'manufacturer.meta_title',
            'manufacturer.meta_description',
            'manufacturer.meta_keywords'
        );
    }
}