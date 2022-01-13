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
use Doctrine\ORM\Query\Expr\Join;
use Enlight_Components_Snippet_Manager;
use PDO;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Article;
use Shopware\Models\Order\Order;

class OrderReader extends GenericReader
{
    private Enlight_Components_Snippet_Manager $snippets;

    public function __construct(string $entity, ModelManager $entityManager, Enlight_Components_Snippet_Manager $snippets)
    {
        parent::__construct($entity, $entityManager);
        $this->snippets = $snippets;
    }

    public function getList($identifiers)
    {
        $orders = parent::getList($identifiers);
        $documents = $this->getDocuments($orders);

        $namespace = $this->snippets->getNamespace('backend/static/order_status');

        foreach ($orders as &$order) {
            $order['orderStateName'] = $namespace->get($order['orderStateKey']);
            $order['orderDocuments'] = $this->getOrderDocuments($documents, $order);
            $order['supplierId'] = explode(',', $order['supplierId']);
            $order['articleNumber'] = explode(',', $order['articleNumber']);
        }

        return $orders;
    }

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
            'GroupConcat(DISTINCT orderDetails.articleNumber) as articleNumber',
            'GroupConcat(DISTINCT supplier.id) as supplierId',
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
        $query->leftJoin('entity.details', 'orderDetails');
        $query->leftJoin(Article::class, 'product', Join::WITH, 'orderDetails.articleId = product.id');
        $query->leftJoin('product.supplier', 'supplier');
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
        $query->groupBy('entity.id');
        $query->setParameter(':cancelStatus', -1);

        return $query;
    }

    /**
     * @param array<array<string, mixed>> $orders
     *
     * @return array<array<string, mixed>>
     */
    private function getDocuments(array $orders): array
    {
        $query = $this->entityManager->getConnection()->createQueryBuilder();
        $query->select('documents.orderID', 'documents.docID');
        $query->from('s_order_documents', 'documents');
        $query->where('documents.orderID IN (:ids)');
        $query->setParameter(':ids', array_keys($orders), Connection::PARAM_INT_ARRAY);

        return $query->execute()->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param array<array<string, mixed>> $documents
     * @param array<string, mixed>        $order
     *
     * @return array<array<string, mixed>>
     */
    private function getOrderDocuments(array $documents, array $order): array
    {
        return array_values(array_filter(array_column(array_filter($documents, function (array $document) use ($order) {
            return (int) $document['orderID'] === (int) $order['id'];
        }), 'docID')));
    }
}
