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

namespace Shopware\Bundle\CartBundle\Infrastructure\Payment;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\CartBundle\Domain\Payment\PaymentService;
use Shopware\Bundle\CartBundle\Infrastructure\SortArrayByKeysTrait;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\FieldHelper;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class PaymentServiceGateway
{
    use SortArrayByKeysTrait;

    /**
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @var paymentServiceHydrator
     */
    private $hydrator;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param FieldHelper $fieldHelper
     * @param PaymentServiceHydrator $hydrator
     * @param Connection $connection
     */
    public function __construct(
        FieldHelper $fieldHelper,
        PaymentServiceHydrator $hydrator,
        Connection $connection
    ) {
        $this->fieldHelper = $fieldHelper;
        $this->hydrator = $hydrator;
        $this->connection = $connection;
    }

    /**
     * @param int[] $ids
     * @param ShopContextInterface $context
     * @return PaymentService[]
     */
    public function getList($ids, ShopContextInterface $context)
    {
        if (0 === count($ids)) {
            return [];
        }
        $query = $this->connection->createQueryBuilder();
        $query->select('paymentService.id as arrayKey');
        $query->addSelect($this->fieldHelper->getPaymentServiceFields());
        $query->from('s_core_paymentmeans', 'paymentService');
        $query->leftJoin('paymentService', 's_core_paymentmeans_attributes', 'paymentServiceAttribute', 'paymentServiceAttribute.paymentmeanID = paymentService.id');

        $query->where('paymentService.id IN (:ids)');
        $query->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);
        $data = $query->execute()->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_UNIQUE);

        $services = [];
        foreach ($data as $id => $row) {
            $services[$id] = $this->hydrator->hydrate($row);
        }
        return $this->sortIndexedArrayByKeys($ids, $services);
    }
}
