<?php

namespace Shopware\Gateway\DBAL;

use Shopware\Components\Model\ModelManager;
use Shopware\Struct as Struct;

class Tax implements \Shopware\Gateway\Tax
{
    /**
     * @var \Shopware\Hydrator\DBAL\Tax
     */
    private $taxHydrator;

    /**
     * @var \Shopware\Components\Model\ModelManager
     */
    private $entityManager;

    /**
     * @param \Shopware\Components\Model\ModelManager $entityManager
     * @param \Shopware\Hydrator\DBAL\Tax $taxHydrator
     */
    function __construct(
        ModelManager $entityManager,
        \Shopware\Hydrator\DBAL\Tax $taxHydrator
    )
    {
        $this->entityManager = $entityManager;
        $this->taxHydrator = $taxHydrator;
    }

    /**
     * @param Struct\CustomerGroup $customerGroup
     * @param Struct\Area $area
     * @param Struct\Country $country
     * @param Struct\State $state
     * @return Struct\Tax[]
     */
    public function getRules(
        Struct\CustomerGroup $customerGroup,
        Struct\Area $area = null,
        Struct\Country $country = null,
        Struct\State $state = null
    )
    {
        $query = $this->entityManager->getDBALQueryBuilder();
        $query->select(array(
            'tax.id',
            'tax.description as name',
            'tax.tax'
        ))
            ->from('s_core_tax', 'tax');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();
        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $rules = array();

        $query = $this->getAreaQuery($customerGroup, $area, $country, $state);

        foreach ($data as $tax) {
            $query->setParameter(':taxId', $tax['id']);

            /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
            $statement = $query->execute();

            $area = $statement->fetch(\PDO::FETCH_ASSOC);

            if (!empty($area['tax'])) {
                $area['name'] = $tax['name'];

                $rule = $this->taxHydrator->hydrate($area);
            } else {
                $rule = $this->taxHydrator->hydrate($tax);
            }

            $key = 'tax_' . $tax['id'];
            $rules[$key] = $rule;
        }

        return $rules;
    }

    private function getAreaQuery(
        Struct\CustomerGroup $customerGroup = null,
        Struct\Area $area = null,
        Struct\Country $country = null,
        Struct\State $state = null
    ) {

        $query = $this->entityManager->getDBALQueryBuilder();

        $query->select(array(
            'rule.groupID as id',
            'rule.tax',
            'rule.name'
        ));

        $query->from('s_core_tax_rules', 'rule');

        $areaId = ($area) ? $area->getId() : null;
        $countryId = ($country) ? $country->getId() : null;
        $stateId = ($state) ? $state->getId() : null;

        $query->andWhere('(rule.areaID = :area OR rule.areaID IS NULL)')
            ->setParameter(':area',$areaId);

        $query->andWhere('(rule.countryID = :country OR rule.countryID IS NULL)')
            ->setParameter(':country', $countryId);

        $query->andWhere('(rule.stateID = :state OR rule.stateID IS NULL)')
            ->setParameter(':state', $stateId);

        $query->andWhere('(rule.customer_groupID = :customerGroup OR rule.customer_groupID IS NULL)')
            ->setParameter(':customerGroup', $customerGroup->getId());

        $query->andWhere('rule.groupID = :taxId')
            ->andWhere('rule.active = 1');

        $query->orderBy('rule.customer_groupID', 'DESC')
            ->addOrderBy('rule.areaID', 'DESC')
            ->addOrderBy('rule.countryID', 'DESC')
            ->addOrderBy('rule.stateID', 'DESC');

        $query->setFirstResult(0)
            ->setMaxResults(1);

        return $query;
    }

}