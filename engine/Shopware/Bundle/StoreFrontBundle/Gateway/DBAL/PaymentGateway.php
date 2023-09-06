<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use PDO;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\PaymentHydrator;
use Shopware\Bundle\StoreFrontBundle\Gateway\PaymentGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class PaymentGateway implements PaymentGatewayInterface
{
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

    private PaymentHydrator $paymentHydrator;

    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        PaymentHydrator $paymentHydrator
    ) {
        $this->connection = $connection;
        $this->fieldHelper = $fieldHelper;
        $this->paymentHydrator = $paymentHydrator;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(array $paymentIds, ShopContextInterface $context)
    {
        $paymentIds = array_unique($paymentIds);

        $query = $this->connection->createQueryBuilder();

        $query->addSelect($this->fieldHelper->getPaymentFields());

        $query->from('s_core_paymentmeans', 'payment')
            ->leftJoin('payment', 's_core_paymentmeans_attributes', 'paymentAttribute', 'paymentAttribute.paymentmeanID = payment.id')
            ->where('payment.id IN (:ids)')
            ->addOrderBy('position', 'ASC')
            ->addOrderBy('name', 'ASC')
            ->setParameter(':ids', $paymentIds, Connection::PARAM_INT_ARRAY);

        $this->fieldHelper->addPaymentTranslation($query, $context);

        $data = $query->execute()->fetchAll(PDO::FETCH_ASSOC);

        $payments = [];
        foreach ($data as $row) {
            $id = (int) $row['__payment_id'];
            $payments[$id] = $this->paymentHydrator->hydrate($row);
        }

        return $payments;
    }
}
