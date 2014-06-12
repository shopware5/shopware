<?php

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Gateway\DBAL\Hydrator as Hydrator;
use Shopware\Struct;

class Manufacturer
{
    /**
     * @var Hydrator\Manufacturer
     */
    private $manufacturerHydrator;

    /**
     * The FieldHelper class is used for the
     * different table column definitions.
     *
     * This class helps to select each time all required
     * table data for the store front.
     *
     * Additionally the field helper reduce the work, to
     * select in a second step the different required
     * attribute tables for a parent table.
     *
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @param ModelManager $entityManager
     * @param FieldHelper $fieldHelper
     * @param Hydrator\Manufacturer $manufacturerHydrator
     */
    function __construct(
        ModelManager $entityManager,
        FieldHelper $fieldHelper,
        Hydrator\Manufacturer $manufacturerHydrator
    ) {
        $this->entityManager = $entityManager;
        $this->manufacturerHydrator = $manufacturerHydrator;
        $this->fieldHelper = $fieldHelper;
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
    public function getList(array $ids, Struct\Context $context)
    {
        $query = $this->entityManager->getDBALQueryBuilder();

        $query->addSelect($this->fieldHelper->getManufacturerFields());

        $query->from('s_articles_supplier', 'manufacturer')
            ->leftJoin(
                'manufacturer',
                's_articles_supplier_attributes',
                'manufacturerAttribute',
                'manufacturerAttribute.supplierID = manufacturer.id'
            );

        $query->where('manufacturer.id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $this->fieldHelper->addManufacturerTranslation($query);
        $query->setParameter(':language', $context->getShop()->getId());


        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $manufacturers = array();
        foreach ($data as $row) {
            $id = $row['__manufacturer_id'];
            $manufacturers[$id] = $this->manufacturerHydrator->hydrate($row);
        }

        return $manufacturers;
    }
}
