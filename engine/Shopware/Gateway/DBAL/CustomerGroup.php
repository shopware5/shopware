<?php

namespace Shopware\Gateway\DBAL;

use Shopware\Components\Model\ModelManager;
use Shopware\Hydrator\DBAL as Hydrator;

class CustomerGroup implements \Shopware\Gateway\CustomerGroup
{
    /**
     * @var \Shopware\Hydrator\DBAL\CustomerGroup
     */
    private $customerGroupHydrator;

    /**
     * @var \Shopware\Components\Model\ModelManager
     */
    private $entityManager;

    /**
     * @param ModelManager $entityManager
     * @param Hydrator\CustomerGroup $customerGroupHydrator
     */
    function __construct(
        ModelManager $entityManager,
        Hydrator\CustomerGroup $customerGroupHydrator
    ) {
        $this->customerGroupHydrator = $customerGroupHydrator;
        $this->entityManager = $entityManager;
    }

    /**
     * Returns a single Struct\CustomerGroup object.
     *
     * The customer group should be loaded with the CustomerGroup attributes.
     * Otherwise the customer group data isn't extendable.
     *
     * The passed $key parameter contains the alphanumeric customer group identifier
     * which stored in the s_core_customergroups.groupkey column.
     *
     * @param $key
     * @return \Shopware\Struct\CustomerGroup
     */
    public function getByKey($key)
    {
        $data = $this->getTableRow(
            's_core_customergroups',
            $key,
            'groupkey'
        );

        $data['attribute'] = $this->getTableRow(
            's_core_customergroups_attributes',
            $data['id'],
            'customerGroupID'
        );

        return $this->customerGroupHydrator->hydrate($data);
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