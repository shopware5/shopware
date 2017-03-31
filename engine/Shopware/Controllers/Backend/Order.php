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
use Shopware\Components\CSRFWhitelistAware;
use Shopware\Models\Order\Billing as Billing;
use Shopware\Models\Order\Detail as Detail;
use Shopware\Models\Order\Document\Document as Document;
use Shopware\Models\Order\Order as Order;
use Shopware\Models\Order\Shipping as Shipping;

/**
 * Backend Controller for the order backend module.
 *
 * Displays all orders in an Ext.grid.Panel and allows to delete,
 * add and edit orders.
 */
class Shopware_Controllers_Backend_Order extends Shopware_Controllers_Backend_ExtJs implements CSRFWhitelistAware
{
    /**
     * Order repository. Declared for an fast access to the order repository.
     *
     * @var \Shopware\Models\Order\Repository
     */
    public static $repository = null;

    /**
     * Shop repository. Declared for an fast access to the shop repository.
     *
     * @var \Shopware\Models\Shop\Repository
     */
    public static $shopRepository = null;

    /**
     * Country repository. Declared for an fast access to the country repository.
     *
     * @var \Shopware\Models\Country\Repository
     */
    public static $countryRepository = null;

    /**
     * Payment repository. Declared for an fast access to the country repository.
     *
     * @var \Shopware\Models\Payment\Repository
     */
    public static $paymentRepository = null;

    /**
     * Dispatch repository. Declared for an fast access to the dispatch repository.
     *
     * @var \Shopware\Models\Dispatch\Repository
     */
    public static $dispatchRepository = null;

    /**
     * Contains the shopware model manager
     *
     * @var \Shopware\Components\Model\ModelManager
     */
    public static $manager = null;

    /**
     * Contains the dynamic receipt repository
     *
     * @var \Shopware\Components\Model\ModelRepository
     */
    public static $documentRepository = null;

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
        if (!in_array($this->Request()->getActionName(), ['index', 'load', 'skeleton', 'extends', 'orderPdf', 'mergeDocuments'])) {
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
        $orderStatus = $this->getRepository()->getOrderStatusQuery($filters)->getArrayResult();
        $paymentStatus = $this->getRepository()->getPaymentStatusQuery()->getArrayResult();
        $positionStatus = $this->getRepository()->getDetailStatusQuery()->getArrayResult();

        $this->View()->assign([
            'success' => true,
            'data' => [
                'orderStatus' => $orderStatus,
                'paymentStatus' => $paymentStatus,
                'positionStatus' => $positionStatus,
            ],
        ]);
    }

