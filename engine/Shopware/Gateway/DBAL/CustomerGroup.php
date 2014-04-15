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
    )
    {
        $this->customerGroupHydrator = $customerGroupHydrator;
        $this->entityManager = $entityManager;
    }

    /**
     * Returns a list of Struct\CustomerGroup object.
     *
     * The customer groups should be loaded with the CustomerGroup attributes.
     * Otherwise the customer group data isn't extendable.
     *
     * The passed $keys parameter contains the alphanumeric customer group identifier
     * which stored in the s_core_customergroups.groupkey column.
     *
     * @param array $keys
     * @return \Shopware\Struct\CustomerGroup[]
     */
    public function getByKeys(array $keys)
    {
        $query = $this->entityManager->getDBALQueryBuilder();
        $query->select($this->getCustomerGroupFields())
            ->addSelect($this->getTableFields('s_core_customergroups_attributes', 'attribute'));

        $query->from('s_core_customergroups', 'customerGroup')
            ->leftJoin('customerGroup', 's_core_customergroups_attributes', 'attribute', 'attribute.customerGroupID = customerGroup.id');

        $query->where('customerGroup.groupkey IN (:keys)')
            ->setParameter(':keys', implode(',', $keys));

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $customerGroups = array();
        foreach($data as $group) {
            $customerGroups[] = $this->customerGroupHydrator->hydrate($group);
        }

        return $customerGroups;
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
        $groups = $this->getByKeys(array($key));

        return array_shift($groups);
    }

    private function getCustomerGroupFields()
    {
        return array(
            'customerGroup.id',
            'customerGroup.groupkey',
            'customerGroup.description',
            'customerGroup.tax',
            'customerGroup.taxinput',
            'customerGroup.mode',
            'customerGroup.discount',
            'customerGroup.minimumorder',
            'customerGroup.minimumordersurcharge'
        );
    }

    /**
     * Helper function which generates an array with table column selections
     * for the passed table.
     *
     * @param $table
     * @param $alias
     * @return array
     */
    private function getTableFields($table, $alias)
    {
        $key = $table . '_' . $alias;

        if ($this->attributeFields[$key] !== null) {
            return $this->attributeFields[$key];
        }

        $schemaManager = $this->entityManager->getConnection()->getSchemaManager();

        $tableColumns = $schemaManager->listTableColumns($table);
        $columns = array();

        foreach ($tableColumns as $column) {
            $columns[] = $alias . '.' . $column->getName() . ' as __' . $alias . '_' . $column->getName();
        }

        $this->attributeFields[$key] = $columns;

        return $this->attributeFields[$key];
    }


}