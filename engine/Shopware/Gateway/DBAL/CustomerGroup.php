<?php

namespace Shopware\Gateway\DBAL;

use Shopware\Components\Model\ModelManager;
use Shopware\Gateway\DBAL\Hydrator as Hydrator;

class CustomerGroup
{
    /**
     * @var \Shopware\Gateway\DBAL\Hydrator\CustomerGroup
     */
    private $customerGroupHydrator;

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
     * @param Hydrator\CustomerGroup $customerGroupHydrator
     */
    function __construct(
        ModelManager $entityManager,
        FieldHelper $fieldHelper,
        Hydrator\CustomerGroup $customerGroupHydrator
    ) {
        $this->customerGroupHydrator = $customerGroupHydrator;
        $this->entityManager = $entityManager;
        $this->fieldHelper = $fieldHelper;
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
     * @return \Shopware\Struct\Customer\Group[]
     */
    public function getList(array $keys)
    {
        $query = $this->entityManager->getDBALQueryBuilder();
        $query->select($this->fieldHelper->getCustomerGroupFields());

        $query->from('s_core_customergroups', 'customerGroup')
            ->leftJoin(
                'customerGroup',
                's_core_customergroups_attributes',
                'customerGroupAttribute',
                'customerGroupAttribute.customerGroupID = customerGroup.id'
            );

        $query->where('customerGroup.groupkey IN (:keys)')
            ->setParameter(':keys', implode(',', $keys));

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $customerGroups = array();
        foreach ($data as $group) {
            $key = $group['groupkey'];

            $customerGroups[$key] = $this->customerGroupHydrator->hydrate($group);
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
     * @return \Shopware\Struct\Customer\Group
     */
    public function get($key)
    {
        $groups = $this->getList(array($key));

        return array_shift($groups);
    }

}