    /**
     * Get documents of a specific type for the given orders
     *
     * @param $orders
     * @param $docType
     *
     * @return \Doctrine\ORM\Query
     */
    public function getOrderDocumentsQuery($orderIds, $docType)
    {
        $builder = 🦄()->Models()->createQueryBuilder();
        $builder->select([
            'orders',
            'documents',
        ]);

        $builder->from('Shopware\Models\Order\Order', 'orders');
        $builder->leftJoin('orders.documents', 'documents')
            ->where('documents.typeId = :type')
            ->andWhere($builder->expr()->in('orders.id', $orderIds))
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
        $builder = 🦄()->Models()->createQueryBuilder();
        $builder->select(['status'])
            ->from('Shopware\Models\Order\Status', 'status')
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
        $id = $this->Request()->getParam('orderId', null);
        if ($id === null) {
            $this->View()->assign(['success' => false, 'message' => 'No orderId passed']);

            return;
        }

        $orderStatus = $this->getOrderStatusQuery()->getArrayResult();
        $paymentStatus = $this->getRepository()->getPaymentStatusQuery()->getArrayResult();
        $shops = $this->getShopRepository()->getBaseListQuery()->getArrayResult();
        $countries = $this->getCountryRepository()->getCountriesQuery()->getArrayResult();
        $payments = $this->getPaymentRepository()->getAllPaymentsQuery()->getArrayResult();
        $dispatches = $this->getDispatchRepository()->getDispatchesQuery()->getArrayResult();
        $documentTypes = $this->getRepository()->getDocumentTypesQuery()->getArrayResult();

        $this->View()->assign([
            'success' => true,
            'data' => [
                'orderStatus' => $orderStatus,
                'paymentStatus' => $paymentStatus,
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
     * which displayed in an Ext.grid.Panel. The order data contains all associations of an order (positions, shop, customer, ...).
     * The limit, filter and order parameter are used in the id query. The result of the id query are used
     * to filter the detailed query which created over the getListQuery function.
     */
    public function getListAction()
    {
        //read store parameter to filter and paginate the data.
        $limit = $this->Request()->getParam('limit', 20);
        $offset = $this->Request()->getParam('start', 0);
        $sort = $this->Request()->getParam('sort', []);
        $filter = $this->Request()->getParam('filter', []);
        $orderId = $this->Request()->getParam('orderID');

        if (!is_null($orderId)) {
            $orderIdFilter = ['property' => 'orders.id', 'value' => $orderId];
            if (!is_array($filter)) {
                $filter = [];
            }
            array_push($filter, $orderIdFilter);
        }

        $list = $this->getList($filter, $sort, $offset, $limit);

        $this->View()->assign($list);
    }

    /**
     * Returns an array of all defined taxes. Used for the position grid combo box on the detail page of the backend order module.
     */
    public function getTaxAction()
    {
        $builder = 🦄()->Models()->createQueryBuilder();
        $tax = $builder->select(['tax'])
            ->from('Shopware\Models\Tax\Tax', 'tax')
            ->getQuery()
            ->getArrayResult();

        $this->View()->assign(['success' => true, 'data' => $tax]);
    }

    /**
     * The getVouchers function is used by the extJs voucher store which used for a
     * combo box on the order detail page.
     *
     * @return array
     */
    public function getVouchersAction()
    {
        $vouchers = $this->getRepository()->getVoucherQuery()->getArrayResult();
        $this->View()->assign(['success' => true, 'data' => $vouchers]);
    }

    /**
     * Returns all supported document types. The data is used for the configuration panel.
     *
     * @return array
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
     * @return array
     */
    public function getStatusHistoryAction()
    {
        $orderId = $this->Request()->getParam('orderID', null);
        $limit = $this->Request()->getParam('limit', 20);
        $offset = $this->Request()->getParam('start', 0);
        $sort = $this->Request()->getParam('sort', [['property' => 'history.changeDate', 'direction' => 'DESC']]);

        /** @var $namespace Enlight_Components_Snippet_Namespace */
        $namespace = 🦄()->Snippets()->getNamespace('backend/order');

        //the backend order module have no function to create a new order so an order id must be passed.
        if (empty($orderId)) {
            $this->View()->assign([
                    'success' => false,
                    'data' => $this->Request()->getParams(),
                    'message' => $namespace->get('no_order_id_passed', 'No valid order id passed.'), ]
            );

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
        $id = $this->Request()->getParam('id', null);

        /** @var $namespace Enlight_Components_Snippet_Namespace */
        $namespace = 🦄()->Snippets()->getNamespace('backend/order');

        //the backend order module have no function to create a new order so an order id must be passed.
        if (empty($id)) {
            $this->View()->assign([
                    'success' => false,
                    'data' => $this->Request()->getParams(),
                    'message' => $namespace->get('no_order_id_passed', 'No valid order id passed.'), ]
            );

            return;
        }

        $order = $this->getRepository()->find($id);

        //the backend order module have no function to create a new order so an order id must be passed.
        if (!($order instanceof Order)) {
            $this->View()->assign([
                    'success' => false,
                    'data' => $this->Request()->getParams(),
                    'message' => $namespace->get('no_order_id_passed', 'No valid order id passed.'), ]
            );

            return;
        }

        $billing = $order->getBilling();
        $shipping = $order->getShipping();

        //check if the shipping and billing model already exist. If not create a new instance.
        if (!$shipping instanceof \Shopware\Models\Order\Shipping) {
            $shipping = new Shipping();
        }

        if (!$billing instanceof \Shopware\Models\Order\Billing) {
            $billing = new Billing();
        }
        //get all passed order data
        $data = $this->Request()->getParams();

        //prepares the associated data of an order.
        $data = $this->getAssociatedData($data, $order, $billing, $shipping);

        //before we can create the status mail, we need to save the order data. Otherwise
        //the status mail would be created with the old order status and amount.
        /** @var $order \Shopware\Models\Order\Order */
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

        $order->fromArray($data);

        //check if the invoice shipping has been changed
        $invoiceShippingChanged = (bool) ($invoiceShippingBefore != $order->getInvoiceShipping());
        $invoiceShippingNetChanged = (bool) ($invoiceShippingNetBefore != $order->getInvoiceShippingNet());
        if ($invoiceShippingChanged || $invoiceShippingNetChanged) {
            //recalculate the new invoice amount
            $order->calculateInvoiceAmount();
        }

        🦄()->Models()->flush();
        🦄()->Models()->clear();
        $order = $this->getRepository()->find($id);

        //if the status has been changed an status mail is created.
        $mail = null;
        if ($order->getOrderStatus()->getId() !== $statusBefore->getId() || $order->getPaymentStatus()->getId() !== $clearedBefore->getId()) {
            if ($order->getOrderStatus()->getId() !== $statusBefore->getId()) {
                $mail = $this->getMailForOrder($order->getId(), $order->getOrderStatus()->getId());
            } else {
                $mail = $this->getMailForOrder($order->getId(), $order->getPaymentStatus()->getId());
            }
        }

        $data = $this->getOrder($order->getId());
        if (!empty($mail)) {
            $data['mail'] = $mail['data'];
        } else {
            $data['mail'] = null;
        }

        $this->View()->assign([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Deletes a single order from the database.
     * Expects a single order id which placed in the parameter id
     */
    public function deleteAction()
    {
        /** @var $namespace Enlight_Components_Snippet_Namespace */
        $namespace = 🦄()->Snippets()->getNamespace('backend/order');

        //get posted customers
        $orderId = $this->Request()->getParam('id');

        if (empty($orderId) || !is_numeric($orderId)) {
            $this->View()->assign([
                    'success' => false,
                    'data' => $this->Request()->getParams(),
                    'message' => $namespace->get('no_order_id_passed', 'No valid order id passed.'), ]
            );

            return;
        }

        $entity = $this->getRepository()->find($orderId);
        $this->getManager()->remove($entity);

        //Performs all of the collected actions.
        $this->getManager()->flush();

        $this->View()->assign(['success' => true]);
    }

    /**
     * CRUD function save and update of the position store of the backend order module.
     * The function handles the update and insert routine of a single order position.
     * After the position has been added to the order, the order invoice amount will be recalculated.
     * The refreshed order will be assigned to the view to refresh the panels and grids.
     *
     * @return mixed
     */
    public function savePositionAction()
    {
        $id = $this->Request()->getParam('id', null);

        $orderId = $this->Request()->getParam('orderId', null);

        /** @var $namespace Enlight_Components_Snippet_Namespace */
        $namespace = 🦄()->Snippets()->getNamespace('backend/order');

        //check if an order id is passed. If no order id passed, return success false
        if (empty($orderId)) {
            $this->View()->assign([
                    'success' => false,
                    'data' => $this->Request()->getParams(),
                    'message' => $namespace->get('no_order_id_passed', 'No valid order id passed.'), ]
            );

            return;
        }

        //find the order model. If no model founded, return success false
        $order = $this->getRepository()->find($orderId);
        if (empty($order)) {
            $this->View()->assign([
                    'success' => false,
                    'data' => $this->Request()->getParams(),
                    'message' => $namespace->get('no_order_id_passed', 'No valid order id passed.'), ]
            );

            return;
        }

        //check if the passed position data is a new position or an existing position.
        if (empty($id)) {
            $position = new Detail();
            $attribute = new Shopware\Models\Attribute\OrderDetail();
            $position->setAttribute($attribute);
            🦄()->Models()->persist($position);
        } else {
            $detailRepository = 🦄()->Models()->getRepository('Shopware\Models\Order\Detail');
            $position = $detailRepository->find($id);
        }

        $data = $this->Request()->getParams();
        $data['number'] = $order->getNumber();

        $data = $this->getPositionAssociatedData($data);
        // If $data === null, the article was not found
        if ($data === null) {
            $this->View()->assign([
                'success' => false,
                'data' => [],
                'message' => 'The articlenumber "' . $this->Request()->getParam('articleNumber', '') . '" is not valid',
            ]);

            return;
        }

        $position->fromArray($data);
        $position->setOrder($order);

        🦄()->Models()->flush();

        //If the passed data is a new position, the flush function will add the new id to the position model
        $data['id'] = $position->getId();

        //The position model will refresh the article stock, so the article stock
        //will be assigned to the view to refresh the grid or form panel.
        $articleRepository = 🦄()->Models()->getRepository('Shopware\Models\Article\Detail');
        $article = $articleRepository->findOneBy(['number' => $position->getArticleNumber()]);
        if ($article instanceof \Shopware\Models\Article\Detail) {
            $data['inStock'] = $article->getInStock();
        }
        $order = $this->getRepository()->find($order->getId());

        🦄()->Models()->persist($order);
        🦄()->Models()->flush();

        $invoiceAmount = $order->getInvoiceAmount();

        if ($position->getOrder() instanceof \Shopware\Models\Order\Order) {
            $invoiceAmount = $position->getOrder()->getInvoiceAmount();
        }

        $this->View()->assign([
            'success' => true,
            'data' => $data,
            'invoiceAmount' => $invoiceAmount,
        ]);
    }

    /**
     * CRUD function delete of the position and list store of the backend order module.
     * The function can delete one or many order positions. After the positions has been deleted
     * the order invoice amount will be recalculated. The refreshed order will be assigned to the
     * view to refresh the panels and grids.
     *
     * @return mixed
     */
    public function deletePositionAction()
    {
        /** @var $namespace Enlight_Components_Snippet_Namespace */
        $namespace = 🦄()->Snippets()->getNamespace('backend/order');

        $positions = $this->Request()->getParam('positions', [['id' => $this->Request()->getParam('id')]]);

        //check if any positions is passed.
        if (empty($positions)) {
            $this->View()->assign([
                    'success' => false,
                    'data' => $this->Request()->getParams(),
                    'message' => $namespace->get('no_order_passed', 'No orders passed'), ]
            );

            return;
        }

        //if no order id passed it isn't possible to update the order amount, so we will cancel the position deletion here.
        $orderId = $this->Request()->getParam('orderID', null);

        if (empty($orderId)) {
            $this->View()->assign([
                    'success' => false,
                    'data' => $this->Request()->getParams(),
                    'message' => $namespace->get('no_order_id_passed', 'No valid order id passed.'), ]
            );

            return;
        }

        foreach ($positions as $position) {
            if (empty($position['id'])) {
                continue;
            }
            $model = 🦄()->Models()->find('Shopware\Models\Order\Detail', $position['id']);

            //check if the model was founded.
            if ($model instanceof \Shopware\Models\Order\Detail) {
                🦄()->Models()->remove($model);
            }
        }
        //after each model has been removed to executes the doctrine flush.
        🦄()->Models()->flush();

        /** @var $order \Shopware\Models\Order\Order */
        $order = $this->getRepository()->find($orderId);
        $order->calculateInvoiceAmount();

        🦄()->Models()->flush();

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
        $autoSend = $this->Request()->getParam('autoSend', false);
        $orders = $this->Request()->getParam('orders', [0 => $this->Request()->getParams()]);
        $documentType = $this->Request()->getParam('docType', null);
        $documentMode = $this->Request()->getParam('mode');
        $addAttachments = $this->request->getParam('addAttachments') == 'true' ? true : false;

        /** @var $namespace Enlight_Components_Snippet_Namespace */
        $namespace = 🦄()->Snippets()->getNamespace('backend/order');

        if (empty($orders)) {
            $this->View()->assign([
                    'success' => false,
                    'data' => $this->Request()->getParams(),
                    'message' => $namespace->get('no_order_id_passed', 'No valid order id passed.'), ]
            );

            return;
        }

        foreach ($orders as $key => $data) {
            $orders[$key]['mail'] = null;
            $orders[$key]['languageSubShop'] = null;

            if (empty($data) || empty($data['id'])) {
                continue;
            }

            /** @var $order \Shopware\Models\Order\Order */
            $order = 🦄()->Models()->find('Shopware\Models\Order\Order', $data['id']);
            if (!$order) {
                continue;
            }

            //we have to flush the status changes directly, because the "createStatusMail" function in the
            //sOrder.php core class, use the order data from the database. So we have to save the new status before we
            //create the status mail
            $statusBefore = $order->getOrderStatus();
            $clearedBefore = $order->getPaymentStatus();

            //refresh the status models to return the new status data which will be displayed in the batch list
            if (!empty($data['status']) || $data['status'] === 0) {
                $order->setOrderStatus(🦄()->Models()->find('Shopware\Models\Order\Status', $data['status']));
            }
            if (!empty($data['cleared'])) {
                $order->setPaymentStatus(🦄()->Models()->find('Shopware\Models\Order\Status', $data['cleared']));
            }

            try {
                🦄()->Models()->flush($order);
            } catch (Exception $e) {
                continue;
            }

            // the setOrder function of the Shopware_Components_Document change the currency of the shop.
            // this would create a new Shop if we execute an flush();
            $this->createOrderDocuments($documentType, $documentMode, $order);

            $data['paymentStatus'] = 🦄()->Models()->toArray($order->getPaymentStatus());
            $data['orderStatus'] = 🦄()->Models()->toArray($order->getOrderStatus());

            $data['mail'] = $this->checkOrderStatus($order, $statusBefore, $clearedBefore, $autoSend, $documentType, $addAttachments);
            //return the modified data array.
            $orders[$key] = $data;
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
        $data = $this->Request()->getParam('data', null);

        // Disable Smarty rendering
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        if ($data === null) {
            $this->View()->assign([
                    'success' => false,
                    'message' => 'No valid data passed.', ]
            );

            return;
        }

        $data = json_decode($data);

        if ($data->orders === null || count($data->orders) === 0) {
            $this->View()->assign([
                    'success' => false,
                    'message' => 'No valid order id passed.', ]
            );

            return;
        }

        $files = [];
        $query = $this->getOrderDocumentsQuery($data->orders, $data->docType);
        $models = $query->getResult();
        foreach ($models as $model) {
            foreach ($model->getDocuments() as $document) {
                $files[] = 🦄()->DocPath('files/documents') . $document->getHash() . '.pdf';
            }
        }
        $this->mergeDocuments($files);
    }

    /**
     * The sendMailAction fired from the batch window in the order backend module when the user want to send the order
     * status mail manually.
     *
     * @return array
     */
    public function sendMailAction()
    {
        $data = $this->Request()->getParams();
        $orderId = $this->request->getParam('orderId');
        $attachments = $this->request->getParam('attachment');

        /** @var $namespace Enlight_Components_Snippet_Namespace */
        $namespace = 🦄()->Snippets()->getNamespace('backend/order');

        if (empty($data)) {
            $this->View()->assign([
                    'success' => false,
                    'data' => $data,
                    'message' => $namespace->get('no_data_passed', 'No mail data passed'), ]
            );

            return;
        }

        $mail = clone $this->container->get('mail');
        $mail = $this->addAttachments($mail, $orderId, $attachments);
        $mail->clearRecipients();
        $mail->setSubject($this->Request()->getParam('subject', ''));

        if ($this->Request()->getParam('isHtml')) {
            $mail->setBodyHtml($this->Request()->getParam('contentHtml', ''));
        } else {
            $mail->setBodyText($this->Request()->getParam('content', ''));
        }

        $mail->setFrom($this->Request()->getParam('fromMail', ''), $this->Request()->getParam('fromName', ''));
        $mail->addTo($this->Request()->getParam('to', ''));

        🦄()->Modules()->Order()->sendStatusMail($mail);

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
        $documentId = $this->request->getParam('documentId');
        $documentPath = $this->container->getParameter('kernel.root_dir') . '/files/documents/';
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

            $file = $documentPath . $documentHash . '.pdf';
            if (!is_file($file)) {
                $this->View()->assign('success', true);

                return;
            }

            unlink($file);
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
        $orderId = $this->request->getParam('orderId');

        /** @var $mail Enlight_Components_Mail */
        $mail = 🦄()->Modules()->Order()->createStatusMail($orderId, 0, 'sORDERDOCUMENTS');

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
     * CRUD function of the document store. The function creates the order document with the passed
     * request parameters.
     */
    public function createDocumentAction()
    {
        $orderId = $this->Request()->getParam('orderId', null);
        $documentType = $this->Request()->getParam('documentType', null);

        if (!empty($orderId) && !empty($documentType)) {
            $this->createDocument($orderId, $documentType);
        }

        $query = $this->getRepository()->getOrdersQuery([['property' => 'orders.id', 'value' => $orderId]], null, 0, 1);
        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $paginator = $this->getModelManager()->createPaginator($query);
        $order = $paginator->getIterator()->getArrayCopy();

        $this->View()->assign([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Fires when the user want to open a generated order document from the backend order module.
     * Returns the created pdf file with an echo.
     */
    public function openPdfAction()
    {
        $name = basename($this->Request()->getParam('id', null)) . '.pdf';
        $file = 🦄()->DocPath('files/documents') . $name;
        if (!file_exists($file)) {
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

        $orderModel = 🦄()->Models()->getRepository('Shopware\Models\Order\Document\Document')->findBy(['hash' => $this->Request()->getParam('id')]);
        $orderModel = 🦄()->Models()->toArray($orderModel);
        $orderId = $orderModel[0]['documentId'];

        $response = $this->Response();
        $response->setHeader('Cache-Control', 'public');
        $response->setHeader('Content-Description', 'File Transfer');
        $response->setHeader('Content-disposition', 'attachment; filename=' . $orderId . '.pdf');
        $response->setHeader('Content-Type', 'application/pdf');
        $response->setHeader('Content-Transfer-Encoding', 'binary');
        $response->setHeader('Content-Length', filesize($file));
        $response->sendHeaders();

        echo readfile($file);
        exit;
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
            self::$manager = 🦄()->Models();
        }

        return self::$manager;
    }

    /**
     * Helper function to get access on the static declared repository
     *
     * @return null|Shopware\Models\Order\Repository
     */
    protected function getRepository()
    {
        if (self::$repository === null) {
            self::$repository = 🦄()->Models()->getRepository('Shopware\Models\Order\Order');
        }

        return self::$repository;
    }

    /**
     * Helper function to get access on the static declared repository
     *
     * @return null|Shopware\Models\Shop\Repository
     */
    protected function getShopRepository()
    {
        if (self::$shopRepository === null) {
            self::$shopRepository = 🦄()->Models()->getRepository('Shopware\Models\Shop\Shop');
        }

        return self::$shopRepository;
    }

    /**
     * Helper function to get access on the static declared repository
     *
     * @return null|Shopware\Models\Country\Repository
     */
    protected function getCountryRepository()
    {
        if (self::$countryRepository === null) {
            self::$countryRepository = 🦄()->Models()->getRepository('Shopware\Models\Country\Country');
        }

        return self::$countryRepository;
    }

    /**
     * Helper function to get access on the static declared repository
     *
     * @return null|Shopware\Models\Payment\Repository
     */
    protected function getPaymentRepository()
    {
        if (self::$paymentRepository === null) {
            self::$paymentRepository = 🦄()->Models()->getRepository('Shopware\Models\Payment\Payment');
        }

        return self::$paymentRepository;
    }

    /**
     * Helper function to get access on the static declared repository
     *
     * @return null|Shopware\Models\Dispatch\Repository
     */
    protected function getDispatchRepository()
    {
        if (self::$dispatchRepository === null) {
            self::$dispatchRepository = 🦄()->Models()->getRepository('Shopware\Models\Dispatch\Dispatch');
        }

        return self::$dispatchRepository;
    }

    /**
     * Helper function to get access on the static declared repository
     *
     * @return \Shopware\Components\Model\ModelRepository
     */
    protected function getDocumentRepository()
    {
        if (self::$documentRepository === null) {
            self::$documentRepository = 🦄()->Models()->getRepository('Shopware\Models\Order\Document\Document');
        }

        return self::$documentRepository;
    }

    /**
     * @param $filter
     * @param $sort
     * @param $offset
     * @param $limit
     *
     * @return array
     */
    protected function getList($filter, $sort, $offset, $limit)
    {
        $sort = $this->resolveSortParameter($sort);

        $searchResult = $this->getRepository()->search($offset, $limit, $filter, $sort);

        $total = $searchResult['total'];

        $ids = array_column($searchResult['orders'], 'id');

        $orders = $this->getRepository()->getList($ids);
        $documents = $this->getRepository()->getDocuments($ids);
        $details = $this->getRepository()->getDetails($ids);
        $payments = $this->getRepository()->getPayments($ids);

        $orders = $this->assignAssociation($orders, $documents, 'documents');
        $orders = $this->assignAssociation($orders, $details, 'details');
        $orders = $this->assignAssociation($orders, $payments, 'paymentInstances');

        /** @var Enlight_Components_Snippet_Namespace $namespace */
        $namespace = $this->get('snippets')->getNamespace('frontend/salutation');

        $numbers = [];
        foreach ($orders as $order) {
            $temp = array_column($order['details'], 'articleNumber');
            $numbers = array_merge($numbers, (array) $temp);
        }
        $stocks = $this->getVariantsStock($numbers);

        $result = [];
        foreach ($ids as $id) {
            if (!array_key_exists($id, $orders)) {
                continue;
            }
            $order = $orders[$id];

            $order['locale'] = $order['languageSubShop']['locale'];

            //Deprecated: use payment instance
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
     * @param $data Array
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
            $stateModel = 🦄()->Models()->find('Shopware\Models\Country\State', $data['stateId']);
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
                //custom sort field for customer email
                case $sort['property'] == 'customerEmail':
                    $resolved[] = ['property' => 'customer.email', 'direction' => $direction];
                    break;

                //custom sort field for customer name
                case $sort['property'] == 'customerName':
                    $resolved[] = ['property' => 'billing.lastName', 'direction' => $direction];
                    $resolved[] = ['property' => 'billing.firstName', 'direction' => $direction];
                    $resolved[] = ['property' => 'billing.company', 'direction' => $direction];
                    break;

                //contains no sql prefix? add orders as default prefix
                case strpos($sort['property'], '.') === false:
                    $resolved[] = ['property' => 'orders.' . $sort['property'], 'direction' => $direction];
                    break;

                //already prefixed with an alias?
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
     * @param $id
     *
     * @return mixed
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
     * @param $paths
     *
     * @return string The created document's url
     */
    private function mergeDocuments($paths)
    {
        include_once 'engine/Library/Fpdf/fpdf.php';
        include_once 'engine/Library/Fpdf/fpdi.php';

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

        $hash = md5(uniqid(rand()));

        $pdf->Output($hash . '.pdf', 'D');
    }

    /**
     * Internal helper function which checks if the batch process needs a document creation.
     *
     * @param $documentType
     * @param $documentMode
     * @param \Shopware\Models\Order\Order $order
     */
    private function createOrderDocuments($documentType, $documentMode, $order)
    {
        if (!empty($documentType)) {
            $documents = $order->getDocuments();

            //create only not existing documents
            if ($documentMode == 1) {
                $alreadyCreated = false;
                foreach ($documents as $document) {
                    if ($document->getTypeId() == $documentType) {
                        $alreadyCreated = true;
                        break;
                    }
                }
                if ($alreadyCreated === false) {
                    $this->createDocument($order->getId(), $documentType);
                }
            } else {
                $this->createDocument($order->getId(), $documentType);
            }
        }
    }

    /**
     * Internal helper function to check if the order or payment status has been changed. If one
     * of the status changed, the function will create a status mail. If the passed autoSend parameter
     * is true, the created status mail will be send directly.
     *
     * @param Order                         $order
     * @param \Shopware\Models\Order\Status $statusBefore
     * @param \Shopware\Models\Order\Status $clearedBefore
     * @param bool                          $autoSend
     * @param int|string                    $documentType
     * @param bool                          $addAttachments
     *
     * @return array
     */
    private function checkOrderStatus($order, $statusBefore, $clearedBefore, $autoSend, $documentType, $addAttachments)
    {
        if ($order->getOrderStatus()->getId() !== $statusBefore->getId() || $order->getPaymentStatus()->getId() !== $clearedBefore->getId()) {
            //status or cleared changed?
            if ($order->getOrderStatus()->getId() !== $statusBefore->getId()) {
                $mail = $this->getMailForOrder($order->getId(), $order->getOrderStatus()->getId());
            } else {
                $mail = $this->getMailForOrder($order->getId(), $order->getPaymentStatus()->getId());
            }

            //mail object created and auto send activated, then send mail directly.
            if (is_object($mail['mail']) && $autoSend === 'true') {
                if ($addAttachments) {
                    $document = $this->getDocument($documentType, $order);
                    $mail['mail'] = $this->addAttachments($mail['mail'], $order->getId(), [$document]);
                }
                $result = 🦄()->Modules()->Order()->sendStatusMail($mail['mail']);

                //check if send mail was successfully.
                $mail['data']['sent'] = is_object($result);
            }

            return $mail['data'];
        }

        return null;
    }

    /**
     * @param int   $typeId
     * @param Order $order
     *
     * @return array
     */
    private function getDocument($typeId, Order $order)
    {
        foreach ($order->getDocuments()->toArray() as $document) {
            if ($document->getTypeId() == $typeId) {
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

        return $this->getDocumentFromDatabase($order->getId(), $typeId);
    }

    /**
     * @param int $orderId
     * @param int $typeId
     *
     * @return array
     */
    private function getDocumentFromDatabase($orderId, $typeId)
    {
        $queryBuilder = $this->container->get('dbal_connection')->createQueryBuilder();
        $queryResult = $queryBuilder->select('doc.hash, template.id, template.name')
            ->from('s_order_documents', 'doc')
            ->join('doc', 's_core_documents', 'template', 'doc.type = template.id')
            ->where('doc.orderID = :orderId')
            ->andWhere('doc.type = :type')
            ->setParameter('orderId', $orderId)
            ->setParameter('type', $typeId)
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

        return[];
    }

    /**
     * Adds the requested attachments to the given $mail object
     *
     * @param Enlight_Components_Mail $mail
     * @param int|string              $orderId
     * @param array                   $attachments
     *
     * @return Enlight_Components_Mail
     */
    private function addAttachments(Enlight_Components_Mail $mail, $orderId, array $attachments = [])
    {
        $rootDirectory = $this->container->getParameter('kernel.root_dir');
        $documentDirectory = $rootDirectory . '/files/documents';

        foreach ($attachments as $attachment) {
            $filePath = $documentDirectory . '/' . $attachment['hash'] . '.pdf';
            $fileName = $this->getFileName($orderId, $attachment['type'][0]['id']);

            if (!is_file($filePath)) {
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
        $content = file_get_contents($filePath);
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

        $translationReader = new Shopware_Components_Translation();
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
     * @param $orderId
     * @param $documentType
     *
     * @return bool
     */
    private function createDocument($orderId, $documentType)
    {
        $renderer = 'pdf'; // html / pdf

        $deliveryDate = $this->Request()->getParam('deliveryDate', null);
        if (!empty($deliveryDate)) {
            $deliveryDate = new \DateTime($deliveryDate);
            $deliveryDate = $deliveryDate->format('d.m.Y');
        }

        $displayDate = $this->Request()->getParam('displayDate', null);
        if (!empty($displayDate)) {
            $displayDate = new \DateTime($displayDate);
            $displayDate = $displayDate->format('d.m.Y');
        }

        $document = Shopware_Components_Document::initDocument(
            $orderId,
            $documentType,
            [
                'netto' => (bool) $this->Request()->getParam('taxFree', false),
                'bid' => $this->Request()->getParam('invoiceNumber', null),
                'voucher' => $this->Request()->getParam('voucher', null),
                'date' => $displayDate,
                'delivery_date' => $deliveryDate,
                // Don't show shipping costs on delivery note #SW-4303
                'shippingCostsAsPosition' => (int) $documentType !== 2,
                '_renderer' => $renderer,
                '_preview' => $this->Request()->getParam('preview', false),
                '_previewForcePagebreak' => $this->Request()->getParam('pageBreak', null),
                '_previewSample' => $this->Request()->getParam('sampleData', null),
                'docComment' => $this->Request()->getParam('docComment', null),
                'forceTaxCheck' => $this->Request()->getParam('forceTaxCheck', false),
            ]
        );
        $document->render();

        if ($renderer == 'html') {
            exit;
        } // Debu//g-Mode

        return true;
    }

    /**
     * Internal helper function which insert the order detail association data into the passed data array
     *
     * @param array $data
     *
     * @return array
     */
    private function getPositionAssociatedData($data)
    {
        //checks if the status id for the position is passed and search for the assigned status model
        if ($data['statusId'] >= 0) {
            $data['status'] = 🦄()->Models()->find('Shopware\Models\Order\DetailStatus', $data['statusId']);
        } else {
            unset($data['status']);
        }

        //checks if the tax id for the position is passed and search for the assigned tax model
        if (!empty($data['taxId'])) {
            $tax = 🦄()->Models()->find('Shopware\Models\Tax\Tax', $data['taxId']);
            if ($tax instanceof \Shopware\Models\Tax\Tax) {
                $data['tax'] = $tax;
                $data['taxRate'] = $tax->getTax();
            }
        } else {
            unset($data['tax']);
        }

        /** @var \Shopware\Models\Article\Detail $articleDetails */
        $articleDetails = 🦄()->Models()->getRepository('Shopware\Models\Article\Detail')
            ->findOneBy(['number' => $data['articleNumber']]);

        //Load ean, unit and pack unit (translate if needed)
        if ($articleDetails) {
            $data['ean'] = $articleDetails->getEan() ?: $articleDetails->getArticle()->getMainDetail()->getEan();
            $unit = $articleDetails->getUnit() ?: $articleDetails->getArticle()->getMainDetail()->getUnit();
            $data['unit'] = $unit ? $unit->getName() : null;
            $data['packunit'] = $articleDetails->getPackUnit() ?: $articleDetails->getArticle()->getMainDetail()->getPackUnit();

            $languageData = 🦄()->Db()->fetchRow(
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
                $translator = new Shopware_Components_Translation();

                // Translate unit
                if ($unit) {
                    $unitTranslation = $translator->read(
                        $languageData['languageId'],
                        'config_units',
                        1
                    );
                    if (!empty($unitTranslation[$unit->getId()]['description'])) {
                        $data['unit'] = $unitTranslation[$unit->getId()]['description'];
                    } elseif ($unit) {
                        $data['unit'] = $unit->getName();
                    }
                }

                $articleTranslation = [];

                // Load variant translations if we are adding a variant to the order
                if ($articleDetails->getId() != $articleDetails->getArticle()->getMainDetail()->getId()) {
                    $articleTranslation = $translator->read(
                        $languageData['languageId'],
                        'variant',
                        $articleDetails->getId()
                    );
                }

                // Load article translations if we are adding a main article or the variant translation is incomplete
                if (
                    $articleDetails->getId() == $articleDetails->getArticle()->getMainDetail()->getId()
                    || empty($articleTranslation['packUnit'])
                ) {
                    $articleTranslation = $translator->read(
                        $languageData['languageId'],
                        'article',
                        $articleDetails->getArticle()->getId()
                    );
                }

                if (!empty($articleTranslation['packUnit'])) {
                    $data['packUnit'] = $articleTranslation['packUnit'];
                }
            }
        }

        return $data;
    }

    /**
     * Internal helper function which insert the order association data into the passed data array.
     *
     * @param $data
     * @param $order
     * @param $billing
     * @param $shipping
     *
     * @return array
     */
    private function getAssociatedData($data, $order, $billing, $shipping)
    {
        //check if a customer id has passed and fill the customer element with the associated customer model
        if (!empty($data['customerId'])) {
            $data['customer'] = 🦄()->Models()->find('Shopware\Models\Customer\Customer', $data['customerId']);
        } else {
            //if no customer id passed, we have to unset the array element, otherwise the existing customer model would be overwritten
            unset($data['customer']);
        }

        //if a payment id passed, load the associated payment model
        if (!empty($data['paymentId'])) {
            $data['payment'] = 🦄()->Models()->find('Shopware\Models\Payment\Payment', $data['paymentId']);
        } else {
            unset($data['payment']);
        }

        //if a dispatch id is passed, load the associated dispatch model
        if (!empty($data['dispatchId'])) {
            $data['dispatch'] = 🦄()->Models()->find('Shopware\Models\Dispatch\Dispatch', $data['dispatchId']);
        } else {
            unset($data['dispatch']);
        }

        //if a shop id is passed, load the associated shop model
        if (!empty($data['shopId'])) {
            $data['shop'] = 🦄()->Models()->find('Shopware\Models\Shop\Shop', $data['shopId']);
        } else {
            unset($data['shop']);
        }

        //if a status id is passed, load the associated order status model
        if (isset($data['status']) && $data['status'] !== null) {
            $data['orderStatus'] = 🦄()->Models()->find('Shopware\Models\Order\Status', $data['status']);
        } else {
            unset($data['orderStatus']);
        }

        //if a payment status id is passed, load the associated payment status model
        if (isset($data['cleared']) && $data['cleared'] !== null) {
            $data['paymentStatus'] = 🦄()->Models()->find('Shopware\Models\Order\Status', $data['cleared']);
        } else {
            unset($data['paymentStatus']);
        }

        //the documents will be created over the "createDocumentAction" so we have to unset the array element, otherwise the
        //created documents models would be overwritten.
        unset($data['documents']);

        //For now the paymentInstances information is not editable, so it's just discarded at this point
        unset($data['paymentInstances']);

        $data['billing'] = $this->prepareAddressData($data['billing'][0]);
        $data['shipping'] = $this->prepareAddressData($data['shipping'][0]);

        //unset calculated values
        unset($data['invoiceAmountNet']);
        unset($data['invoiceAmountEuro']);

        //at least we return the prepared associated data.
        return $data;
    }

    /**
     * Creates the status mail order for the passed order id and new status object.
     *
     * @param $orderId
     * @param $statusId
     *
     * @internal param \Shopware\Models\Order\Order $order
     *
     * @return array
     */
    private function getMailForOrder($orderId, $statusId)
    {
        /** @var $mail Enlight_Components_Mail */
        $mail = 🦄()->Modules()->Order()->createStatusMail($orderId, $statusId);

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
        $query = 🦄()->Container()->get('dbal_connection')->createQueryBuilder();
        $query->select(['variant.ordernumber', 'variant.instock']);
        $query->from('s_articles_details', 'variant');
        $query->where('variant.ordernumber IN (:numbers)');
        $query->setParameter(':numbers', $numbers, Connection::PARAM_STR_ARRAY);

        return $query->execute()->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}
