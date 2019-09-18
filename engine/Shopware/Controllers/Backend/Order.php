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

use Doctrine\DBAL\Connection;
use Doctrine\ORM\AbstractQuery;
use Shopware\Bundle\AttributeBundle\Repository\SearchCriteria;
use Shopware\Bundle\MailBundle\Service\LogEntryBuilder;
use Shopware\Components\CSRFWhitelistAware;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Components\Random;
use Shopware\Components\StateTranslatorService;
use Shopware\Models\Article\Detail as ArticleDetail;
use Shopware\Models\Article\Unit;
use Shopware\Models\Country\Country;
use Shopware\Models\Country\State;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Dispatch\Dispatch;
use Shopware\Models\Document\Document as DocumentType;
use Shopware\Models\Mail\Mail;
use Shopware\Models\Order\Billing;
use Shopware\Models\Order\Detail;
use Shopware\Models\Order\DetailStatus;
use Shopware\Models\Order\Document\Document;
use Shopware\Models\Order\Order;
use Shopware\Models\Order\Shipping;
use Shopware\Models\Order\Status;
use Shopware\Models\Payment\Payment;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Tax\Tax;

class Shopware_Controllers_Backend_Order extends Shopware_Controllers_Backend_ExtJs implements CSRFWhitelistAware
{
    /**
     * Order repository. Declared for an fast access to the order repository.
     *
     * @var \Shopware\Models\Order\Repository
     */
    public static $repository;

    /**
     * Shop repository. Declared for an fast access to the shop repository.
     *
     * @var \Shopware\Models\Shop\Repository
     */
    public static $shopRepository;

    /**
     * Country repository. Declared for an fast access to the country repository.
     *
     * @var \Shopware\Models\Country\Repository
     */
    public static $countryRepository;

    /**
     * Payment repository. Declared for an fast access to the country repository.
     *
     * @var \Shopware\Models\Payment\Repository
     */
    public static $paymentRepository;

    /**
     * Dispatch repository. Declared for an fast access to the dispatch repository.
     *
     * @var \Shopware\Models\Dispatch\Repository
     */
    public static $dispatchRepository;

    /**
     * Contains the shopware model manager
     *
     * @var \Shopware\Components\Model\ModelManager
     */
    public static $manager;

    /**
     * Contains the dynamic receipt repository
     *
     * @var \Shopware\Components\Model\ModelRepository
     */
    public static $documentRepository;

