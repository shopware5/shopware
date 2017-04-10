<?php
declare(strict_types=1);
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

namespace Shopware\Bundle\StoreFrontBundle\ShippingMethod;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Common\FieldHelper;

use Shopware\Bundle\CartBundle\Infrastructure\SortArrayByKeysTrait;

use Shopware\Bundle\StoreFrontBundle\Context\TranslationContext;

class ShippingMethodGateway
{
    use SortArrayByKeysTrait;

    /**
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\ShippingMethod\ShippingMethodHydrator
     */
    private $hydrator;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(
        FieldHelper $fieldHelper,
        ShippingMethodHydrator $hydrator,
        Connection $connection
    ) {
        $this->fieldHelper = $fieldHelper;
        $this->hydrator = $hydrator;
        $this->connection = $connection;
    }

    /**
     * @param int[]              $ids
     * @param \Shopware\Bundle\StoreFrontBundle\Context\TranslationContext $context
     *
     * @return ShippingMethod[]
     */
    public function getList(array $ids, TranslationContext $context): array
    {
        if (0 === count($ids)) {
            return [];
        }
        $query = $this->createQuery($context);

        $query->where('shippingMethod.id IN (:ids)');
        $query->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $data = $query->execute()->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE);
        $services = [];
        foreach ($data as $id => $row) {
            $services[$id] = $this->hydrator->hydrate($row);
        }

        return $this->sortIndexedArrayByKeys($ids, $services);
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Context\TranslationContext $context
     *
     * @return ShippingMethod[]
     */
    public function getAll(TranslationContext $context): array
    {
        $query = $this->createQuery($context);

        $data = $query->execute()->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE);

        $services = [];
        foreach ($data as $id => $row) {
            $services[$id] = $this->hydrator->hydrate($row);
        }

        return $services;
    }

    private function createQuery(TranslationContext $context): QueryBuilder
    {
        $query = $this->connection->createQueryBuilder();
        $query->select('shippingMethod.id as arrayKey');
        $query->addSelect($this->fieldHelper->getShippingMethodFields());

        $query->from('s_premium_dispatch', 'shippingMethod');

        $query->leftJoin(
            'shippingMethod',
            's_premium_dispatch_attributes',
            'shippingMethodAttribute',
            'shippingMethodAttribute.dispatchID = shippingMethod.id'
        );

        $this->fieldHelper->addDeliveryTranslation($query, $context);

        return $query;
    }
}
