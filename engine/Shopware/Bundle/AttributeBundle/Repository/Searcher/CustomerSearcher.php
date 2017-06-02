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

namespace Shopware\Bundle\AttributeBundle\Repository\Searcher;

use Shopware\Bundle\AttributeBundle\Repository\SearchCriteria;
use Shopware\Models\Customer\Customer;
use Shopware\Models\CustomerStream\Mapping;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.com)
 */
class CustomerSearcher extends GenericSearcher
{
    protected function createQuery(SearchCriteria $criteria)
    {
        $builder = $this->entityManager->createQueryBuilder();
        $builder->select($this->getIdentifierField());
        $builder->from(Customer::class, 'entity');
        $builder->innerJoin('entity.billing', 'billing');
        $builder->innerJoin('entity.group', 'customerGroup');
        $builder->setAlias('entity');

        if ($criteria->params && $criteria->params['streamId']) {
            $builder->innerJoin(Mapping::class, 'mapping', 'WITH', 'mapping.customerId = entity.id AND mapping.streamId = :streamId');
            $builder->setParameter(':streamId', $criteria->params['streamId']);
        }

        return $builder;
    }

    /**
     * @param SearchCriteria $criteria
     *
     * @return array
     */
    protected function getSearchFields(SearchCriteria $criteria)
    {
        return [
            'entity.email',
            'entity.number',
            'billing.firstName',
            'billing.lastName',
            'billing.company',
            'customerGroup.name',
        ];
    }
}
