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

use Doctrine\DBAL\Connection;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use setasign\Fpdi\Fpdi;
use Shopware\Bundle\AttributeBundle\Repository\OrderRepository as AttributeOrderRepository;
use Shopware\Bundle\AttributeBundle\Repository\SearchCriteria;
use Shopware\Bundle\MailBundle\Service\LogEntryBuilder;
use Shopware\Bundle\OrderBundle\Service\CalculationServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Tax as TaxStruct;
use Shopware\Components\CSRFWhitelistAware;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Components\Random;
use Shopware\Components\StateTranslatorService;
use Shopware\Models\Article\Detail as ProductVariant;
use Shopware\Models\Article\Unit;
use Shopware\Models\Attribute\OrderDetail as OrderDetailAttribute;
use Shopware\Models\Country\Area;
use Shopware\Models\Country\Country;
use Shopware\Models\Country\Repository as CountryRepository;
use Shopware\Models\Country\State;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Dispatch\Dispatch;
use Shopware\Models\Dispatch\Repository as DispatchRepository;
use Shopware\Models\Document\Document as DocumentType;
use Shopware\Models\Mail\Mail;
use Shopware\Models\Order\Billing;
use Shopware\Models\Order\Detail as OrderDetail;
use Shopware\Models\Order\DetailStatus;
use Shopware\Models\Order\Document\Document;
use Shopware\Models\Order\Document\Repository as OrderDocumentRepository;
use Shopware\Models\Order\Order;
use Shopware\Models\Order\Repository as OrderRepository;
use Shopware\Models\Order\Shipping;
use Shopware\Models\Order\Status;
use Shopware\Models\Payment\Payment;
use Shopware\Models\Payment\Repository as PaymentRepository;
use Shopware\Models\Shop\Locale as ShopLocale;
use Shopware\Models\Shop\Repository as ShopRepository;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Tax\Tax;

class Shopware_Controllers_Backend_Order extends Shopware_Controllers_Backend_ExtJs implements CSRFWhitelistAware
{
    /**
     * @deprecated - Will be private and an instance property in Shopware 5.8
     *
     * @var OrderRepository
     */
    public static $repository;

    /**
     * @deprecated - Will be private and an instance property in Shopware 5.8
     *
     * @var ShopRepository
     */
    public static $shopRepository;

    /**
     * @deprecated - Will be private and an instance property in Shopware 5.8
     *
     * @var CountryRepository
     */
    public static $countryRepository;

    /**
     * @deprecated - Will be private and an instance property in Shopware 5.8
     *
     * @var PaymentRepository
     */
    public static $paymentRepository;

    /**
     * @deprecated - Will be private and an instance property in Shopware 5.8
     *
     * @var DispatchRepository
     */
    public static $dispatchRepository;

    /**
     * @deprecated - Will be private and an instance property in Shopware 5.8
     * Contains the shopware model manager
     *
     * @var ModelManager
     */
    public static $manager;

    /**
     * @deprecated - Will be removed in Shopware 5.8 without a replacement
     * Contains the dynamic receipt repository
     *
     * @var OrderDocumentRepository
     */
    public static $documentRepository;

    /**
     * @deprecated - Will be protected in Shopware 5.8 to match the parent method signature
     */
    public function initAcl()
    {
        $this->addAclPermission('loadStores', 'read', 'Insufficient Permissions');
        $this->addAclPermission('save', 'update', 'Insufficient Permissions');
        $this->addAclPermission('deletePosition', 'update', 'Insufficient Permissions');
        $this->addAclPermission('savePosition', 'update', 'Insufficient Permissions');
        $this->addAclPermission('createDocument', 'update', 'Insufficient Permissions');
        $this->addAclPermission('batchProcess', 'update', 'Insufficient Permissions');
        $this->addAclPermission('delete', 'delete', 'Insufficient Permissions');
        $this->addAclPermission('deleteDocument', 'deleteDocument', 'Insufficient Permissions');
    }

    /**
     * Get a list of available payment status
     *
     * @return void
     */
    public function getPaymentStatusAction()
    {
        $orderStatus = $this->getRepository()->getPaymentStatusQuery()->getArrayResult();

        $this->View()->assign([
            'success' => true,
            'data' => $orderStatus,
        ]);
    }

    /**
     * Enable json renderer for index / load action
     * Check acl rules
     *
     * @return void
     */
    public function preDispatch()
    {
        $actions = ['index', 'load', 'skeleton', 'extends', 'mergeDocuments'];
        if (!\in_array($this->Request()->getActionName(), $actions)) {
            $this->Front()->Plugins()->Json()->setRenderer();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'openPdf',
            'createDocument',
            'mergeDocuments',
        ];
    }

    /**
     * @return void
     */
    public function loadListAction()
    {
        $filters = [['property' => 'status.id', 'expression' => '!=', 'value' => '-1']];
        $orderState = $this->getRepository()->getOrderStatusQuery($filters)->getArrayResult();
        $paymentState = $this->getRepository()->getPaymentStatusQuery()->getArrayResult();
        $positionStatus = $this->getRepository()->getDetailStatusQuery()->getArrayResult();

        $stateTranslator = $this->get('shopware.components.state_translator');

        $orderState = array_map(function ($orderStateItem) use ($stateTranslator) {
            return $stateTranslator->translateState(StateTranslatorService::STATE_ORDER, $orderStateItem);
        }, $orderState);

        $paymentState = array_map(function ($paymentStateItem) use ($stateTranslator) {
            return $stateTranslator->translateState(StateTranslatorService::STATE_PAYMENT, $paymentStateItem);
        }, $paymentState);

        $this->View()->assign([
            'success' => true,
            'data' => [
                'orderStatus' => $orderState,
                'paymentStatus' => $paymentState,
                'positionStatus' => $positionStatus,
            ],
        ]);
    }

    /**
     * @deprecated - Will be private in Shopware 5.8
     * Get documents of a specific type for the given orders
     *
     * @param int[]  $orderIds
     * @param string $docType
     *
     * @return Query
     */
    public function getOrderDocumentsQuery($orderIds, $docType)
    {
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select([
            'orders',
            'documents',
        ]);

        $builder->from(Order::class, 'orders');
        $builder->leftJoin('orders.documents', 'documents')
            ->where('documents.typeId = :type')
            ->andWhere('orders.id IN (:orderIds)')
            ->setParameter('orderIds', $orderIds, Connection::PARAM_INT_ARRAY)
            ->setParameter(':type', $docType);

        return $builder->getQuery();
    }

