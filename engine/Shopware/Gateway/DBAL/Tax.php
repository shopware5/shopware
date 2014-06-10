<?php

namespace Shopware\Gateway\DBAL;

use Shopware\Components\Model\ModelManager;
use Shopware\Struct as Struct;
use Shopware\Gateway\DBAL\Hydrator;

class Tax
{
    /**
     * @var \Shopware\Gateway\DBAL\Hydrator\Tax
     */
    private $taxHydrator;

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
     * @param \Shopware\Components\Model\ModelManager $entityManager
     * @param FieldHelper $fieldHelper
     * @param Hydrator\Tax $taxHydrator
     */
    function __construct(
        ModelManager $entityManager,
        FieldHelper $fieldHelper,
        Hydrator\Tax $taxHydrator
    ) {
        $this->entityManager = $entityManager;
        $this->taxHydrator = $taxHydrator;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * @param \Shopware\Struct\Customer\Group $customerGroup
     * @param \Shopware\Struct\Country\Area $area
     * @param \Shopware\Struct\Country $country
     * @param \Shopware\Struct\Country\State $state
     * @return Struct\Tax[]
     */
    public function getRules(
        Struct\Customer\Group $customerGroup,
        Struct\Country\Area $area = null,
        Struct\Country $country = null,
        Struct\Country\State $state = null
    ) {
        $query = $this->entityManager->getDBALQueryBuilder();
        $query->select($this->fieldHelper->getTaxFields())
            ->from('s_core_tax', 'tax');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();
        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $rules = array();

        $query = $this->getAreaQuery(
            $customerGroup,
            $area,
            $country,
            $state
        );

        foreach ($data as $tax) {
            $query->setParameter(':taxId', $tax['__tax_id']);

            /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
            $statement = $query->execute();

            $area = $statement->fetch(\PDO::FETCH_ASSOC);

            if (!empty($area['__taxRule_tax'])) {
                $area['__taxRule_name'] = $tax['__tax_description'];
                $rule = $this->taxHydrator->hydrateRule($area);
            } else {
                $rule = $this->taxHydrator->hydrate($tax);
            }

            $key = 'tax_' . $tax['__tax_id'];
            $rules[$key] = $rule;
        }

        return $rules;
    }

    private function getAreaQuery(
        Struct\Customer\Group $customerGroup = null,
        Struct\Country\Area $area = null,
        Struct\Country $country = null,
        Struct\Country\State $state = null
    ) {

        $query = $this->entityManager->getDBALQueryBuilder();

        $query->select($this->fieldHelper->getTaxRuleFields());

        $query->from('s_core_tax_rules', 'taxRule');

        $areaId = ($area) ? $area->getId() : null;
        $countryId = ($country) ? $country->getId() : null;
        $stateId = ($state) ? $state->getId() : null;

        $query->andWhere('(taxRule.areaID = :area OR taxRule.areaID IS NULL)')
            ->setParameter(':area', $areaId);

        $query->andWhere('(taxRule.countryID = :country OR taxRule.countryID IS NULL)')
            ->setParameter(':country', $countryId);

        $query->andWhere('(taxRule.stateID = :state OR taxRule.stateID IS NULL)')
            ->setParameter(':state', $stateId);

        $query->andWhere('(taxRule.customer_groupID = :customerGroup OR taxRule.customer_groupID IS NULL)')
            ->setParameter(':customerGroup', $customerGroup->getId());

        $query->andWhere('taxRule.groupID = :taxId')
            ->andWhere('taxRule.active = 1');

        $query->orderBy('taxRule.customer_groupID', 'DESC')
            ->addOrderBy('taxRule.areaID', 'DESC')
            ->addOrderBy('taxRule.countryID', 'DESC')
            ->addOrderBy('taxRule.stateID', 'DESC');

        $query->setFirstResult(0)
            ->setMaxResults(1);

        return $query;
    }

}