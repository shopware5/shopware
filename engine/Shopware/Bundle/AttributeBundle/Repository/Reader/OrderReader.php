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

namespace Shopware\Bundle\AttributeBundle\Repository\Reader;

use Doctrine\DBAL\Connection;
use PDO;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Order;

class OrderReader extends GenericReader
{
    /**
     * @var \Enlight_Components_Snippet_Manager
     */
    private $snippets;

    /**
     * @param string $entity
     */
    public function __construct($entity, ModelManager $entityManager, \Enlight_Components_Snippet_Manager $snippets)
    {
        parent::__construct($entity, $entityManager);
        $this->snippets = $snippets;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($identifiers)
    {
        $data = parent::getList($identifiers);
        $documents = $this->getDocuments($data);

        $namespace = $this->snippets->getNamespace('backend/static/order_status');

        foreach ($data as &$row) {
            $row['orderStateName'] = $namespace->get($row['orderStateKey']);
            $row['orderDocuments'] = $this->getOrderDocuments($documents, $row);
        }

        return $data;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder|\Shopware\Components\Model\QueryBuilder
     */
    protected function createListQuery()
    {
        $query = $this->entityManager->createQueryBuilder();
        $query->select([
            'entity.id',
            'entity.number',
            'entity.invoiceAmount',
            'entity.invoiceShipping',
            'entity.orderTime',
            'entity.status',
            'entity.transactionId',
            'entity.orderTime',
            'entity.cleared',
            'customer.id as customerId',
            'customer.email as email',
            'customer.groupKey as groupKey',
            'billing.firstName as firstname',
            'billing.lastName as lastname',
            'payment.id as paymentId',
            'payment.description as paymentName',
            'dispatch.id as dispatchId',
            'dispatch.name as dispatchName',
            'shop.id as shopId',
            'shop.name as shopName',
            'billing.title',
            'billing.company',
            'billing.department',
            'billing.street',
            'billing.zipCode',
            'billing.city',
            'billing.phone',
            'shipping.countryId as shippingCountryId',
            'billing.countryId as billingCountryId',
            'billingCountry.name as countryName',
            'orderStatus.id as orderStateId',
            'orderStatus.name as orderStateKey',
        ]);
        $query->from(Order::class, 'entity', $this->getIdentifierField());
        $query->leftJoin('entity.payment', 'payment');
        $query->leftJoin('entity.orderStatus', 'orderStatus');
        $query->leftJoin('entity.dispatch', 'dispatch');
        $query->leftJoin('entity.shop', 'shop');
        $query->leftJoin('entity.customer', 'customer');
        $query->leftJoin('entity.billing', 'billing');
        $query->leftJoin('entity.shipping', 'shipping');
        $query->leftJoin('billing.country', 'billingCountry');
        $query->andWhere('entity.number IS NOT NULL');
        $query->andWhere('entity.status != :cancelStatus');
        $query->setParameter(':cancelStatus', -1);

        return $query;
    }

    /**
     * @return array
     */
    private function getDocuments(array $orders)
    {
        $query = $this->entityManager->getConnection()->createQueryBuilder();
        $query->select('documents.orderID', 'documents.docID');
        $query->from('s_order_documents', 'documents');
        $query->where('documents.orderID IN (:ids)');
        $query->setParameter(':ids', array_keys($orders), Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array
     */
    private function getOrderDocuments(array $documents, array $row)
    {
        return array_values(array_filter(array_column(array_filter($documents, function (array $document) use ($row) {
            return (int) $document['orderID'] === (int) $row['id'];
        }), 'docID')));
    }
}
