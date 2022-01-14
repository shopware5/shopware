<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PDO;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\TaxHydrator;
use Shopware\Bundle\StoreFrontBundle\Gateway\TaxGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Country;
use Shopware\Bundle\StoreFrontBundle\Struct\Country\Area;
use Shopware\Bundle\StoreFrontBundle\Struct\Country\State;
use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group;

class TaxGateway implements TaxGatewayInterface
{
    private TaxHydrator $taxHydrator;

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
     */
    private FieldHelper $fieldHelper;

    private Connection $connection;

    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        TaxHydrator $taxHydrator
    ) {
        $this->connection = $connection;
        $this->taxHydrator = $taxHydrator;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getRules(Group $customerGroup, Area $area = null, Country $country = null, State $state = null)
    {
        $query = $this->connection->createQueryBuilder();
        $query->select($this->fieldHelper->getTaxFields())
            ->from('s_core_tax', 'tax');

        $statement = $query->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        $rules = [];

        $query = $this->getAreaQuery(
            $customerGroup,
            $area,
            $country,
            $state
        );

        foreach ($data as $tax) {
            $query->setParameter(':taxId', $tax['__tax_id']);

            $statement = $query->execute();

            $area = $statement->fetch(PDO::FETCH_ASSOC);

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
        Group $customerGroup,
        Area $area = null,
        Country $country = null,
        State $state = null
    ): QueryBuilder {
        $areaId = $area ? $area->getId() : null;
        $countryId = $country ? $country->getId() : null;
        $stateId = $state ? $state->getId() : null;

        $query = $this->connection->createQueryBuilder();
        $query->select($this->fieldHelper->getTaxRuleFields());

        $query->from('s_core_tax_rules', 'taxRule')
            ->andWhere('(taxRule.areaID = :area OR taxRule.areaID IS NULL)')
            ->andWhere('(taxRule.countryID = :country OR taxRule.countryID IS NULL)')
            ->andWhere('(taxRule.stateID = :state OR taxRule.stateID IS NULL)')
            ->andWhere('(taxRule.customer_groupID = :customerGroup OR taxRule.customer_groupID IS NULL)')
            ->andWhere('taxRule.groupID = :taxId')
            ->andWhere('taxRule.active = 1')
            ->orderBy('taxRule.customer_groupID', 'DESC')
            ->addOrderBy('taxRule.areaID', 'DESC')
            ->addOrderBy('taxRule.countryID', 'DESC')
            ->addOrderBy('taxRule.stateID', 'DESC')
            ->setParameter(':area', $areaId)
            ->setParameter(':country', $countryId)
            ->setParameter(':state', $stateId)
            ->setParameter(':customerGroup', $customerGroup->getId())
            ->setFirstResult(0)
            ->setMaxResults(1);

        return $query;
    }
}