    /**
     * @deprecated - Will be private in Shopware 5.8
     * This class has its own OrderStatusQuery as we need to get rid of states with status.id = -1
     *
     * @param array<array{property: string, value: mixed, expression?: string}>|null $filter
     * @param array<array{property: string, direction: string}>|null                 $order
     * @param int|null                                                               $offset
     * @param int|null                                                               $limit
     *
     * @return Query
     */
    public function getOrderStatusQuery($filter = null, $order = null, $offset = null, $limit = null)
    {
        /** @var QueryBuilder $builder */
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select(['status'])
            ->from(Status::class, 'status')
            ->andWhere("status.group = 'state'");

        if ($filter !== null) {
            $builder->addFilter($filter);
        }
        if ($order !== null) {
            $builder->addOrderBy($order);
        } else {
            $builder->orderBy('status.position', 'ASC');
        }

        if ($offset !== null) {
            $builder->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        return $builder->getQuery();
    }

    /**
     * batch function which summarizes some queries in order to speed up the order-detail startup
     *
     * @return void
     */
    public function loadStoresAction()
    {
        $id = $this->Request()->getParam('orderId');
        if ($id === null) {
            $this->View()->assign(['success' => false, 'message' => 'No orderId passed']);

            return;
        }

        $stateTranslator = $this->get(StateTranslatorService::class);

        $orderState = $this->getOrderStatusQuery()->getArrayResult();
        $paymentState = $this->getRepository()->getPaymentStatusQuery()->getArrayResult();

        $orderState = array_map(function ($orderStateItem) use ($stateTranslator) {
            return $stateTranslator->translateState(StateTranslatorService::STATE_ORDER, $orderStateItem);
        }, $orderState);

        $paymentState = array_map(function ($paymentStateItem) use ($stateTranslator) {
            return $stateTranslator->translateState(StateTranslatorService::STATE_PAYMENT, $paymentStateItem);
        }, $paymentState);

        $countriesSort = [
            [
                'property' => 'countries.active',
                'direction' => 'DESC',
            ],
            [
                'property' => 'countries.name',
                'direction' => 'ASC',
            ],
        ];

        $shops = $this->getShopRepository()->getBaseListQuery()->getArrayResult();
        $countries = $this->getCountryRepository()->getCountriesQuery(null, $countriesSort)->getArrayResult();
        $payments = $this->getPaymentRepository()->getAllPaymentsQuery()->getArrayResult();
        $dispatches = $this->getDispatchRepository()->getDispatchesQuery()->getArrayResult();
        $documentTypes = $this->getRepository()->getDocumentTypesQuery()->getArrayResult();

        // Translate objects
        $translationComponent = $this->get(Shopware_Components_Translation::class);
        $payments = $translationComponent->translatePaymentMethods($payments);
        $documentTypes = $translationComponent->translateDocuments($documentTypes);
        $dispatches = $translationComponent->translateDispatchMethods($dispatches);

        $this->View()->assign([
            'success' => true,
            'data' => [
                'orderStatus' => $orderState,
                'paymentStatus' => $paymentState,
                'shops' => $shops,
                'countries' => $countries,
                'payments' => $payments,
                'dispatches' => $dispatches,
                'documentTypes' => $documentTypes,
            ],
        ]);
    }

    /**
     * Event listener method which fires when the order store is loaded. Returns an array of order data
     * which displayed in an Ext.grid.Panel. The order data contains all associations of an order (positions, shop,
     * customer, ...). The limit, filter and order parameter are used in the id query. The result of the id query are
     * used to filter the detailed query which created over the getListQuery function.
     *
     * @return void
     */
    public function getListAction()
    {
        // Read store parameter to filter and paginate the data.
        $limit = (int) $this->Request()->getParam('limit', 20);
        $offset = (int) $this->Request()->getParam('start', 0);
        $sort = $this->Request()->getParam('sort', []);
        $filter = $this->Request()->getParam('filter', []);
        $orderId = $this->Request()->getParam('orderID');

        if ($orderId !== null) {
            $orderIdFilter = ['property' => 'orders.id', 'value' => $orderId];
            if (!\is_array($filter)) {
                $filter = [];
            }
            $filter[] = $orderIdFilter;
        }

        $list = $this->getList($filter, $sort, $offset, $limit);

        $list['data'] = $this->get(Shopware_Components_Translation::class)->translateOrders($list['data']);

        $this->View()->assign($list);
    }

    /**
     * Returns an array of all defined taxes. Used for the position grid combo box on the detail page of the backend
     * order module.
     *
     * @return void
     */
    public function getTaxAction()
    {
        $tax = $this->getManager()->createQueryBuilder()->select(['tax'])
            ->from(Tax::class, 'tax')
            ->getQuery()
            ->getArrayResult();

        $this->View()->assign(['success' => true, 'data' => $tax]);
    }

    /**
     * The getVouchers function is used by the extJs voucher store which used for a
     * combo box on the order detail page.
     *
     * @return void
     */
    public function getVouchersAction()
    {
        $vouchers = $this->getRepository()->getVoucherQuery()->getArrayResult();
        $this->View()->assign(['success' => true, 'data' => $vouchers]);
    }

    /**
     * Returns all supported document types. The data is used for the configuration panel.
     *
     * @return void
     */
    public function getDocumentTypesAction()
    {
        $types = $this->getRepository()->getDocumentTypesQuery()->getArrayResult();
        $this->View()->assign(['success' => true, 'data' => $types]);
    }

    /**
     * Event listener function of the history store in the order backend module.
     * Returns the status history of the passed order.
     *
     * @return void
     */
    public function getStatusHistoryAction()
    {
        $orderId = $this->Request()->getParam('orderID');
        $limit = $this->Request()->getParam('limit', 20);
        $offset = $this->Request()->getParam('start', 0);
        $sort = $this->Request()->getParam('sort', [['property' => 'history.changeDate', 'direction' => 'DESC']]);

        $namespace = $this->get('snippets')->getNamespace('backend/order');

        //the backend order module have no function to create a new order so an order id must be passed.
        if (empty($orderId)) {
            $this->View()->assign([
                'success' => false,
                'data' => $this->Request()->getParams(),
                'message' => $namespace->get('no_order_id_passed', 'No valid order id passed.'),
            ]);

            return;
        }

        $history = $this->getRepository()
            ->getOrderStatusHistoryListQuery($orderId, $sort, $offset, $limit)
            ->getArrayResult();

        $this->View()->assign([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * CRUD function save and update of the order model.
     *
     * Event listener method of the backend/order/model/order.js model which
     * is used for the backend order module detail page to edit a single order.
     *
     * @return void
     */
    public function saveAction()
    {
        $id = (int) $this->Request()->getParam('id');

        $namespace = $this->get('snippets')->getNamespace('backend/order/main');

        // The backend order module have no function to create a new order so an order id must be passed.
        if (empty($id)) {
            $this->View()->assign([
                'success' => false,
                'data' => $this->Request()->getParams(),
                'message' => $namespace->get('no_order_id_passed', 'No valid order id passed.'),
            ]);

            return;
        }

        $order = $this->getRepository()->find($id);

        // The backend order module have no function to create a new order so an order id must be passed.
        if (!($order instanceof Order)) {
            $this->View()->assign([
                'success' => false,
                'data' => $this->Request()->getParams(),
                'message' => $namespace->get('no_order_id_passed', 'No valid order id passed.'),
            ]);

            return;
        }

        $billing = $order->getBilling();
        $shipping = $order->getShipping();

        // Check if the shipping and billing model already exist. If not create a new instance.
        if (!$shipping instanceof Shipping) {
            $shipping = new Shipping();
        }

        if (!$billing instanceof Billing) {
            $billing = new Billing();
        }
        // Get all passed order data
        $data = $this->Request()->getParams();

        if ($order->getChanged() !== null) {
            try {
                $changed = new DateTime($data['changed']);
            } catch (Exception $e) {
                // If we have an invalid date caused by imports
                $changed = $order->getChanged();
            }

            if ($changed->getTimestamp() < 0 && $order->getChanged()->getTimestamp() < 0) {
                $changed = $order->getChanged();
            }

            // We have timestamp conversion issues on Windows Users
            $diff = abs($order->getChanged()->getTimestamp() - $changed->getTimestamp());

            // Check whether the order has been modified in the meantime
            if ($diff > 1) {
                $this->View()->assign([
                    'success' => false,
                    'data' => $this->getOrder($order->getId()),
                    'overwriteAble' => true,
                    'message' => $namespace->get('order_has_been_changed', 'The order has been changed in the meantime. To prevent overwriting these changes, saving the order was aborted. Please close the order and re-open it.'),
                ]);

                return;
            }
        }

        // Prepares the associated data of an order.
        $data = $this->getAssociatedData($data);

        // Before we can create the status mail, we need to save the order data.
        // Otherwise, the status mail would be created with the old order status and amount.
        $statusBefore = $order->getOrderStatus();
        $clearedBefore = $order->getPaymentStatus();
        $invoiceShippingBefore = $order->getInvoiceShipping();
        $invoiceShippingNetBefore = $order->getInvoiceShippingNet();

        if (!empty($data['clearedDate'])) {
            try {
                $data['clearedDate'] = new DateTime($data['clearedDate']);
            } catch (Exception $e) {
                $data['clearedDate'] = null;
            }
        }

        if (isset($data['orderTime'])) {
            unset($data['orderTime']);
        }

        $order->fromArray($data);

        // Check if the invoice shipping has been changed
        $invoiceShippingChanged = $invoiceShippingBefore !== $order->getInvoiceShipping();
        $invoiceShippingNetChanged = $invoiceShippingNetBefore !== $order->getInvoiceShippingNet();
        if ($invoiceShippingChanged || $invoiceShippingNetChanged) {
            // Recalculate the new invoice amount
            $calculationService = $this->container->get(CalculationServiceInterface::class);
            $calculationService->recalculateOrderTotals($order);
        }

        $this->getManager()->flush();
        $this->getManager()->clear();
        $order = $this->getRepository()->find($id);
        if (!($order instanceof Order)) {
            $this->View()->assign([
                'success' => false,
                'data' => $this->Request()->getParams(),
                'message' => $namespace->get('no_order_id_passed', 'No valid order id passed.'),
            ]);

            return;
        }

        // If the status has been changed and status mail is created.
        $warning = null;
        $mail = null;
        if ($order->getOrderStatus()->getId() !== $statusBefore->getId() || $order->getPaymentStatus()->getId() !== $clearedBefore->getId()) {
            if ($order->getOrderStatus()->getId() !== $statusBefore->getId()) {
                $status = $order->getOrderStatus();
            } else {
                $status = $order->getPaymentStatus();
            }
            try {
                $mail = $this->getMailForOrder($order->getId(), $status->getId());
            } catch (Exception $e) {
                $warning = sprintf(
                    $namespace->get('warning/mail_creation_failed'),
                    $status->getName(),
                    $e->getMessage()
                );
            }
        }

        $data = $this->getOrder($order->getId());
        if (\is_array($mail)) {
            $data['mail'] = $mail['data'];
        } else {
            $data['mail'] = null;
        }

        $result = [
            'success' => true,
            'data' => $data,
        ];
        if (isset($warning)) {
            $result['warning'] = $warning;
        }

        $this->View()->assign($result);
    }

    /**
     * Deletes a single order from the database.
     * Expects a single order id which placed in the parameter id
     *
     * @return void
     */
    public function deleteAction()
    {
        $namespace = $this->get('snippets')->getNamespace('backend/order');

        // Get posted customers
        $orderId = $this->Request()->getParam('id');

        if (empty($orderId) || !is_numeric($orderId)) {
            $this->View()->assign([
                'success' => false,
                'data' => $this->Request()->getParams(),
                'message' => $namespace->get('no_order_id_passed', 'No valid order id passed.'),
            ]);

            return;
        }

        $entity = $this->getRepository()->find($orderId);
        if ($entity === null) {
            $this->View()->assign(['success' => true]);

            return;
        }

        $this->getManager()->remove($entity);

        // Performs all the collected actions.
        $this->getManager()->flush();

        $this->View()->assign(['success' => true]);
    }

    /**
     * CRUD function save and update of the position store of the backend order module.
     * The function handles the update and insert routine of a single order position.
     * After the position has been added to the order, the order invoice amount will be recalculated.
     * The refreshed order will be assigned to the view to refresh the panels and grids.
     *
     * @return void
     */
    public function savePositionAction()
    {
        $id = (int) $this->Request()->getParam('id');

        $orderId = $this->Request()->getParam('orderId');

        $namespace = $this->get('snippets')->getNamespace('backend/order/controller/main');

        // Check if an order id is passed. If no order id passed, return success false
        if (empty($orderId)) {
            $this->View()->assign([
                'success' => false,
                'data' => $this->Request()->getParams(),
                'message' => $namespace->get('no_order_id_passed', 'No valid order id passed.'),
            ]);

            return;
        }

        // Find the order model. If no model founded, return success false
        $order = $this->getRepository()->find($orderId);
        if (!$order instanceof Order) {
            $this->View()->assign([
                'success' => false,
                'data' => $this->Request()->getParams(),
                'message' => $namespace->get('no_order_id_passed', 'No valid order id passed.'),
            ]);

            return;
        }

        $lastOrderChange = new DateTime($this->Request()->getParam('changed', ''));
        if ($this->checkIfOrderHasBeenModifiedSince($lastOrderChange, $order, $namespace)) {
            return;
        }

        // Check if the passed position data is a new position or an existing position.
        $position = $this->getManager()->getRepository(OrderDetail::class)->find($id);
        if (!$position instanceof OrderDetail) {
            $position = new OrderDetail();
            $attribute = new OrderDetailAttribute();
            $position->setAttribute($attribute);
            $this->getManager()->persist($position);
        }

        $data = $this->Request()->getParams();
        $data['number'] = $order->getNumber();

        $data = $this->getPositionAssociatedData($data, $order);

        $position->fromArray($data);
        $position->setOrder($order);

        $this->getManager()->flush();

        // If the passed data is a new position, the flush function will add the new id to the position model
        $data['id'] = $position->getId();

        // The position model will refresh the product stock, so the product stock
        // will be assigned to the view to refresh the grid or form panel.
        $variant = $this->getManager()->getRepository(ProductVariant::class)
            ->findOneBy(['number' => $position->getArticleNumber()]);
        if ($variant instanceof ProductVariant) {
            $data['inStock'] = $variant->getInStock();
        }

        $order = $this->getRepository()->find($order->getId());
        if (!$order instanceof Order) {
            $this->View()->assign([
                'success' => false,
                'data' => $this->Request()->getParams(),
                'message' => $namespace->get('no_order_id_passed', 'No valid order id passed.'),
            ]);

            return;
        }

        $this->getManager()->persist($order);
        $this->getManager()->flush();

        $invoiceAmount = $order->getInvoiceAmount();
        $changed = $order->getChanged();

        if ($position->getOrder() instanceof Order) {
            $invoiceAmount = $position->getOrder()->getInvoiceAmount();
            $changed = $position->getOrder()->getChanged();
        }

        $this->View()->assign([
            'success' => true,
            'data' => $data,
            'invoiceAmount' => $invoiceAmount,
            'changed' => $changed,
        ]);
    }

    /**
     * CRUD function delete of the position and list store of the backend order module.
     * The function can delete one or many order positions. After the positions has been deleted
     * the order invoice amount will be recalculated. The refreshed order will be assigned to the
     * view to refresh the panels and grids.
     *
     * @return void
     */
    public function deletePositionAction()
    {
        $namespace = $this->get('snippets')->getNamespace('backend/order/controller/main');

        $positions = $this->Request()->getParam('positions', [['id' => $this->Request()->getParam('id')]]);

        // Check if any positions is passed.
        if (empty($positions)) {
            $this->View()->assign([
                'success' => false,
                'data' => $this->Request()->getParams(),
                'message' => $namespace->get('no_order_passed', 'No orders passed'),
            ]);

            return;
        }

        // If no order id passed it isn't possible to update the order amount, so we will cancel the position deletion here.
        $orderId = $this->Request()->getParam('orderID');

        if (empty($orderId)) {
            $this->View()->assign([
                'success' => false,
                'data' => $this->Request()->getParams(),
                'message' => $namespace->get('no_order_id_passed', 'No valid order id passed.'),
            ]);

            return;
        }

        $order = $this->getRepository()->find($orderId);
        if (empty($order)) {
            $this->View()->assign([
                'success' => false,
                'data' => $this->Request()->getParams(),
                'message' => $namespace->get('no_order_id_passed', 'No valid order id passed.'),
            ]);

            return;
        }

        $lastOrderChange = new DateTime($this->Request()->getParam('changed', ''));
        if ($this->checkIfOrderHasBeenModifiedSince($lastOrderChange, $order, $namespace)) {
            return;
        }

        foreach ($positions as $position) {
            if (empty($position['id'])) {
                continue;
            }
            $model = $this->getManager()->find(OrderDetail::class, $position['id']);

            // Check if the model was founded.
            if ($model instanceof OrderDetail) {
                $this->getManager()->remove($model);
            }
        }
        // After each model has been removed to execute the doctrine flush.
        $this->getManager()->flush();

        $calculationService = $this->container->get(CalculationServiceInterface::class);
        $calculationService->recalculateOrderTotals($order);

        $this->getManager()->flush();

        $data = $this->getOrder($order->getId());
        $this->View()->assign([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * The batchProcessAction function handles the request of the batch window in order backend module.
     * It is responsible to create the order document for the passed parameters and updates the order
     * or|and payment status that passed for each order. If the order or payment status has been changed
     * the function will create for each order and status mail which will be assigned to the passed order
     * and will be displayed in the email panel on the right side of the batch window.
     * If the parameter "autoSend" is set to true (configurable over the checkbox in the form panel) each
     * created status mail will be sent directly.
     *
     * @return void
     */
    public function batchProcessAction()
    {
        $autoSend = $this->Request()->getParam('autoSend') === 'true';
        $orders = $this->Request()->getParam('orders', [0 => $this->Request()->getParams()]);
        $documentType = (int) $this->Request()->getParam('docType');
        $documentMode = (int) $this->Request()->getParam('mode');
        $addAttachments = $this->request->getParam('addAttachments') === 'true';

        $namespace = $this->get('snippets')->getNamespace('backend/order');

        if (empty($orders)) {
            $this->View()->assign([
                'success' => false,
                'data' => $this->Request()->getParams(),
                'message' => $namespace->get('no_order_id_passed', 'No valid order id passed.'),
            ]);

            return;
        }

        $modelManager = $this->get(ModelManager::class);
        $stateTranslator = $this->get(StateTranslatorService::class);

        $previousLocale = $this->getCurrentLocale();

        foreach ($orders as &$data) {
            $data['success'] = false;
            $data['errorMessage'] = $namespace->get('no_order_id_passed', 'No valid order id passed.');

            if (empty($data) || empty($data['id'])) {
                continue;
            }

            $order = $modelManager->find(Order::class, $data['id']);
            if (!$order) {
                continue;
            }

            /*
                We have to flush the status changes directly, because the "createStatusMail" function in the
                sOrder.php core class, use the order data from the database. So we have to save the new status before we
                create the status mail
            */
            $statusBefore = $order->getOrderStatus();
            $clearedBefore = $order->getPaymentStatus();

            // Refresh the status models to return the new status data which will be displayed in the batch list
            if (!empty($data['status']) || $data['status'] === 0) {
                $order->setOrderStatus($modelManager->find(Status::class, $data['status']));
            }
            if (!empty($data['cleared'])) {
                $order->setPaymentStatus($modelManager->find(Status::class, $data['cleared']));
            }

            try {
                $modelManager->flush($order);
            } catch (Exception $e) {
                $data['success'] = false;
                $data['errorMessage'] = sprintf(
                    $namespace->get('save_order_failed', 'Error when saving the order. Error: %s'),
                    $e->getMessage()
                );
                continue;
            }

            /*
                The setOrder function of the Shopware_Components_Document change the currency of the shop.
                This would create a new Shop if we execute a flush(); Only create order documents when requested.
            */
            if ($documentType !== 0) {
                $this->createOrderDocuments($documentType, $documentMode, $order);
            }

            if ($previousLocale) {
                // This is necessary, since the "checkOrderStatus" method might change the locale due to translation issues
                // when sending an order status mail. Therefore, we reset it here to the chosen backend language.
                $this->get('snippets')->setLocale($previousLocale);
                $this->get('snippets')->resetShop();
            }

            $data['paymentStatus'] = $stateTranslator->translateState(StateTranslatorService::STATE_PAYMENT, $modelManager->toArray($order->getPaymentStatus()));
            $data['orderStatus'] = $stateTranslator->translateState(StateTranslatorService::STATE_ORDER, $modelManager->toArray($order->getOrderStatus()));

            try {
                // The method '$this->checkOrderStatus()' (even its name would not imply that) sends mails and can fail
                // with an exception. Catch this exception, so the batch process does not abort.
                $data['mail'] = $this->checkOrderStatus($order, $statusBefore, $clearedBefore, $autoSend, $documentType, $addAttachments);
            } catch (Exception $e) {
                $data['mail'] = null;
                $data['success'] = false;
                $data['errorMessage'] = sprintf(
                    $namespace->get('send_mail_failed', 'Error when sending mail. Error: %s'),
                    $e->getMessage()
                );
                continue;
            }

            $data['success'] = true;
            $data['errorMessage'] = null;
        }

        $this->View()->assign([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * This function is called by the batch controller after all documents were created
     * It will read the created documents' hashes from database and merge them
     *
     * @return void
     */
    public function mergeDocumentsAction()
    {
        $data = $this->Request()->getParam('data');

        // Disable Smarty rendering
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        if ($data === null) {
            $this->View()->assign([
                'success' => false,
                'message' => 'No valid data passed.',
            ]);

            return;
        }

        $data = json_decode($data);

        if ($data->orders === null || \count($data->orders) === 0) {
            $this->View()->assign([
                'success' => false,
                'message' => 'No valid order id passed.',
            ]);

            return;
        }

        $files = [];
        $models = $this->getOrderDocumentsQuery($data->orders, $data->docType)->getResult();
        foreach ($models as $model) {
            foreach ($model->getDocuments() as $document) {
                $file = $this->downloadFileFromFilesystem(sprintf('documents/%s.pdf', $document->getHash()));
                if ($file !== null) {
                    $files[] = $file;
                }
            }
        }

        $this->mergeDocuments($files);

        // Remove temporary files
        foreach ($files as $file) {
            unlink($file);
        }
    }

    /**
     * The sendMailAction fired from the batch window in the order backend module when the user want to send the order
     * status mail manually.
     *
     * @return void
     */
    public function sendMailAction()
    {
        $data = $this->Request()->getParams();
        $orderId = (int) $this->request->getParam('orderId');
        $attachments = $this->request->getParam('attachment');

        $namespace = $this->get('snippets')->getNamespace('backend/order');

        if (empty($data)) {
            $this->View()->assign([
                'success' => false,
                'data' => $data,
                'message' => $namespace->get('no_data_passed', 'No mail data passed'),
            ]);

            return;
        }

        $mailTemplateName = $this->Request()->getParam('templateName') ?: 'sORDERDOCUMENTS';

        $mail = $this->container->get('modules')->Order()->createStatusMail($orderId, 0, $mailTemplateName);
        if (!$mail instanceof Enlight_Components_Mail) {
            $this->View()->assign([
                'success' => false,
                'message' => 'Could not create mail object.',
            ]);

            return;
        }

        $mail->clearRecipients();
        $mail->clearSubject();
        $mail->clearFrom();
        $mail->clearBody();

        $mailData = [
            'attachments' => $attachments,
            'subject' => $this->Request()->getParam('subject', ''),
            'fromMail' => $this->Request()->getParam('fromMail'),
            'fromName' => $this->Request()->getParam('fromName'),
            'to' => [$this->Request()->getParam('to')],
            'isHtml' => $this->Request()->getParam('isHtml'),
            'bodyHtml' => $this->Request()->getParam('contentHtml', ''),
            'bodyText' => $this->Request()->getParam('content', ''),
        ];

        $mailData = $this->get('events')->filter(
            'Shopware_Controllers_Order_SendMail_Prepare',
            $mailData,
            [
                'subject' => $this,
                'orderId' => $orderId,
                'mail' => $mail,
            ]
        );

        $mail->setSubject($mailData['subject']);

        $mail->setFrom($mailData['fromMail'], $mailData['fromName']);
        $mail->addTo($mailData['to']);

        if ($mailData['isHtml']) {
            $mail->setBodyHtml($mailData['bodyHtml']);
        } else {
            $mail->setBodyText($mailData['bodyText']);
        }
        $mail = $this->addAttachments($mail, $orderId, $mailData['attachments']);

        $mail->setAssociation(LogEntryBuilder::ORDER_ID_ASSOCIATION, $orderId);

        $this->get('modules')->Order()->sendStatusMail($mail);

        $this->View()->assign([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Deletes a document by the requested document id.
     *
     * @return void
     */
    public function deleteDocumentAction()
    {
        $filesystem = $this->container->get('shopware.filesystem.private');
        $documentId = $this->request->getParam('documentId');
        $connection = $this->container->get(Connection::class);
        $queryBuilder = $connection->createQueryBuilder();

        try {
            $documentHash = $queryBuilder->select('hash')
                ->from('s_order_documents')
                ->where('id = :documentId')
                ->setParameter('documentId', $documentId)
                ->execute()
                ->fetchOne();

            $queryBuilder = $connection->createQueryBuilder();
            $queryBuilder->delete('s_order_documents')
                ->where('id = :documentId')
                ->setParameter('documentId', $documentId)
                ->execute();

            $file = sprintf('documents/%s.pdf', $documentHash);
            if ($filesystem->has($file)) {
                $filesystem->delete($file);
            }
        } catch (Exception $exception) {
            $this->View()->assign([
                'success' => false,
                'errorMessage' => $exception->getMessage(),
            ]);
        }

        $this->View()->assign('success', true);
    }

    /**
     * Creates a mail by the requested orderId and assign it to the view.
     *
     * @return void
     */
    public function createMailAction()
    {
        $orderId = (int) $this->Request()->getParam('orderId');
        $mailTemplateName = $this->Request()->getParam('mailTemplateName', 'sORDERDOCUMENTS');

        $mail = $this->get('modules')->Order()->createStatusMail($orderId, 0, $mailTemplateName);
        if (!$mail instanceof Enlight_Components_Mail) {
            $this->View()->assign([
                'success' => false,
                'message' => 'Could not create mail object.',
            ]);

            return;
        }

        $this->view->assign([
            'mail' => [
                'error' => false,
                'content' => $mail->getPlainBodyText(),
                'contentHtml' => $mail->getPlainBody(),
                'subject' => $mail->getPlainSubject(),
                'to' => implode(', ', $mail->getTo()),
                'fromMail' => $mail->getFrom(),
                'fromName' => $mail->getFromName(),
                'sent' => false,
                'isHtml' => !empty($mail->getPlainBody()),
                'orderId' => $orderId,
            ],
        ]);
    }

    /**
     * Retrieves all available mail templates
     *
     * @return void
     */
    public function getMailTemplatesAction()
    {
        $limit = (int) $this->Request()->getParam('limit', 100);
        $offset = (int) $this->Request()->getParam('start', 0);
        $order = $this->Request()->getParam('sort', []);
        $filter = $this->Request()->getParam('filter', []);

        $mailTemplatesQuery = $this->getModelManager()->getRepository(Mail::class)->getMailsListQueryBuilder(
            $filter,
            $order,
            $offset,
            $limit
        );

        $mailTemplates = $mailTemplatesQuery->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);

        // Add a display name to the mail templates
        $documentTypes = $this->getModelManager()->getRepository(DocumentType::class)->findAll();
        $documentTypeNames = [];

        foreach ($documentTypes as $documentType) {
            $documentTypeNames['document_' . $documentType->getKey()] = $documentType->getName();
        }
        foreach ($mailTemplates as &$mailTemplate) {
            if ($mailTemplate['name'] === 'sORDERDOCUMENTS') {
                $mailTemplate['displayName'] = $this->get('snippets')->getNamespace(
                    'backend/order/main'
                )->get(
                    'default_mail_template',
                    'Default template'
                );
            } elseif (isset($documentTypeNames[$mailTemplate['name']])) {
                $mailTemplate['displayName'] = $documentTypeNames[$mailTemplate['name']];
            } else {
                $mailTemplate['displayName'] = $mailTemplate['name'];
            }
        }

        $this->View()->assign([
            'success' => true,
            'data' => $mailTemplates,
        ]);
    }

    /**
     * CRUD function of the document store. The function creates the order document with the passed
     * request parameters.
     *
     * @return void
     */
    public function createDocumentAction()
    {
        $orderId = (int) $this->Request()->getParam('orderId');
        $documentType = (int) $this->Request()->getParam('documentType');

        // Needs to be called this early since $this->createDocument boots the
        // shop, the order was made in, and thereby destroys the backend session
        $translationComponent = $this->get(Shopware_Components_Translation::class);

        if ($orderId !== 0 && $documentType !== 0) {
            $this->createDocument($orderId, $documentType);
        }

        $query = $this->getRepository()->getOrdersQuery([['property' => 'orders.id', 'value' => $orderId]], null, 0, 1);
        $query->setHydrationMode(AbstractQuery::HYDRATE_ARRAY);
        $order = $this->getModelManager()->createPaginator($query)->getIterator()->getArrayCopy();

        $order = $translationComponent->translateOrders($order);

        $this->View()->assign([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Fires when the user want to open a generated order document from the backend order module.
     * Returns the created pdf file.
     *
     * @return void
     */
    public function openPdfAction()
    {
        $filesystem = $this->container->get('shopware.filesystem.private');
        $file = sprintf('documents/%s.pdf', basename($this->Request()->getParam('id')));

        if ($filesystem->has($file) === false) {
            $this->View()->assign([
                'success' => false,
                'data' => $this->Request()->getParams(),
                'message' => 'File does not exist.',
            ]);

            return;
        }

        // Disable Smarty rendering
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->Front()->Plugins()->Json()->setRenderer(false);

        $orderModel = $this->getManager()->getRepository(Document::class)->findBy(['hash' => $this->Request()->getParam('id')]);
        $orderModel = $this->getManager()->toArray($orderModel);

        $fileName = $this->container->get('events')->filter(
            'Shopware_Controllers_Order_OpenPdf_FilterName',
            $orderModel[0]['documentId'],
            ['data' => $orderModel[0]]
        );

        $response = $this->Response();
        $response->headers->set('cache-control', 'public', true);
        $response->headers->set('content-description', 'File Transfer');
        $response->headers->set('content-disposition', 'attachment; filename=' . $fileName . '.pdf');
        $response->headers->set('content-type', 'application/pdf');
        $response->headers->set('content-transfer-encoding', 'binary');
        $response->headers->set('content-length', (string) $filesystem->getSize($file));

        $upstream = $filesystem->readStream($file);
        if (!\is_resource($upstream)) {
            $this->View()->assign([
                'success' => false,
                'data' => $this->Request()->getParams(),
                'message' => sprintf('Could not open file: %s', $file),
            ]);

            return;
        }
        $downstream = fopen('php://output', 'wb');
        if (!\is_resource($downstream)) {
            $this->View()->assign([
                'success' => false,
                'data' => $this->Request()->getParams(),
                'message' => 'Could not create file output',
            ]);

            return;
        }

        while (!feof($upstream)) {
            $read = fread($upstream, 4096);
            if (\is_string($read)) {
                fwrite($downstream, $read);
            }
        }
    }

    /**
     * Returns filterable partners
     *
     * @return void
     */
    public function getPartnersAction()
    {
        $limit = $this->Request()->getParam('limit', 20);
        $offset = $this->Request()->getParam('start', 0);

        $data = $this->get(Connection::class)->createQueryBuilder()
            ->select(['SQL_CALC_FOUND_ROWS MAX(IFNULL(partner.company, orders.partnerID)) as name', 'orders.partnerID as `value`'])
            ->from('s_order', 'orders')
            ->leftJoin('orders', 's_emarketing_partner', 'partner', 'orders.partnerID = partner.idcode')
            ->where('orders.partnerID IS NOT NULL')
            ->andWhere('orders.partnerID != ""')
            ->groupBy('orders.partnerID')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->execute()
            ->fetchAllAssociative();

        $total = (int) $this->get(Connection::class)->fetchOne('SELECT FOUND_ROWS()');

        $this->View()->assign(['success' => true, 'data' => $data, 'total' => $total]);
    }

    /**
     * @deprecated - Will be private in Shopware 5.8
     * Returns the shopware model manager
     *
     * @return ModelManager
     */
    protected function getManager()
    {
        if (self::$manager === null) {
            self::$manager = $this->get('models');
        }

        return self::$manager;
    }

    /**
     * @deprecated - Will be private in Shopware 5.8
     * Helper function to get access on the static declared repository
     *
     * @return OrderRepository
     */
    protected function getRepository()
    {
        if (self::$repository === null) {
            self::$repository = $this->getManager()->getRepository(Order::class);
        }

        return self::$repository;
    }

    /**
     * @deprecated - Will be private in Shopware 5.8
     * Helper function to get access on the static declared repository
     *
     * @return ShopRepository
     */
    protected function getShopRepository()
    {
        if (self::$shopRepository === null) {
            self::$shopRepository = $this->getManager()->getRepository(Shop::class);
        }

        return self::$shopRepository;
    }

    /**
     * @deprecated - Will be private in Shopware 5.8
     * Helper function to get access on the static declared repository
     *
     * @return CountryRepository
     */
    protected function getCountryRepository()
    {
        if (self::$countryRepository === null) {
            self::$countryRepository = $this->getManager()->getRepository(Country::class);
        }

        return self::$countryRepository;
    }

    /**
     * @deprecated - Will be private in Shopware 5.8
     * Helper function to get access on the static declared repository
     *
     * @return PaymentRepository
     */
    protected function getPaymentRepository()
    {
        if (self::$paymentRepository === null) {
            self::$paymentRepository = $this->getManager()->getRepository(Payment::class);
        }

        return self::$paymentRepository;
    }

    /**
     * @deprecated - Will be private in Shopware 5.8
     * Helper function to get access on the static declared repository
     *
     * @return DispatchRepository
     */
    protected function getDispatchRepository()
    {
        if (self::$dispatchRepository === null) {
            self::$dispatchRepository = $this->getManager()->getRepository(Dispatch::class);
        }

        return self::$dispatchRepository;
    }

    /**
     * @return OrderDocumentRepository
     *
     * @deprecated in 5.6, will be removed in 5.8 without a replacement
     *
     * Helper function to get access on the static declared repository
     */
    protected function getDocumentRepository()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.8. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        if (self::$documentRepository === null) {
            self::$documentRepository = $this->getManager()->getRepository(Document::class);
        }

        return self::$documentRepository;
    }

    /**
     * @deprecated - Will be private in Shopware 5.8
     *
     * @param array<array{property: string, value: mixed, expression?: string}> $filter
     * @param array<array{property: string, direction: string}>                 $sort
     * @param int|null                                                          $offset
     * @param int|null                                                          $limit
     *
     * @return array{success: bool, data: array<array<string, mixed>>, total: int}
     */
    protected function getList($filter, $sort, $offset, $limit)
    {
        $sort = $this->resolveSortParameter($sort);

        if ($this->container->getParameter('shopware.es.backend.enabled')) {
            $repository = $this->container->get(AttributeOrderRepository::class);
            $criteria = $this->createCriteria();
            $result = $repository->search($criteria);

            $total = $result->getCount();
            $ids = array_column($result->getData(), 'id');
        } else {
            $searchResult = $this->getRepository()->search($offset, $limit, $filter, $sort);
            $total = $searchResult['total'];
            $ids = array_column($searchResult['orders'], 'id');
        }

        $orders = $this->getRepository()->getList($ids);
        $documents = $this->getRepository()->getDocuments($ids);
        $details = $this->getRepository()->getDetails($ids);
        $payments = $this->getRepository()->getPayments($ids);

        $orders = $this->assignAssociation($orders, $documents, 'documents');
        $orders = $this->assignAssociation($orders, $details, 'details');
        $orders = $this->assignAssociation($orders, $payments, 'paymentInstances');

        $namespace = $this->get('snippets')->getNamespace('frontend/salutation');

        $stateTranslator = $this->get(StateTranslatorService::class);

        $numbers = [];
        foreach ($orders as $orderKey => $order) {
            $temp = array_column($order['details'], 'articleNumber');
            $numbers = array_merge($numbers, $temp);

            $orders[$orderKey]['orderStatus'] = $stateTranslator->translateState(StateTranslatorService::STATE_ORDER, $order['orderStatus']);
            $orders[$orderKey]['paymentStatus'] = $stateTranslator->translateState(StateTranslatorService::STATE_PAYMENT, $order['paymentStatus']);
        }

        $stocks = $this->getVariantsStock($numbers);

        $result = [];
        foreach ($ids as $id) {
            if (!\array_key_exists($id, $orders)) {
                continue;
            }
            $order = $orders[$id];

            $order['locale'] = $order['languageSubShop']['locale'];

            // Deprecated: use payment instance
            $order['debit'] = $order['customer']['debit'];
            $order['customerEmail'] = $order['customer']['email'];
            $order['billing']['salutationSnippet'] = $namespace->get($order['billing']['salutation']);
            $order['shipping']['salutationSnippet'] = $namespace->get($order['shipping']['salutation']);

            foreach ($order['details'] as &$orderDetail) {
                $number = $orderDetail['articleNumber'];
                $orderDetail['inStock'] = 0;
                if (!isset($stocks[$number])) {
                    continue;
                }
                $orderDetail['inStock'] = $stocks[$number];
            }
            $result[] = $order;
        }

        return [
            'success' => true,
            'data' => $result,
            'total' => $total,
        ];
    }

    /**
     * @deprecated - Will be private in Shopware 5.8
     * Prepare address data - loads countryModel from a given countryId
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    protected function prepareAddressData(array $data)
    {
        if (isset($data['countryId']) && !empty($data['countryId'])) {
            $countryModel = $this->getCountryRepository()->find($data['countryId']);
            if ($countryModel) {
                $data['country'] = $countryModel;
            }
            unset($data['countryId']);
        }

        if (isset($data['stateId']) && !empty($data['stateId'])) {
            $stateModel = $this->getManager()->find(State::class, $data['stateId']);
            if ($stateModel) {
                $data['state'] = $stateModel;
            }
            unset($data['stateId']);
        }

        return $data;
    }

    /**
     * @param array<array{property: string, direction: string}> $sorts
     *
     * @return array<array{property: string, direction: string}>
     */
    private function resolveSortParameter(array $sorts): array
    {
        if (empty($sorts)) {
            return [
                ['property' => 'orders.orderTime', 'direction' => 'DESC'],
            ];
        }

        $resolved = [];
        foreach ($sorts as $sort) {
            $direction = $sort['direction'] ?: 'ASC';
            switch (true) {
                // Custom sort field for customer email
                case $sort['property'] === 'customerEmail':
                    $resolved[] = ['property' => 'customer.email', 'direction' => $direction];
                    break;

                // Custom sort field for customer name
                case $sort['property'] === 'customerName':
                    $resolved[] = ['property' => 'billing.lastName', 'direction' => $direction];
                    $resolved[] = ['property' => 'billing.firstName', 'direction' => $direction];
                    $resolved[] = ['property' => 'billing.company', 'direction' => $direction];
                    break;

                // Contains no sql prefix? add orders as default prefix
                case !str_contains($sort['property'], '.'):
                    $resolved[] = ['property' => 'orders.' . $sort['property'], 'direction' => $direction];
                    break;

                // Already prefixed with an alias?
                default:
                    $resolved[] = $sort;
            }
        }

        return $resolved;
    }

    /**
     * @param array<array<string, mixed>> $orders
     * @param array<array<string, mixed>> $associations
     *
     * @return array<array<string, mixed>>
     */
    private function assignAssociation(array $orders, array $associations, string $arrayKey): array
    {
        foreach ($orders as &$order) {
            $order[$arrayKey] = [];
        }

        foreach ($associations as $association) {
            $id = $association['orderId'];
            $orders[$id][$arrayKey][] = $association;
        }

        return $orders;
    }

    /**
     * Helper function to select a single order.
     *
     * @return array<string, mixed>
     */
    private function getOrder(int $id): array
    {
        $query = $this->getRepository()->getOrdersQuery([['property' => 'orders.id', 'value' => $id]], []);
        $data = $query->getArrayResult();

        return $data[0];
    }

    /**
     * Simple helper function which actually merges a given array of document-paths
     *
     * @param array<string> $paths
     */
    private function mergeDocuments(array $paths): void
    {
        $pdf = new Fpdi();

        foreach ($paths as $path) {
            $numPages = $pdf->setSourceFile($path);
            for ($i = 1; $i <= $numPages; ++$i) {
                $template = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($template);
                if (!\is_array($size)) {
                    continue;
                }
                $pdf->AddPage('P', [$size['width'], $size['height']]);
                $pdf->useTemplate($template);
            }
        }

        $hash = Random::getAlphanumericString(32);

        $pdf->Output($hash . '.pdf', 'D');

        $this->Response()->headers->set('content-type', 'application/x-download');
    }

    /**
     * Internal helper function which checks if the batch process needs a document creation.
     */
    private function createOrderDocuments(int $documentTypeId, int $documentMode, Order $order): void
    {
        if ($documentTypeId === 0) {
            return;
        }

        $documents = $order->getDocuments();

        // Create only not existing documents
        if ($documentMode === 1) {
            $alreadyCreated = false;
            foreach ($documents as $document) {
                if ($document->getTypeId() === $documentTypeId) {
                    $alreadyCreated = true;
                    break;
                }
            }
            if ($alreadyCreated === false) {
                $this->createDocument($order->getId(), $documentTypeId);
            }
        } else {
            $this->createDocument($order->getId(), $documentTypeId);
        }
    }

    /**
     * Internal helper function to check if the order or payment status has been changed.
     * If one of the status changed, the function will create a status mail.
     * If the autoSend parameter is true, the created status mail will be sent directly,
     * if addAttachments and documentType are true/selected as well, the according documents will be attached.
     *
     * @return array<string, mixed>|null
     */
    private function checkOrderStatus(Order $order, Status $statusBefore, Status $clearedBefore, bool $autoSend, int $documentTypeId, bool $addAttachments): ?array
    {
        $orderStatusChanged = $order->getOrderStatus()->getId() !== $statusBefore->getId();
        $paymentStatusChanged = $order->getPaymentStatus()->getId() !== $clearedBefore->getId();
        $documentMailSendable = $documentTypeId !== 0 && $addAttachments;
        $mail = null;

        // Abort if autoSend isn't active and neither the order-, nor the payment-status changed
        if (!$autoSend && !$orderStatusChanged && !$paymentStatusChanged) {
            return null;
        }

        if ($orderStatusChanged) {
            // Generate mail with order status template
            $mail = $this->getMailForOrder($order->getId(), $order->getOrderStatus()->getId());
        } elseif ($paymentStatusChanged) {
            // Generate mail with payment status template
            $mail = $this->getMailForOrder($order->getId(), $order->getPaymentStatus()->getId());
        } elseif ($documentMailSendable) {
            // Generate mail with document template
            $mail = $this->getMailForOrder($order->getId(), null, $documentTypeId);
        }

        if (\is_array($mail)) {
            if ($addAttachments) {
                // Attach documents
                $document = $this->getDocument($documentTypeId, $order);
                $mailObject = $mail['mail'];
                $mail['mail'] = $this->addAttachments($mailObject, $order->getId(), [$document]);
            }
            if ($autoSend) {
                // Send mail
                $mailObject = $mail['mail'];
                $result = $this->get('modules')->Order()->sendStatusMail($mailObject);
                $mail['data']['sent'] = \is_object($result);
            }

            return $mail['data'];
        }

        return null;
    }

    /**
     * @return array{hash: string, type: array<array{id: int, name: string}>}
     */
    private function getDocument(int $documentTypeId, Order $order): array
    {
        foreach ($order->getDocuments()->toArray() as $document) {
            if ($document->getTypeId() === $documentTypeId) {
                return [
                    'hash' => $document->getHash(),
                    'type' => [
                        [
                            'id' => $document->getTypeId(),
                            'name' => $document->getType()->getName(),
                        ],
                    ],
                ];
            }
        }

        return $this->getDocumentFromDatabase($order->getId(), $documentTypeId);
    }

    /**
     * @return array{hash: string, type: array<array{id: int, name: string}>}
     */
    private function getDocumentFromDatabase(int $orderId, int $documentTypeId): array
    {
        $queryResult = $this->container->get(Connection::class)->createQueryBuilder()->select('doc.hash, template.id, template.name')
            ->from('s_order_documents', 'doc')
            ->join('doc', 's_core_documents', 'template', 'doc.type = template.id')
            ->where('doc.orderID = :orderId')
            ->andWhere('doc.type = :type')
            ->setParameter('orderId', $orderId, PDO::PARAM_INT)
            ->setParameter('type', $documentTypeId, PDO::PARAM_INT)
            ->execute()
            ->fetchAssociative();

        if (\is_array($queryResult)) {
            return [
                'hash' => $queryResult['hash'],
                'type' => [
                    [
                        'id' => (int) $queryResult['id'],
                        'name' => $queryResult['name'],
                    ],
                ],
            ];
        }

        return [
            'hash' => '',
            'type' => [
                [
                    'id' => -1,
                    'name' => 'invalid',
                ],
            ],
        ];
    }

    /**
     * Adds the requested attachments to the given $mail object
     *
     * @param array<array{hash: string, type: array<array{id: int, name: string}>}> $attachments
     */
    private function addAttachments(Enlight_Components_Mail $mail, int $orderId, array $attachments = []): Enlight_Components_Mail
    {
        $filesystem = $this->container->get('shopware.filesystem.private');

        foreach ($attachments as $attachment) {
            $filePath = sprintf('documents/%s.pdf', $attachment['hash']);
            $fileName = $this->getFileName($orderId, (int) $attachment['type'][0]['id']);

            if ($filesystem->has($filePath) === false) {
                continue;
            }

            $mail->addAttachment($this->createAttachment($filePath, $fileName));
        }

        return $mail;
    }

    /**
     * Creates an attachment by a file path.
     */
    private function createAttachment(string $filePath, string $fileName): Zend_Mime_Part
    {
        $content = $this->container->get('shopware.filesystem.private')->read($filePath);
        $zendAttachment = new Zend_Mime_Part($content);
        $zendAttachment->type = 'application/pdf';
        $zendAttachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
        $zendAttachment->encoding = Zend_Mime::ENCODING_BASE64;
        $zendAttachment->filename = $fileName;

        return $zendAttachment;
    }

    private function getFileName(int $orderId, int $typeId): string
    {
        $localeId = $this->getOrderLocaleId($orderId);

        $translationReader = $this->container->get(Shopware_Components_Translation::class);
        $translations = $translationReader->read($localeId, 'documents', $typeId, true);

        if (empty($translations) || empty($translations['name'])) {
            return $this->getDefaultName($typeId) . '.pdf';
        }

        return $translations['name'] . '.pdf';
    }

    /**
     * Returns the locale id from the order
     */
    private function getOrderLocaleId(int $orderId): string
    {
        return (string) $this->container->get(Connection::class)
            ->createQueryBuilder()
            ->select('language')
            ->from('s_order')
            ->where('id = :orderId')
            ->setParameter('orderId', $orderId)
            ->execute()
            ->fetchOne();
    }

    /**
     * Gets the default name from the document template
     */
    private function getDefaultName(int $typeId): string
    {
        return (string) $this->container->get(Connection::class)->createQueryBuilder()->select('name')
            ->from('s_core_documents')
            ->where('`id` = :typeId')
            ->setParameter('typeId', $typeId)
            ->execute()
            ->fetchOne();
    }

    /**
     * Internal helper function which is used from the batch function and the createDocumentAction.
     * The batch function fired from the batch window to create multiple documents for many orders.
     * The createDocumentAction fired from the detail page when the user clicks the "create Document button"
     */
    private function createDocument(int $orderId, int $documentType): void
    {
        $renderer = strtolower($this->Request()->getParam('renderer', 'pdf')); // html / pdf
        if (!\in_array($renderer, ['html', 'pdf'])) {
            $renderer = 'pdf';
        }

        $deliveryDate = $this->Request()->getParam('deliveryDate');
        if (!empty($deliveryDate)) {
            $deliveryDate = new DateTime($deliveryDate);
            $deliveryDate = $deliveryDate->format('d.m.Y');
        }

        $displayDate = $this->Request()->getParam('displayDate');
        if (!empty($displayDate)) {
            $displayDate = new DateTime($displayDate);
            $displayDate = $displayDate->format('d.m.Y');
        }

        $document = Shopware_Components_Document::initDocument(
            $orderId,
            $documentType,
            [
                'netto' => (bool) $this->Request()->getParam('taxFree', false),
                'bid' => $this->Request()->getParam('invoiceNumber'),
                'voucher' => $this->Request()->getParam('voucher'),
                'date' => $displayDate,
                'delivery_date' => $deliveryDate,
                // Don't show shipping costs on delivery note #SW-4303
                'shippingCostsAsPosition' => $documentType !== 2,
                '_renderer' => $renderer,
                '_preview' => $this->Request()->getParam('preview', false),
                '_previewForcePagebreak' => $this->Request()->getParam('pageBreak'),
                '_previewSample' => $this->Request()->getParam('sampleData'),
                'docComment' => $this->Request()->getParam('docComment'),
                'forceTaxCheck' => $this->Request()->getParam('forceTaxCheck', false),
            ]
        );
        $document->render();

        if ($renderer === 'html') {
            exit;
        }
    }

    /**
     * Internal helper function which insert the order detail association data into the passed data array
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function getPositionAssociatedData(array $data, Order $order): array
    {
        // Checks if the status id for the position is passed and search for the assigned status model
        if ($data['statusId'] >= 0) {
            $data['status'] = $this->getManager()->find(DetailStatus::class, $data['statusId']);
        } else {
            unset($data['status']);
        }

        $data = $this->checkTaxRule($data, $order);

        $variant = $this->getManager()->getRepository(ProductVariant::class)
            ->findOneBy(['number' => $data['articleNumber']]);

        // Load ean, unit and pack unit (translate if needed)
        if ($variant instanceof ProductVariant) {
            $mainVariant = $variant->getArticle()->getMainDetail();
            $data['ean'] = $variant->getEan();
            if (!\is_string($data['ean']) && $mainVariant instanceof ProductVariant) {
                $data['ean'] = $mainVariant->getEan();
            }
            $unit = $variant->getUnit();
            if (!$unit instanceof Unit && $mainVariant instanceof ProductVariant) {
                $unit = $mainVariant->getUnit();
            }
            $data['unit'] = $unit instanceof Unit ? $unit->getName() : null;
            $data['packunit'] = $variant->getPackUnit();
            if (!\is_string($data['packunit']) && $mainVariant instanceof ProductVariant) {
                $data['packunit'] = $mainVariant->getPackUnit();
            }

            $languageData = Shopware()->Db()->fetchRow(
                'SELECT s_core_shops.default, s_order.language AS languageId
                FROM s_core_shops
                INNER JOIN s_order ON s_order.language = s_core_shops.id
                WHERE s_order.id = :orderId
                LIMIT 1',
                [
                    'orderId' => $data['orderId'],
                ]
            );

            if (!$languageData['default']) {
                $translator = $this->container->get(Shopware_Components_Translation::class);

                // Translate unit
                if ($unit) {
                    $unitTranslation = $translator->read(
                        $languageData['languageId'],
                        'config_units'
                    );

                    $data['unit'] = $unit->getName();
                    if (!empty($unitTranslation[$unit->getId()]['description'])) {
                        $data['unit'] = $unitTranslation[$unit->getId()]['description'];
                    }
                }

                $productTranslation = [];

                // Load variant translations if we are adding a variant to the order
                if ($mainVariant instanceof ProductVariant && $variant->getId() !== $mainVariant->getId()) {
                    $productTranslation = $translator->read(
                        $languageData['languageId'],
                        'variant',
                        $variant->getId()
                    );
                }

                // Load product translations if we are adding a main product or the variant translation is incomplete
                if (empty($productTranslation['packUnit'])
                    || ($mainVariant instanceof ProductVariant && $variant->getId() === $mainVariant->getId())
                ) {
                    $productTranslation = $translator->read(
                        $languageData['languageId'],
                        'article',
                        $variant->getArticle()->getId()
                    );
                }

                if (!empty($productTranslation['packUnit'])) {
                    $data['packUnit'] = $productTranslation['packUnit'];
                }
            }
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function checkTaxRule(array $data, Order $order): array
    {
        $taxId = $data['taxId'];
        if (empty($taxId)) {
            unset($data['tax']);

            return $data;
        }
        $tax = $this->getManager()->find(Tax::class, $taxId);
        if ($tax instanceof Tax) {
            $data['tax'] = $tax;
            $data['taxRate'] = (float) $tax->getTax();
        }

        $shop = $order->getShop();
        $areaId = null;
        $countryId = null;
        $billingAddress = $order->getBilling();
        if ($billingAddress instanceof Billing) {
            $countryId = $billingAddress->getCountry()->getId();

            $area = $billingAddress->getCountry()->getArea();
            if ($area instanceof Area) {
                $areaId = $area->getId();
            }
        }
        $shopContext = $this->get('shopware_storefront.shop_context_factory')->create(
            $shop->getBaseUrl() ?? '',
            $shop->getId(),
            null,
            null,
            $areaId,
            $countryId
        );
        $taxRule = $shopContext->getTaxRule($taxId);
        if ($taxRule instanceof TaxStruct) {
            $data['taxRate'] = (float) $taxRule->getTax();
        }

        return $data;
    }

    /**
     * Internal helper function which insert the order association data into the passed data array.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function getAssociatedData(array $data): array
    {
        // Check if a customer id has passed and fill the customer element with the associated customer model
        if (!empty($data['customerId'])) {
            $data['customer'] = $this->getManager()->find(Customer::class, $data['customerId']);
        } else {
            //if no customer id passed, we have to unset the array element, otherwise the existing customer model would be overwritten
            unset($data['customer']);
        }

        // If a payment id passed, load the associated payment model
        if (!empty($data['paymentId'])) {
            $data['payment'] = $this->getManager()->find(Payment::class, $data['paymentId']);
        } else {
            unset($data['payment']);
        }

        // If a dispatch id is passed, load the associated dispatch model
        if (!empty($data['dispatchId'])) {
            $data['dispatch'] = $this->getManager()->find(Dispatch::class, $data['dispatchId']);
        } else {
            unset($data['dispatch']);
        }

        // If a shop id is passed, load the associated shop model
        if (!empty($data['shopId'])) {
            $data['shop'] = $this->getManager()->find(Shop::class, $data['shopId']);
        } else {
            unset($data['shop']);
        }

        // If a status id is passed, load the associated order status model
        if (isset($data['status'])) {
            $data['orderStatus'] = $this->getManager()->find(Status::class, $data['status']);
        } else {
            unset($data['orderStatus']);
        }

        // If a payment status id is passed, load the associated payment status model
        if (isset($data['cleared'])) {
            $data['paymentStatus'] = $this->getManager()->find(Status::class, $data['cleared']);
        } else {
            unset($data['paymentStatus']);
        }

        // The documents will be created over the "createDocumentAction" so we have to unset the array element, otherwise the
        // created documents models would be overwritten.
        // For now the paymentInstances information is not editable, so it's just discarded at this point
        unset($data['documents'], $data['paymentInstances']);

        $data['billing'] = $this->prepareAddressData($data['billing'][0]);
        $data['shipping'] = $this->prepareAddressData($data['shipping'][0]);

        // Unset calculated values
        unset($data['invoiceAmountNet'], $data['invoiceAmountEuro']);

        // At last, we return the prepared associated data
        return $data;
    }

    /**
     * Creates the status mail order for the passed order id and new status object.
     *
     * @return array{mail: Enlight_Components_Mail, data: array<string, mixed>}|null
     */
    private function getMailForOrder(int $orderId, ?int $statusId, ?int $documentTypeId = null): ?array
    {
        $templateName = null;

        if ($documentTypeId !== null) {
            $templateName = $this->getTemplateNameForDocumentTypeId($documentTypeId);
        }

        $mail = $this->get('modules')->Order()->createStatusMail($orderId, (int) $statusId, $templateName);
        if (!$mail instanceof Enlight_Components_Mail) {
            return null;
        }

        return [
            'mail' => $mail,
            'data' => [
                'error' => false,
                'content' => $mail->getPlainBodyText(),
                'contentHtml' => $mail->getPlainBody(),
                'subject' => $mail->getPlainSubject(),
                'to' => implode(', ', $mail->getTo()),
                'fromMail' => $mail->getFrom(),
                'fromName' => $mail->getFromName(),
                'sent' => false,
                'isHtml' => !empty($mail->getPlainBody()),
                'orderId' => $orderId,
            ],
        ];
    }

    /**
     * @param string[] $numbers
     *
     * @return array<string, int>
     */
    private function getVariantsStock(array $numbers): array
    {
        $query = Shopware()->Container()->get(Connection::class)->createQueryBuilder();
        $query->select(['variant.ordernumber', 'variant.instock']);
        $query->from('s_articles_details', 'variant');
        $query->where('variant.ordernumber IN (:numbers)');
        $query->setParameter(':numbers', $numbers, Connection::PARAM_STR_ARRAY);

        $variantInStock = [];
        foreach ($query->execute()->fetchAllKeyValue() as $number => $stock) {
            $variantInStock[(string) $number] = (int) $stock;
        }

        return $variantInStock;
    }

    private function downloadFileFromFilesystem(string $path): ?string
    {
        $filesystem = $this->container->get('shopware.filesystem.private');
        $tmpFile = tempnam(sys_get_temp_dir(), 'merge_document');
        if (!\is_string($tmpFile)) {
            $this->View()->assign([
                'success' => false,
                'message' => 'Could not create temporary file.',
            ]);

            return null;
        }

        $source = $filesystem->readStream($path);
        if (!\is_resource($source)) {
            $this->View()->assign([
                'success' => false,
                'message' => sprintf('Could not read from path: %s', $path),
            ]);

            return null;
        }
        $downstream = fopen($tmpFile, 'wb');
        if (!\is_resource($downstream)) {
            $this->View()->assign([
                'success' => false,
                'message' => sprintf('Could not read from path: %s', $tmpFile),
            ]);

            return null;
        }
        stream_copy_to_stream($source, $downstream);

        return $tmpFile;
    }

    private function createCriteria(): SearchCriteria
    {
        $request = $this->Request();
        $criteria = new SearchCriteria(Order::class);

        $criteria->offset = $request->getParam('start', 0);
        $criteria->limit = $request->getParam('limit', 30);
        $criteria->ids = $request->getParam('ids', []);
        $criteria->sortings = $request->getParam('sort', []);
        $conditions = $request->getParam('filter', []);
        $orderId = (int) $this->Request()->getParam('orderID');
        if ($orderId !== 0) {
            $criteria->ids[] = $orderId;
        }

        $mapped = [];
        foreach ($conditions as $condition) {
            if ($condition['property'] === 'free') {
                $criteria->term = $condition['value'];
                continue;
            }

            if ($condition['property'] === 'billing.countryId') {
                $condition['property'] = 'billingCountryId';
            } elseif ($condition['property'] === 'shipping.countryId') {
                $condition['property'] = 'shippingCountryId';
            } else {
                $name = explode('.', $condition['property']);
                $name = array_pop($name);
                $condition['property'] = $name;
            }

            if ($condition['property'] === 'to') {
                $condition['value'] = (new DateTime($condition['value']))->format('Y-m-d');
                $condition['property'] = 'orderTime';
                $condition['expression'] = '<=';
            }

            if ($condition['property'] === 'from') {
                $condition['value'] = (new DateTime($condition['value']))->format('Y-m-d');
                $condition['property'] = 'orderTime';
                $condition['expression'] = '>=';
            }

            $mapped[] = $condition;
        }

        foreach ($criteria->sortings as &$sorting) {
            if ($sorting['property'] === 'customerEmail') {
                $sorting['property'] = 'email.raw';
            }
            if ($sorting['property'] === 'customerName') {
                $sorting['property'] = 'lastname.raw';
            }
            if ($sorting['property'] === 'number') {
                $sorting['property'] = 'number.raw';
            }
        }

        $criteria->conditions = $mapped;

        return $criteria;
    }

    private function getTemplateNameForDocumentTypeId(?int $documentTypeId = null): string
    {
        // Generic fallback template
        $templateName = 'sORDERDOCUMENTS';

        if ($documentTypeId === null) {
            return $templateName;
        }

        $sql = 'SELECT `name`
                FROM `s_core_config_mails`
                WHERE `name` = (
                    SELECT CONCAT("document_", `key`)
                    FROM `s_core_documents`
                    WHERE id=:documentTypeId
                )';
        $statement = $this->container->get(Connection::class)->prepare($sql);

        $statement->bindValue('documentTypeId', $documentTypeId, PDO::PARAM_INT);
        $result = $statement->executeQuery()->fetchAssociative();

        if (\is_array($result) && \array_key_exists('name', $result)) {
            $templateName = $result['name'];
        }

        return $templateName;
    }

    private function getCurrentLocale(): ?ShopLocale
    {
        return $this->get('auth')->getIdentity()->locale;
    }

    private function checkIfOrderHasBeenModifiedSince(DateTime $lastOrderChange, Order $order, Enlight_Components_Snippet_Namespace $namespace): bool
    {
        $orderChangeDate = $order->getChanged();
        if (!$orderChangeDate instanceof DateTimeInterface) {
            return false;
        }

        if ($orderChangeDate->getTimestamp() === $lastOrderChange->getTimestamp()) {
            return false;
        }

        $params = $this->Request()->getParams();
        $params['changed'] = $orderChangeDate;
        $this->View()->assign([
            'success' => false,
            'data' => $params,
            'overwriteAble' => true,
            'message' => $namespace->get('order_has_been_changed', 'The order has been changed in the meantime. To prevent overwriting these changes, saving the order was aborted. Please close the order and re-open it.'),
        ]);

        return true;
    }
}