    /**
     * Registers the different acl permission for the different controller actions.
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
     */
    public function preDispatch()
    {
        $actions = ['index', 'load', 'skeleton', 'extends', 'mergeDocuments'];
        if (!in_array($this->Request()->getActionName(), $actions)) {
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

    public function loadListAction()
    {
        $filters = [['property' => 'status.id', 'expression' => '!=', 'value' => '-1']];
        $orderState = $this->getRepository()->getOrderStatusQuery($filters)->getArrayResult();
        $paymentState = $this->getRepository()->getPaymentStatusQuery()->getArrayResult();
        $positionStatus = $this->getRepository()->getDetailStatusQuery()->getArrayResult();

        $stateTranslator = $this->get('shopware.components.state_translator');

        $orderState = array_map(function ($orderStateItem) use ($stateTranslator) {
            $orderStateItem = $stateTranslator->translateState(StateTranslatorService::STATE_ORDER, $orderStateItem);

            return $orderStateItem;
        }, $orderState);

        $paymentState = array_map(function ($paymentStateItem) use ($stateTranslator) {
            $paymentStateItem = $stateTranslator->translateState(StateTranslatorService::STATE_PAYMENT, $paymentStateItem);

            return $paymentStateItem;
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
     * Get documents of a specific type for the given orders
     *
     * @param int[]  $orderIds
     * @param string $docType
     *
     * @return \Doctrine\ORM\Query
     */
    public function getOrderDocumentsQuery($orderIds, $docType)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select([
            'orders',
            'documents',
        ]);

        $builder->from(Order::class, 'orders');
        $builder->leftJoin('orders.documents', 'documents')
            ->where('documents.typeId = :type')
            ->andWhere('orders.id IN (:orderIds)')
            ->setParameter('orderIds', $orderIds, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY)
            ->setParameter(':type', $docType);

        return $builder->getQuery();
    }

    /**
     * This class has its own OrderStatusQuery as we need to get rid of states with status.id = -1
     *
     * @param array|null $filter
     * @param array|null $order
     * @param int|null   $offset
     * @param int|null   $limit
     *
     * @return \Doctrine\ORM\Query
     */
    public function getOrderStatusQuery($filter = null, $order = null, $offset = null, $limit = null)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
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
     */
    public function loadStoresAction()
    {
        $id = $this->Request()->getParam('orderId');
        if ($id === null) {
            $this->View()->assign(['success' => false, 'message' => 'No orderId passed']);

            return;
        }

        $stateTranslator = $this->get('shopware.components.state_translator');

        $orderState = $this->getOrderStatusQuery()->getArrayResult();
        $paymentState = $this->getRepository()->getPaymentStatusQuery()->getArrayResult();

        $orderState = array_map(function ($orderStateItem) use ($stateTranslator) {
            $orderStateItem = $stateTranslator->translateState(StateTranslatorService::STATE_ORDER, $orderStateItem);

            return $orderStateItem;
        }, $orderState);

        $paymentState = array_map(function ($paymentStateItem) use ($stateTranslator) {
            $paymentStateItem = $stateTranslator->translateState(StateTranslatorService::STATE_PAYMENT, $paymentStateItem);

            return $paymentStateItem;
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

        // translate objects
        $translationComponent = $this->get('translation');
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
            if (!is_array($filter)) {
                $filter = [];
            }
            $filter[] = $orderIdFilter;
        }

        $list = $this->getList($filter, $sort, $offset, $limit);

        $translationComponent = $this->get('translation');
        $list['data'] = $translationComponent->translateOrders($list['data']);

        $this->View()->assign($list);
    }

    /**
     * Returns an array of all defined taxes. Used for the position grid combo box on the detail page of the backend
     * order module.
     */
    public function getTaxAction()
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $tax = $builder->select(['tax'])
            ->from(Tax::class, 'tax')
            ->getQuery()
            ->getArrayResult();

        $this->View()->assign(['success' => true, 'data' => $tax]);
    }

    /**
     * The getVouchers function is used by the extJs voucher store which used for a
     * combo box on the order detail page.
     */
    public function getVouchersAction()
    {
        $vouchers = $this->getRepository()->getVoucherQuery()->getArrayResult();
        $this->View()->assign(['success' => true, 'data' => $vouchers]);
    }

    /**
     * Returns all supported document types. The data is used for the configuration panel.
     */
    public function getDocumentTypesAction()
    {
        $types = $this->getRepository()->getDocumentTypesQuery()->getArrayResult();
        $this->View()->assign(['success' => true, 'data' => $types]);
    }

    /**
     * Event listener function of the history store in the order backend module.
     * Returns the status history of the passed order.
     */
    public function getStatusHistoryAction()
    {
        $orderId = $this->Request()->getParam('orderID');
        $limit = $this->Request()->getParam('limit', 20);
        $offset = $this->Request()->getParam('start', 0);
        $sort = $this->Request()->getParam('sort', [['property' => 'history.changeDate', 'direction' => 'DESC']]);

        /** @var Enlight_Components_Snippet_Namespace $namespace */
        $namespace = Shopware()->Snippets()->getNamespace('backend/order');

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
     */
    public function saveAction()
    {
        $id = (int) $this->Request()->getParam('id');

        /** @var Enlight_Components_Snippet_Namespace $namespace */
        $namespace = Shopware()->Snippets()->getNamespace('backend/order/main');

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
        if (!$shipping instanceof \Shopware\Models\Order\Shipping) {
            $shipping = new Shipping();
        }

        if (!$billing instanceof \Shopware\Models\Order\Billing) {
            $billing = new Billing();
        }
        // Get all passed order data
        $data = $this->Request()->getParams();

        if ($order->getChanged() !== null) {
            try {
                $changed = new \DateTime($data['changed']);
            } catch (Exception $e) {
                // If we have a invalid date caused by imports
                $changed = $order->getChanged();
            }

            if ($changed->getTimestamp() < 0 && $changed->getChanged()->getTimestamp() < 0) {
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

        // Before we can create the status mail, we need to save the order data. Otherwise
        // the status mail would be created with the old order status and amount.
        /** @var \Shopware\Models\Order\Order $order */
        $statusBefore = $order->getOrderStatus();
        $clearedBefore = $order->getPaymentStatus();
        $invoiceShippingBefore = $order->getInvoiceShipping();
        $invoiceShippingNetBefore = $order->getInvoiceShippingNet();

        if (!empty($data['clearedDate'])) {
            try {
                $data['clearedDate'] = new \DateTime($data['clearedDate']);
            } catch (\Exception $e) {
                $data['clearedDate'] = null;
            }
        }

        if (isset($data['orderTime'])) {
            unset($data['orderTime']);
        }

        $order->fromArray($data);

        // Check if the invoice shipping has been changed
        $invoiceShippingChanged = (bool) ($invoiceShippingBefore != $order->getInvoiceShipping());
        $invoiceShippingNetChanged = (bool) ($invoiceShippingNetBefore != $order->getInvoiceShippingNet());
        if ($invoiceShippingChanged || $invoiceShippingNetChanged) {
            // Recalculate the new invoice amount
            $order->calculateInvoiceAmount();
        }

        Shopware()->Models()->flush();
        Shopware()->Models()->clear();
        $order = $this->getRepository()->find($id);

        //if the status has been changed an status mail is created.
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
            } catch (\Exception $e) {
                $warning = sprintf(
                    $namespace->get('warning/mail_creation_failed'),
                    $status->getName(),
                    $e->getMessage()
                );
            }
        }

        $data = $this->getOrder($order->getId());
        if (!empty($mail)) {
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
     */
    public function deleteAction()
    {
        /** @var Enlight_Components_Snippet_Namespace $namespace */
        $namespace = Shopware()->Snippets()->getNamespace('backend/order');

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
        $this->getManager()->remove($entity);

        // Performs all of the collected actions.
        $this->getManager()->flush();

        $this->View()->assign(['success' => true]);
    }

    /**
     * CRUD function save and update of the position store of the backend order module.
     * The function handles the update and insert routine of a single order position.
     * After the position has been added to the order, the order invoice amount will be recalculated.
     * The refreshed order will be assigned to the view to refresh the panels and grids.
     */
    public function savePositionAction()
    {
        $id = $this->Request()->getParam('id');

        $orderId = $this->Request()->getParam('orderId');

        /** @var Enlight_Components_Snippet_Namespace $namespace */
        $namespace = Shopware()->Snippets()->getNamespace('backend/order/controller/main');

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
        if (empty($order)) {
            $this->View()->assign([
                'success' => false,
                'data' => $this->Request()->getParams(),
                'message' => $namespace->get('no_order_id_passed', 'No valid order id passed.'),
            ]);

            return;
        }

        // Check whether the order has been modified in the meantime
        $lastOrderChange = new \DateTime($this->Request()->getParam('changed'));
        if ($order->getChanged() !== null && $order->getChanged()->getTimestamp() != $lastOrderChange->getTimestamp()) {
            $params = $this->Request()->getParams();
            $params['changed'] = $order->getChanged();
            $this->View()->assign([
                'success' => false,
                'data' => $params,
                'overwriteAble' => true,
                'message' => $namespace->get('order_has_been_changed', 'The order has been changed in the meantime. To prevent overwriting these changes, saving the order was aborted. Please close the order and re-open it.'),
            ]);

            return;
        }

        // Check if the passed position data is a new position or an existing position.
        if (empty($id)) {
            $position = new Detail();
            $attribute = new Shopware\Models\Attribute\OrderDetail();
            $position->setAttribute($attribute);
            Shopware()->Models()->persist($position);
        } else {
            $detailRepository = Shopware()->Models()->getRepository(Detail::class);
            $position = $detailRepository->find($id);
        }

        $data = $this->Request()->getParams();
        $data['number'] = $order->getNumber();

        $data = $this->getPositionAssociatedData($data);
        // If $data === null, the product was not found
        if ($data === null) {
            $this->View()->assign([
                'success' => false,
                'data' => [],
                'message' => 'The productnumber "' . $this->Request()->getParam('articleNumber', '') . '" is not valid',
            ]);

            return;
        }

        $position->fromArray($data);
        $position->setOrder($order);

        Shopware()->Models()->flush();

        // If the passed data is a new position, the flush function will add the new id to the position model
        $data['id'] = $position->getId();

        // The position model will refresh the product stock, so the product stock
        // will be assigned to the view to refresh the grid or form panel.
        $variantRepository = Shopware()->Models()->getRepository(ArticleDetail::class);
        $variant = $variantRepository->findOneBy(['number' => $position->getArticleNumber()]);
        if ($variant instanceof ArticleDetail) {
            $data['inStock'] = $variant->getInStock();
        }
        $order = $this->getRepository()->find($order->getId());

        Shopware()->Models()->persist($order);
        Shopware()->Models()->flush();

        $invoiceAmount = $order->getInvoiceAmount();
        $changed = $order->getChanged();

        if ($position->getOrder() instanceof \Shopware\Models\Order\Order) {
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
     */
    public function deletePositionAction()
    {
        /** @var Enlight_Components_Snippet_Namespace $namespace */
        $namespace = Shopware()->Snippets()->getNamespace('backend/order/controller/main');

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

        /** @var \Shopware\Models\Order\Order $order */
        $order = $this->getRepository()->find($orderId);
        if (empty($order)) {
            $this->View()->assign([
                'success' => false,
                'data' => $this->Request()->getParams(),
                'message' => $namespace->get('no_order_id_passed', 'No valid order id passed.'),
            ]);

            return;
        }

        // Check whether the order has been modified in the meantime
        $lastOrderChange = new \DateTime($this->Request()->getParam('changed'));
        if ($order->getChanged() !== null && $order->getChanged()->getTimestamp() != $lastOrderChange->getTimestamp()) {
            $params = $this->Request()->getParams();
            $params['changed'] = $order->getChanged();
            $this->View()->assign([
                'success' => false,
                'data' => $params,
                'overwriteAble' => true,
                'message' => $namespace->get('order_has_been_changed', 'The order has been changed in the meantime. To prevent overwriting these changes, saving the order was aborted. Please close the order and re-open it.'),
            ]);

            return;
        }

        foreach ($positions as $position) {
            if (empty($position['id'])) {
                continue;
            }
            $model = Shopware()->Models()->find(Detail::class, $position['id']);

            // Check if the model was founded.
            if ($model instanceof \Shopware\Models\Order\Detail) {
                Shopware()->Models()->remove($model);
            }
        }
        // After each model has been removed to executes the doctrine flush.
        Shopware()->Models()->flush();

        $order->calculateInvoiceAmount();
        Shopware()->Models()->flush();

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
     * the function will create for each order an status mail which will be assigned to the passed order
     * and will be displayed in the email panel on the right side of the batch window.
     * If the parameter "autoSend" is set to true (configurable over the checkbox in the form panel) each
     * created status mail will be send directly.
     */
    public function batchProcessAction()
    {
        $autoSend = $this->Request()->getParam('autoSend') === 'true';
        $orders = $this->Request()->getParam('orders', [0 => $this->Request()->getParams()]);
        $documentType = $this->Request()->getParam('docType');
        $documentMode = $this->Request()->getParam('mode');
        $addAttachments = $this->request->getParam('addAttachments') === 'true';

        /** @var Enlight_Components_Snippet_Namespace $namespace */
        $namespace = $this->get('snippets')->getNamespace('backend/order');

        if (empty($orders)) {
            $this->View()->assign([
                'success' => false,
                'data' => $this->Request()->getParams(),
                'message' => $namespace->get('no_order_id_passed', 'No valid order id passed.'),
            ]);

            return;
        }

        $modelManager = $this->get('models');
        /** @var \Shopware\Components\StateTranslatorServiceInterface $stateTranslator */
        $stateTranslator = $this->get('shopware.components.state_translator');

        $previousLocale = $this->getCurrentLocale();

        foreach ($orders as &$data) {
            $data['success'] = false;
            $data['errorMessage'] = $namespace->get('no_order_id_passed', 'No valid order id passed.');

            if (empty($data) || empty($data['id'])) {
                continue;
            }

            /** @var Order|null $order */
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
                This would create a new Shop if we execute an flush(); Only create order documents when requested.
            */
            if ($documentType) {
                $this->createOrderDocuments($documentType, $documentMode, $order);
            }

            if ($previousLocale) {
                // This is necessary, since the "checkOrderStatus" method might change the locale due to translation issues
                // when sending an order status mail. Therefore we reset it here to the chosen backend language.
                $this->get('snippets')->setLocale($previousLocale);
                $this->get('snippets')->resetShop();
            }

            $data['paymentStatus'] = $stateTranslator->translateState(StateTranslatorService::STATE_PAYMENT, $modelManager->toArray($order->getPaymentStatus()));
            $data['orderStatus'] = $stateTranslator->translateState(StateTranslatorService::STATE_ORDER, $modelManager->toArray($order->getOrderStatus()));

            try {
                // The method '$this->checkOrderStatus()' (even its name would not imply that) sends mails and can fail
                // with an exception. Catch this exception, so the batch process does not abort.
                $data['mail'] = $this->checkOrderStatus($order, $statusBefore, $clearedBefore, $autoSend, $documentType, $addAttachments);
            } catch (\Exception $e) {
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

        if ($data->orders === null || count($data->orders) === 0) {
            $this->View()->assign([
                'success' => false,
                'message' => 'No valid order id passed.',
            ]);

            return;
        }

        $files = [];
        $query = $this->getOrderDocumentsQuery($data->orders, $data->docType);
        /** @var Order[] $models */
        $models = $query->getResult();
        foreach ($models as $model) {
            foreach ($model->getDocuments() as $document) {
                $files[] = $this->downloadFileFromFilesystem(sprintf('documents/%s.pdf', $document->getHash()));
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
     */
    public function sendMailAction()
    {
        $data = $this->Request()->getParams();
        $orderId = $this->request->getParam('orderId');
        $attachments = $this->request->getParam('attachment');

        /** @var Enlight_Components_Snippet_Namespace $namespace */
        $namespace = Shopware()->Snippets()->getNamespace('backend/order');

        if (empty($data)) {
            $this->View()->assign([
                'success' => false,
                'data' => $data,
                'message' => $namespace->get('no_data_passed', 'No mail data passed'),
            ]);

            return;
        }

        $mailTemplateName = $this->Request()->getParam('templateName') ?: 'sORDERDOCUMENTS';

        /** @var Enlight_Components_Mail $mail */
        $mail = $this->container->get('modules')->Order()->createStatusMail($orderId, 0, $mailTemplateName);
        $mail->clearRecipients();
        $mail->clearSubject();
        $mail->clearFrom();

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

        /** @var Enlight_Event_EventManager $events */
        $events = $this->get('events');
        $mailData = $events->filter(
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

        Shopware()->Modules()->Order()->sendStatusMail($mail);

        $this->View()->assign([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Deletes a document by the requested document id.
     */
    public function deleteDocumentAction()
    {
        $filesystem = $this->container->get('shopware.filesystem.private');
        $documentId = $this->request->getParam('documentId');
        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = $this->container->get('dbal_connection');
        $queryBuilder = $connection->createQueryBuilder();

        try {
            $documentHash = $queryBuilder->select('hash')
                ->from('s_order_documents')
                ->where('id = :documentId')
                ->setParameter('documentId', $documentId)
                ->execute()
                ->fetchColumn();

            $queryBuilder = $connection->createQueryBuilder();
            $queryBuilder->delete('s_order_documents')
                ->where('id = :documentId')
                ->setParameter('documentId', $documentId)
                ->execute();

            $file = sprintf('documents/%s.pdf', $documentHash);
            if ($filesystem->has($file)) {
                $filesystem->delete($file);
            }
        } catch (\Exception $exception) {
            $this->View()->assign([
                'success' => false,
                'errorMessage' => $exception->getMessage(),
            ]);
        }

        $this->View()->assign('success', true);
    }

    /**
     * Creates a mail by the requested orderId and assign it to the view.
     */
    public function createMailAction()
    {
        $orderId = (int) $this->Request()->getParam('orderId');
        $mailTemplateName = $this->Request()->getParam('mailTemplateName', 'sORDERDOCUMENTS');

        /** @var Enlight_Components_Mail $mail */
        $mail = Shopware()->Modules()->Order()->createStatusMail($orderId, 0, $mailTemplateName);

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
     */
    public function getMailTemplatesAction()
    {
        $limit = (int) $this->Request()->getParam('limit', 100);
        $offset = (int) $this->Request()->getParam('start', 0);
        $order = $this->Request()->getParam('sort', []);
        $filter = $this->Request()->getParam('filter', []);

        /** @var QueryBuilder $mailTemplatesQuery */
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

        /** @var DocumentType $documentType */
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
     */
    public function createDocumentAction()
    {
        $orderId = $this->Request()->getParam('orderId');
        $documentType = $this->Request()->getParam('documentType');

        // Needs to be called this early since $this->createDocument boots the
        // shop, the order was made in, and thereby destroys the backend session
        $translationComponent = $this->get('translation');

        if (!empty($orderId) && !empty($documentType)) {
            $this->createDocument($orderId, $documentType);
        }

        $query = $this->getRepository()->getOrdersQuery([['property' => 'orders.id', 'value' => $orderId]], null, 0, 1);
        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $paginator = $this->getModelManager()->createPaginator($query);
        $order = $paginator->getIterator()->getArrayCopy();

        $order = $translationComponent->translateOrders($order);

        $this->View()->assign([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Fires when the user want to open a generated order document from the backend order module.
     * Returns the created pdf file.
     */
    public function openPdfAction()
    {
        $filesystem = $this->container->get('shopware.filesystem.private');
        $file = sprintf('documents/%s.pdf', basename($this->Request()->getParam('id', null)));

        if ($filesystem->has($file) === false) {
            $this->View()->assign([
                'success' => false,
                'data' => $this->Request()->getParams(),
                'message' => 'File not exist',
            ]);

            return;
        }

        // Disable Smarty rendering
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        $this->Front()->Plugins()->Json()->setRenderer(false);

        $orderModel = Shopware()->Models()->getRepository(Document::class)->findBy(['hash' => $this->Request()->getParam('id')]);
        $orderModel = Shopware()->Models()->toArray($orderModel);
        $orderId = $orderModel[0]['documentId'];

        $response = $this->Response();
        $response->headers->set('cache-control', 'public', true);
        $response->headers->set('content-description', 'File Transfer');
        $response->headers->set('content-disposition', 'attachment; filename=' . $orderId . '.pdf');
        $response->headers->set('content-type', 'application/pdf');
        $response->headers->set('content-transfer-encoding', 'binary');
        $response->headers->set('content-length', $filesystem->getSize($file));
        $response->sendHeaders();
        $response->sendResponse();

        $upstream = $filesystem->readStream($file);
        $downstream = fopen('php://output', 'wb');

        while (!feof($upstream)) {
            fwrite($downstream, fread($upstream, 4096));
        }
    }

    /**
     * Returns filterable partners
     */
    public function getPartnersAction()
    {
        $limit = $this->Request()->getParam('limit', 20);
        $offset = $this->Request()->getParam('start', 0);

        /** @var \Doctrine\DBAL\Query\QueryBuilder $dbalBuilder */
        $dbalBuilder = $this->get('dbal_connection')->createQueryBuilder();

        $data = $dbalBuilder
            ->select(['SQL_CALC_FOUND_ROWS MAX(IFNULL(partner.company, orders.partnerID)) as name', 'orders.partnerID as `value`'])
            ->from('s_order', 'orders')
            ->leftJoin('orders', 's_emarketing_partner', 'partner', 'orders.partnerID = partner.idcode')
            ->where('orders.partnerID IS NOT NULL')
            ->andWhere('orders.partnerID != ""')
            ->groupBy('orders.partnerID')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->execute()
            ->fetchAll();

        $total = (int) $this->get('dbal_connection')->fetchColumn('SELECT FOUND_ROWS()');

        $this->View()->assign(['success' => true, 'data' => $data, 'total' => $total]);
    }

    /**
     * Returns the shopware model manager
     *
     * @return Shopware\Components\Model\ModelManager
     */
    protected function getManager()
    {
        if (self::$manager === null) {
            self::$manager = Shopware()->Models();
        }

        return self::$manager;
    }

    /**
     * Helper function to get access on the static declared repository
     *
     * @return Shopware\Models\Order\Repository|null
     */
    protected function getRepository()
    {
        if (self::$repository === null) {
            self::$repository = Shopware()->Models()->getRepository(Order::class);
        }

        return self::$repository;
    }

    /**
     * Helper function to get access on the static declared repository
     *
     * @return Shopware\Models\Shop\Repository|null
     */
    protected function getShopRepository()
    {
        if (self::$shopRepository === null) {
            self::$shopRepository = Shopware()->Models()->getRepository(Shop::class);
        }

        return self::$shopRepository;
    }

    /**
     * Helper function to get access on the static declared repository
     *
     * @return Shopware\Models\Country\Repository|null
     */
    protected function getCountryRepository()
    {
        if (self::$countryRepository === null) {
            self::$countryRepository = Shopware()->Models()->getRepository(Country::class);
        }

        return self::$countryRepository;
    }

    /**
     * Helper function to get access on the static declared repository
     *
     * @return Shopware\Models\Payment\Repository|null
     */
    protected function getPaymentRepository()
    {
        if (self::$paymentRepository === null) {
            self::$paymentRepository = Shopware()->Models()->getRepository(Payment::class);
        }

        return self::$paymentRepository;
    }

    /**
     * Helper function to get access on the static declared repository
     *
     * @return Shopware\Models\Dispatch\Repository|null
     */
    protected function getDispatchRepository()
    {
        if (self::$dispatchRepository === null) {
            self::$dispatchRepository = Shopware()->Models()->getRepository(Dispatch::class);
        }

        return self::$dispatchRepository;
    }

    /**
     * @deprecated in 5.6, will be removed in 5.7 without a replacement
     *
     * Helper function to get access on the static declared repository
     *
     * @return \Shopware\Components\Model\ModelRepository
     */
    protected function getDocumentRepository()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.7. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        if (self::$documentRepository === null) {
            self::$documentRepository = Shopware()->Models()->getRepository(Document::class);
        }

        return self::$documentRepository;
    }

    /**
     * @param array[]  $filter
     * @param array[]  $sort
     * @param int|null $offset
     * @param int|null $limit
     *
     * @return array
     */
    protected function getList($filter, $sort, $offset, $limit)
    {
        $sort = $this->resolveSortParameter($sort);

        if ($this->container->getParameter('shopware.es.backend.enabled')) {
            $repository = $this->container->get('shopware_attribute.order_repository');
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

        /** @var Enlight_Components_Snippet_Namespace $namespace */
        $namespace = $this->get('snippets')->getNamespace('frontend/salutation');

        /** @var \Shopware\Components\StateTranslatorServiceInterface $stateTranslator */
        $stateTranslator = $this->get('shopware.components.state_translator');

        $numbers = [];
        foreach ($orders as $orderKey => $order) {
            $temp = array_column($order['details'], 'articleNumber');
            $numbers = array_merge($numbers, (array) $temp);

            $orders[$orderKey]['orderStatus'] = $stateTranslator->translateState(StateTranslatorService::STATE_ORDER, $order['orderStatus']);
            $orders[$orderKey]['paymentStatus'] = $stateTranslator->translateState(StateTranslatorService::STATE_PAYMENT, $order['paymentStatus']);
        }

        $stocks = $this->getVariantsStock($numbers);

        $result = [];
        foreach ($ids as $id) {
            if (!array_key_exists($id, $orders)) {
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
     * Prepare address data - loads countryModel from a given countryId
     *
     * @return array
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
            $stateModel = Shopware()->Models()->find(State::class, $data['stateId']);
            if ($stateModel) {
                $data['state'] = $stateModel;
            }
            unset($data['stateId']);
        }

        return $data;
    }

    /**
     * @param array[] $sorts
     *
     * @return array[]
     */
    private function resolveSortParameter($sorts)
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
                case strpos($sort['property'], '.') === false:
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
     * @param array[] $orders
     * @param array[] $associations
     * @param string  $arrayKey
     *
     * @return array[]
     */
    private function assignAssociation($orders, $associations, $arrayKey)
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
     * @param int $id
     */
    private function getOrder($id)
    {
        $query = $this->getRepository()->getOrdersQuery([['property' => 'orders.id', 'value' => $id]], []);
        $data = $query->getArrayResult();

        return $data[0];
    }

    /**
     * Simple helper function which actually merges a given array of document-paths
     *
     * @return string The created document's url
     */
    private function mergeDocuments(array $paths)
    {
        $pdf = new FPDI();

        foreach ($paths as $path) {
            $numPages = $pdf->setSourceFile($path);
            for ($i = 1; $i <= $numPages; ++$i) {
                $template = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($template);
                $pdf->AddPage('P', [$size['w'], $size['h']]);
                $pdf->useTemplate($template);
            }
        }

        $hash = Random::getAlphanumericString(32);

        $pdf->Output($hash . '.pdf', 'D');

        $this->Response()->headers->set('content-type', 'application/x-download');
    }

    /**
     * Internal helper function which checks if the batch process needs a document creation.
     *
     * @param int                          $documentTypeId
     * @param int                          $documentMode
     * @param \Shopware\Models\Order\Order $order
     */
    private function createOrderDocuments($documentTypeId, $documentMode, $order)
    {
        if (!empty($documentTypeId)) {
            $documentTypeId = (int) $documentTypeId;
            $documentMode = (int) $documentMode;

            /** @var \Shopware\Models\Order\Document\Document[] $documents */
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
    }

    /**
     * Internal helper function to check if the order or payment status has been changed.
     * If one of the status changed, the function will create a status mail.
     * If the autoSend parameter is true, the created status mail will be sent directly,
     * if addAttachments and documentType are true/selected as well, the according documents will be attached.
     *
     * @param Order                         $order
     * @param \Shopware\Models\Order\Status $statusBefore
     * @param \Shopware\Models\Order\Status $clearedBefore
     * @param bool                          $autoSend
     * @param int|null                      $documentTypeId
     * @param bool                          $addAttachments
     *
     * @return array|null
     */
    private function checkOrderStatus($order, $statusBefore, $clearedBefore, $autoSend, $documentTypeId, $addAttachments)
    {
        $orderStatusChanged = $order->getOrderStatus()->getId() !== $statusBefore->getId();
        $paymentStatusChanged = $order->getPaymentStatus()->getId() !== $clearedBefore->getId();
        $documentMailSendable = $documentTypeId && $addAttachments;
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

        if (is_object($mail['mail'])) {
            if ($addAttachments) {
                // Attach documents
                $document = $this->getDocument($documentTypeId, $order);
                /** @var Enlight_Components_Mail $mailObject */
                $mailObject = $mail['mail'];
                $mail['mail'] = $this->addAttachments($mailObject, $order->getId(), [$document]);
            }
            if ($autoSend) {
                // Send mail
                /** @var Enlight_Components_Mail $mailObject */
                $mailObject = $mail['mail'];
                $result = Shopware()->Modules()->Order()->sendStatusMail($mailObject);
                $mail['data']['sent'] = is_object($result);
            }

            return $mail['data'];
        }

        return null;
    }

    /**
     * @param int $documentTypeId
     *
     * @return array
     */
    private function getDocument($documentTypeId, Order $order)
    {
        $documentTypeId = (int) $documentTypeId;

        /** @var \Shopware\Models\Order\Document\Document $document */
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
     * @param int $orderId
     * @param int $documentTypeId
     *
     * @return array
     */
    private function getDocumentFromDatabase($orderId, $documentTypeId)
    {
        $queryBuilder = $this->container->get('dbal_connection')->createQueryBuilder();
        $queryResult = $queryBuilder->select('doc.hash, template.id, template.name')
            ->from('s_order_documents', 'doc')
            ->join('doc', 's_core_documents', 'template', 'doc.type = template.id')
            ->where('doc.orderID = :orderId')
            ->andWhere('doc.type = :type')
            ->setParameter('orderId', $orderId, \PDO::PARAM_INT)
            ->setParameter('type', $documentTypeId, \PDO::PARAM_INT)
            ->execute()
            ->fetch(PDO::FETCH_ASSOC);

        if ($queryResult) {
            return [
                'hash' => $queryResult['hash'],
                'type' => [
                    [
                        'id' => $queryResult['id'],
                        'name' => $queryResult['name'],
                    ],
                ],
            ];
        }

        return [];
    }

    /**
     * Adds the requested attachments to the given $mail object
     *
     * @param int|string $orderId
     *
     * @return Enlight_Components_Mail
     */
    private function addAttachments(Enlight_Components_Mail $mail, $orderId, array $attachments = [])
    {
        $filesystem = $this->container->get('shopware.filesystem.private');

        foreach ($attachments as $attachment) {
            $filePath = sprintf('documents/%s.pdf', $attachment['hash']);
            $fileName = $this->getFileName($orderId, $attachment['type'][0]['id']);

            if ($filesystem->has($filePath) === false) {
                continue;
            }

            $mail->addAttachment($this->createAttachment($filePath, $fileName));
        }

        return $mail;
    }

    /**
     * Creates a attachment by a file path.
     *
     * @param string $filePath
     * @param string $fileName
     *
     * @return Zend_Mime_Part
     */
    private function createAttachment($filePath, $fileName)
    {
        $filesystem = $this->container->get('shopware.filesystem.private');

        $content = $filesystem->read($filePath);
        $zendAttachment = new Zend_Mime_Part($content);
        $zendAttachment->type = 'application/pdf';
        $zendAttachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
        $zendAttachment->encoding = Zend_Mime::ENCODING_BASE64;
        $zendAttachment->filename = $fileName;

        return $zendAttachment;
    }

    /**
     * @param int|string $orderId
     * @param int|string $typeId
     * @param string     $fileExtension
     *
     * @return string
     */
    private function getFileName($orderId, $typeId, $fileExtension = '.pdf')
    {
        $localeId = $this->getOrderLocaleId($orderId);

        $translationReader = $this->container->get('translation');
        $translations = $translationReader->read($localeId, 'documents', $typeId, true);

        if (empty($translations) || empty($translations['name'])) {
            return $this->getDefaultName($typeId) . $fileExtension;
        }

        return $translations['name'] . $fileExtension;
    }

    /**
     * Returns the locale id from the order
     *
     * @param int|string $orderId
     *
     * @return bool|string
     */
    private function getOrderLocaleId($orderId)
    {
        $queryBuilder = $this->container->get('dbal_connection')->createQueryBuilder();

        return $queryBuilder->select('language')
            ->from('s_order')
            ->where('id = :orderId')
            ->setParameter('orderId', $orderId)
            ->execute()
            ->fetchColumn();
    }

    /**
     * Gets the default name from the document template
     *
     * @param int|string $typeId
     *
     * @return bool|string
     */
    private function getDefaultName($typeId)
    {
        /** @var \Doctrine\DBAL\Query\QueryBuilder $queryBuilder */
        $queryBuilder = $this->container->get('dbal_connection')->createQueryBuilder();

        return $queryBuilder->select('name')
            ->from('s_core_documents')
            ->where('`id` = :typeId')
            ->setParameter('typeId', $typeId)
            ->execute()
            ->fetchColumn();
    }

    /**
     * Internal helper function which is used from the batch function and the createDocumentAction.
     * The batch function fired from the batch window to create multiple documents for many orders.
     * The createDocumentAction fired from the detail page when the user clicks the "create Document button"
     *
     * @param int $orderId
     * @param int $documentType
     *
     * @return bool
     */
    private function createDocument($orderId, $documentType)
    {
        $renderer = strtolower($this->Request()->getParam('renderer', 'pdf')); // html / pdf
        if (!in_array($renderer, ['html', 'pdf'])) {
            $renderer = 'pdf';
        }

        $deliveryDate = $this->Request()->getParam('deliveryDate');
        if (!empty($deliveryDate)) {
            $deliveryDate = new \DateTime($deliveryDate);
            $deliveryDate = $deliveryDate->format('d.m.Y');
        }

        $displayDate = $this->Request()->getParam('displayDate');
        if (!empty($displayDate)) {
            $displayDate = new \DateTime($displayDate);
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
                'shippingCostsAsPosition' => (int) $documentType !== 2,
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

        return true;
    }

    /**
     * Internal helper function which insert the order detail association data into the passed data array
     *
     * @param array $data
     *
     * @return array|null
     */
    private function getPositionAssociatedData($data)
    {
        // Checks if the status id for the position is passed and search for the assigned status model
        if ($data['statusId'] >= 0) {
            $data['status'] = Shopware()->Models()->find(DetailStatus::class, $data['statusId']);
        } else {
            unset($data['status']);
        }

        // Checks if the tax id for the position is passed and search for the assigned tax model
        if (!empty($data['taxId'])) {
            $tax = Shopware()->Models()->find(Tax::class, $data['taxId']);
            if ($tax instanceof \Shopware\Models\Tax\Tax) {
                $data['tax'] = $tax;
                $data['taxRate'] = $tax->getTax();
            }
        } else {
            unset($data['tax']);
        }

        /** @var ArticleDetail|null $variant */
        $variant = Shopware()->Models()->getRepository(ArticleDetail::class)
            ->findOneBy(['number' => $data['articleNumber']]);

        // Load ean, unit and pack unit (translate if needed)
        if ($variant) {
            $data['ean'] = $variant->getEan() ?: $variant->getArticle()->getMainDetail()->getEan();
            /** @var Unit|null $unit */
            $unit = $variant->getUnit() ?: $variant->getArticle()->getMainDetail()->getUnit();
            $data['unit'] = $unit ? $unit->getName() : null;
            $data['packunit'] = $variant->getPackUnit() ?: $variant->getArticle()->getMainDetail()->getPackUnit();

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
                $translator = $this->container->get('translation');

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
                if ($variant->getId() != $variant->getArticle()->getMainDetail()->getId()) {
                    $productTranslation = $translator->read(
                        $languageData['languageId'],
                        'variant',
                        $variant->getId()
                    );
                }

                // Load product translations if we are adding a main product or the variant translation is incomplete
                if ($variant->getId() == $variant->getArticle()->getMainDetail()->getId()
                    || empty($productTranslation['packUnit'])
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
     * Internal helper function which insert the order association data into the passed data array.
     *
     * @return array
     */
    private function getAssociatedData(array $data)
    {
        // Check if a customer id has passed and fill the customer element with the associated customer model
        if (!empty($data['customerId'])) {
            $data['customer'] = Shopware()->Models()->find(Customer::class, $data['customerId']);
        } else {
            //if no customer id passed, we have to unset the array element, otherwise the existing customer model would be overwritten
            unset($data['customer']);
        }

        // If a payment id passed, load the associated payment model
        if (!empty($data['paymentId'])) {
            $data['payment'] = Shopware()->Models()->find(Payment::class, $data['paymentId']);
        } else {
            unset($data['payment']);
        }

        // If a dispatch id is passed, load the associated dispatch model
        if (!empty($data['dispatchId'])) {
            $data['dispatch'] = Shopware()->Models()->find(Dispatch::class, $data['dispatchId']);
        } else {
            unset($data['dispatch']);
        }

        // If a shop id is passed, load the associated shop model
        if (!empty($data['shopId'])) {
            $data['shop'] = Shopware()->Models()->find(Shop::class, $data['shopId']);
        } else {
            unset($data['shop']);
        }

        // If a status id is passed, load the associated order status model
        if (isset($data['status']) && $data['status'] !== null) {
            $data['orderStatus'] = Shopware()->Models()->find(Status::class, $data['status']);
        } else {
            unset($data['orderStatus']);
        }

        // If a payment status id is passed, load the associated payment status model
        if (isset($data['cleared']) && $data['cleared'] !== null) {
            $data['paymentStatus'] = Shopware()->Models()->find(Status::class, $data['cleared']);
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

        // At last we return the prepared associated data
        return $data;
    }

    /**
     * Creates the status mail order for the passed order id and new status object.
     *
     * @param int      $orderId
     * @param int|null $statusId
     * @param int|null $documentTypeId
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return array
     */
    private function getMailForOrder($orderId, $statusId, $documentTypeId = null)
    {
        $templateName = null;

        if ($documentTypeId !== null) {
            $templateName = $this->getTemplateNameForDocumentTypeId($documentTypeId);
        }

        /** @var Enlight_Components_Mail $mail */
        $mail = Shopware()->Modules()->Order()->createStatusMail($orderId, (int) $statusId, $templateName);

        if ($mail instanceof Enlight_Components_Mail) {
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

        return [];
    }

    /**
     * @param string[] $numbers
     *
     * @return array
     */
    private function getVariantsStock(array $numbers)
    {
        $query = Shopware()->Container()->get('dbal_connection')->createQueryBuilder();
        $query->select(['variant.ordernumber', 'variant.instock']);
        $query->from('s_articles_details', 'variant');
        $query->where('variant.ordernumber IN (:numbers)');
        $query->setParameter(':numbers', $numbers, Connection::PARAM_STR_ARRAY);

        return $query->execute()->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function downloadFileFromFilesystem($path)
    {
        $filesystem = $this->container->get('shopware.filesystem.private');
        $tmpFile = tempnam(sys_get_temp_dir(), 'merge_document');

        $downstream = fopen($tmpFile, 'wb');
        stream_copy_to_stream($filesystem->readStream($path), $downstream);

        return $tmpFile;
    }

    /**
     * @return SearchCriteria
     */
    private function createCriteria()
    {
        $request = $this->Request();
        $criteria = new SearchCriteria(Order::class);

        $criteria->offset = $request->getParam('start', 0);
        $criteria->limit = $request->getParam('limit', 30);
        $criteria->ids = $request->getParam('ids', []);
        $criteria->sortings = $request->getParam('sort', []);
        $conditions = $request->getParam('filter', []);

        if ($orderId = $this->Request()->getParam('orderID')) {
            $criteria->ids[] = (int) $orderId;
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

    /**
     * @param int|null $documentTypeId
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return string
     */
    private function getTemplateNameForDocumentTypeId($documentTypeId = null)
    {
        // Generic fallback template
        $templateName = 'sORDERDOCUMENTS';

        if ($documentTypeId === null) {
            return $templateName;
        }

        $statement = $this->container->get('dbal_connection')
            ->prepare('SELECT `name` FROM `s_core_config_mails` WHERE `name` = (SELECT CONCAT("document_", `key`) FROM `s_core_documents` WHERE id=:documentTypeId)');

        $statement->bindValue('documentTypeId', (int) $documentTypeId, \PDO::PARAM_INT);
        $statement->execute();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        if (!empty($result) || !array_key_exists('name', $result)) {
            $templateName = $result['name'];
        }

        return $templateName;
    }

    /**
     * @return \Shopware\Models\Shop\Locale|null
     */
    private function getCurrentLocale()
    {
        $user = $this->get('auth')->getIdentity();

        return $user->locale;
    }
}
