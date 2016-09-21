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

use Shopware\Models\Order\Order;

/**
 * Shopware Backend Controller
 * Backend for various ajax queries
 */
class Shopware_Controllers_Backend_CanceledOrder extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Method to define acl dependencies in backend controllers
     * <code>
     * $this->addAclPermission("name_of_action_with_action_prefix","name_of_assigned_privilege","optionally error message");
     * // $this->addAclPermission("indexAction","read","Ops. You have no permission to view that...");
     * </code>
     */
    protected function initAcl()
    {
        // read
        $this->addAclPermission('getStatistics', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getArticle', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getBasket', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getOrder', 'read', 'Insufficient Permissions');

        //delete
        $this->addAclPermission('deleteOrder', 'delete', 'Insufficient Permissions');
    }


    public function convertOrderAction()
    {
        if (!($orderId = $this->Request()->getParam('orderId'))) {
            $this->View()->assign([
                'success' => false,
                'message' => $this->translateMessage('errorMessage/noOrderId', 'No orderId passed.')
            ]);
            return;
        }

        // Get user, shipping and billing
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('orders', 'customer', 'billing', 'payment', 'shipping'))
            ->from('Shopware\Models\Order\Order', 'orders')
            ->leftJoin('orders.customer', 'customer')
            ->leftJoin('orders.payment', 'payment')
            ->leftJoin('customer.billing', 'billing')
            ->leftJoin('customer.shipping', 'shipping')
            ->where("orders.id = ?1")
            ->setParameter(1, $orderId);

        $result = $builder->getQuery()->getArrayResult();

        // Check requiered fields
        if (empty($result) || $result[0]['customer'] === null || $result[0]['customer']['billing'] === null) {
            $this->View()->assign([
                'success' => false,
                'message' => $this->translateMessage('errorMessage/noCustomerData', 'Could not get required customer data.')
            ]);
            return;
        }

        // Get ordernumber
        $numberRepository = Shopware()->Models()->getRepository('Shopware\Models\Order\Number');
        $numberModel = $numberRepository->findOneBy(array('name' => 'invoice'));
        if ($numberModel === null) {
            $this->View()->assign([
                'success' => false,
                'message' => $this->translateMessage('errorMessage/noOrdernumber', 'Could not get ordernumber.')
            ]);
            return;
        }
        $newOrderNumber = $numberModel->getNumber() + 1;

        // Set new ordernumber
        $numberModel->setNumber($newOrderNumber);

        // set new ordernumber to the order
        $orderModel = Shopware()->Models()->find('Shopware\Models\Order\Order', $orderId);
        $orderModel->setNumber($newOrderNumber);

        // refreshes the in stock correctly for this order if the user confirmed it
        if ((bool) $this->Request()->getParam('refreshInStock')) {
            $outOfStock = $this->getOutOfStockProducts($orderModel);

            if (!empty($outOfStock)) {
                $numbers = array_map(function (\Shopware\Models\Article\Detail $variant) {
                    return $variant->getNumber();
                }, $outOfStock);

                $this->View()->assign([
                    'success' => false,
                    'message' => $this->translateMessage('errorMessage/notEnoughStock', "The following products haven't enough stock") . implode(', ', $numbers)
                ]);
                return;
            }

            $this->convertCancelledOrderInStock($orderModel);
        }

        // If there is no shipping address, set billing address to be the shipping address
        if ($result[0]['customer']['shipping'] === null) {
            $result[0]['customer']['shipping'] = $result[0]['customer']['billing'];
        }

        // Create new entry in s_order_billingaddress
        $billingModel = new Shopware\Models\Order\Billing();
        $billingModel->fromArray($result[0]['customer']['billing']);
        $billingModel->setCountry(Shopware()->Models()->find('Shopware\Models\Country\Country', $result[0]['customer']['billing']['countryId']));
        $billingModel->setCustomer(Shopware()->Models()->find('Shopware\Models\Customer\Customer', $result[0]['customer']['billing']['customerId']));
        $billingModel->setOrder($orderModel);
        Shopware()->Models()->persist($billingModel);

        // Create new entry in s_order_shippingaddress
        $shippingModel = new Shopware\Models\Order\Shipping();
        $shippingModel->fromArray($result[0]['customer']['shipping']);
        $shippingModel->setCountry(Shopware()->Models()->find('Shopware\Models\Country\Country', $result[0]['customer']['shipping']['countryId']));
        $shippingModel->setCustomer(Shopware()->Models()->find('Shopware\Models\Customer\Customer', $result[0]['customer']['shipping']['customerId']));
        $shippingModel->setOrder($orderModel);
        Shopware()->Models()->persist($shippingModel);


        // Finally set the order to be a regular order
        $statusModel = Shopware()->Models()->find('Shopware\Models\Order\Status', 1);
        $orderModel->setOrderStatus($statusModel);

        Shopware()->Models()->flush();

        $this->View()->assign(['success' => true]);
    }

    /**
     * Get last viewports/exit pages. This way you can determine, where the customers do have
     * problems with the shop system.
     */
    public function getViewportsAction()
    {
        $startDate = $this->Request()->getParam('fromDate', date("Y-m-d", mktime(0, 0, 0, 1, 1, date("Y"))));
        $endDate   = $this->Request()->getParam('toDate', date("Y-m-d"));
        $filter = $this->Request()->getParam('filter', null);
        $sort = $this->Request()->getParam('sort', null);

        $params = array(
            'endDate'   => $endDate,
            'startDate' => $startDate,
        );

        $sql = "SELECT id
            FROM s_order_basket
            WHERE modus = 0
            AND datum >= :startDate AND datum <= DATE_ADD(:endDate,INTERVAL 1 DAY)
            GROUP BY sessionID";
        $result = Shopware()->Db()->query($sql, $params);
        $total = $result->rowCount();

        if (is_array($filter) && isset($filter[0]['value'])) {
            $params['filter'] = '%' . $filter[0]['value'] . '%';
            $filter = 'AND lastviewport LIKE :filter';
        } else {
            $filter = '';
        }

        if ($sort !== null && isset($sort[0]['property'])) {
            if (isset($sort['0']['direction']) && $sort['0']['direction'] === 'DESC') {
                $direction = 'DESC';
            } else {
                $direction = 'ASC';
            }

            switch ($sort[0]['property']) {
                case 'number':
                    $sort = 'number';
                    break;
                case 'percent':
                    $sort = 'number';
                    break;
                case 'name':
                    $sort = 'name';
                    break;
                default:
                    $sort = '';
                    break;
            }

            $sort = "ORDER BY $sort $direction";
        } else {
            $sort = '';
        }


        $sql = "
            SELECT  lastviewport as name, COUNT(lastviewport) as number
            FROM
            (
                SELECT lastviewport, sessionID
                FROM s_order_basket
                WHERE modus = 0
                AND datum >= :startDate AND datum <= DATE_ADD(:endDate,INTERVAL 1 DAY)
                $filter
                GROUP BY sessionID
            ) as b1
            GROUP BY b1.lastviewport
            $sort";

        $data = Shopware()->Db()->fetchAll($sql, $params);

        // Insert the percentage into each field manually
        if ($data !== null && isset($total)) {
            for ($i = 0; $i < count($data); $i++) {
                if ($total != 0) {
                    $data[$i]['percent'] = round($data[$i]['number'] / $total * 100, 1);
                } else {
                    $data[$i]['percent'] = 0;
                }
            }
        }

        $this->View()->assign([
            'success' => true,
            'data' => $data,
            'total' => count($data),
        ]);
    }

    /**
     * Get available vouchers for a customer who canceled his order.
     * */
    public function getVoucherAction()
    {
        $orderId = $this->Request()->getParam('id', null);

        $sql = "SELECT s_emarketing_vouchers.id, s_emarketing_vouchers.description, s_emarketing_vouchers.value
            FROM s_emarketing_vouchers
            WHERE  s_emarketing_vouchers.modus = 1 AND (s_emarketing_vouchers.valid_to >= CURDATE() OR s_emarketing_vouchers.valid_to is NULL)
            AND (s_emarketing_vouchers.valid_from <= CURDATE() OR s_emarketing_vouchers.valid_from is NULL)
            AND (
                SELECT s_emarketing_voucher_codes.id
                FROM s_emarketing_voucher_codes
                WHERE s_emarketing_voucher_codes.voucherID = s_emarketing_vouchers.id
                AND s_emarketing_voucher_codes.userID is NULL
                AND s_emarketing_voucher_codes.cashed = 0
                LIMIT 1
            )";

        $data = Shopware()->Db()->fetchAll($sql);

        $this->View()->assign([
            'success' => true,
            'data' => $data,
            'total' => count($data),
        ]);
    }

    /**
     * Read free codes from the database. If no free codes are available, null will be returned
     * @param $voucherId
     * @return array|null
     */
    private function getFreeVoucherCode($voucherId)
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('voucherCodes.id', 'voucherCodes.code'))
                ->from('Shopware\Models\Voucher\Voucher', 'voucher')
                ->leftJoin('voucher.codes', 'voucherCodes')
                ->where('voucher.modus = ?1')
                ->andWhere('voucher.id = :voucherId')
                ->andWhere('voucher.validTo >= CURRENT_DATE() OR voucher.validTo is NULL')
                ->andWhere('voucherCodes.customerId is NULL')
                ->andWhere('voucherCodes.cashed = 0')
                ->setParameter(1, 1)
                ->setParameter('voucherId', $voucherId)
                ->setMaxResults(1);
        $query = $builder->getQuery();
        $total = Shopware()->Models()->getQueryCount($query);
        if ($total === 0) {
            return null;
        }

        return $query->getArrayResult();
    }

    /**
     * Sends a CanceledQuestion Mail to a given mail-adress
     */
    public function sendCanceledQuestionMailAction()
    {
        if (!($mailTo = $this->Request()->getParam('mail'))) {
            $this->View()->assign([
                'success' => false,
                'message' => $this->translateMessage('errorMessage/noMail', 'No mail passed.')
            ]);
            return;
        }

        if (!($template = $this->Request()->getParam('template'))) {
            $this->View()->assign([
                'success' => false,
                'message' => $this->translateMessage('errorMessage/noTemplate', 'No template passed.')
            ]);
            return;
        }

        if (($template === 'sCANCELEDVOUCHER') && !($voucherId = $this->Request()->getParam('voucherId'))) {
            $this->View()->assign([
                'success' => false,
                'message' => $this->translateMessage('errorMessage/noVoucherId', 'No voucherId passed.')
            ]);
            return;
        }

        if (!($customerId = $this->Request()->getParam('customerId'))) {
            $this->View()->assign([
                'success' => false,
                'message' => $this->translateMessage('errorMessage/noCustomerId', 'No customerId passed.')
            ]);
            return;
        }

        if (!($orderId = $this->Request()->getParam('orderId'))) {
            $this->View()->assign([
                'success' => false,
                'message' => $this->translateMessage('errorMessage/noOrderId', 'No orderId passed.')
            ]);
            return;
        }

        // Set the template depending on the voucherId. -1 is a special Id, which defines
        // the 'Ask for Reason' question.
        if ($template === 'sCANCELEDQUESTION') {
            $context = null;
        } else {
            $code = $this->getFreeVoucherCode($voucherId);
            if ($code === null) {
                $this->View()->assign([
                    'success' => false,
                    'message' => $this->translateMessage('errorMessage/noVoucherCodes', 'No more free codes available.')
                ]);
                return;
            }
            $context = array(
                'sVouchercode' => $code[0]['code']
            );
        }

        // find the shop matching the order
        $orderModel = Shopware()->Models()->find('Shopware\Models\Order\Order', $orderId);
        if (!$orderModel instanceof Shopware\Models\Order\Order) {
            $shop = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop')->getActiveDefault();
        } else {
            $shop = $orderModel->getLanguageSubShop();
        }
        $shop->registerResources();

        // Try to send the actual mail
        try {
            $mail = Shopware()->TemplateMail()->createMail($template, $context, $shop);
            $mail->addTo($mailTo);
            $mail->send();
        } catch (\Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
            return;
        }

        // Mark the used voucher-code as reserved for our user
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->update('Shopware\Models\Voucher\Code', 'code')
                ->set('code.customerId', $customerId)
                ->where('code.id = ?1')
                ->andWhere('code.customerId is NULL')
                ->setParameter(1, $code[0]['id'])
                ->getQuery()
                ->execute();
        $query = $builder->getQuery();

        // Write to db that Voucher/Mail was already sent
        // For compatibility reason this is done the same way it was done in Shopware 3
        if ($template === "sCANCELEDQUESTION") {
            // 'Frage gesendet' marks a order, when its customer got a "Ask Reason" mail
            // Compatible with Shopware 3

            $orderRepository = Shopware()->Models()->getRepository('Shopware\Models\Order\Order');
            $model = $orderRepository->find($orderId);
            $model->setComment('Frage gesendet');
            Shopware()->Models()->flush();
        } else {
            $orderRepository = Shopware()->Models()->getRepository('Shopware\Models\Order\Order');
            $model = $orderRepository->find($orderId);
            $model->setComment($model->getComment() . ' Gutschein gesendet');
            Shopware()->Models()->flush();
        }

        $this->View()->assign(['success' => true]);
    }

    /*
     * Get data for the statistics view
     * */
    public function getStatisticsAction()
    {
        $startDate = $this->Request()->getParam('fromDate', date("Y-m-d", mktime(0, 0, 0, 1, 1, date("Y"))));
        $endDate   = $this->Request()->getParam('toDate', date("Y-m-d"));
        $filter = $this->Request()->getParam('filter', null);

        $params = array(
            'endDate'   => $endDate,
            'startDate' => $startDate,
        );

        if (is_array($filter) && isset($filter[0]['value'])) {
            $params['filter'] = '%' . $filter[0]['value'] . '%';
            $filter = 'AND s_core_paymentmeans.description LIKE :filter';
        } else {
            $filter = '';
        }


        // Get payment for all orders with order status -1
        $sql = "SELECT s_core_paymentmeans.description AS paymentName,  COUNT(invoice_amount) as number
        FROM s_order
        LEFT JOIN s_core_paymentmeans ON s_order.paymentID = s_core_paymentmeans.id
        WHERE s_order.status = -1
            AND s_order.ordertime >= :startDate AND s_order.ordertime <= DATE_ADD(:endDate,INTERVAL 1 DAY)
            $filter
        GROUP BY s_order.paymentID";

        $data = Shopware()->Db()->fetchAll($sql, $params);

        $this->View()->assign([
            'success' => true,
            'data' => $data,
            'total' => count($data),
        ]);
    }

    /**
     * Gert articles from canceled orders
     */
    public function getArticleAction()
    {
        $startDate = $this->Request()->getParam('fromDate', date("Y-m-d", mktime(0, 0, 0, 1, 1, date("Y"))));
        $endDate   = $this->Request()->getParam('toDate', date("Y-m-d"));
        $filter = $this->Request()->getParam('filter', null);
        $sort = $this->Request()->getParam('sort', null);

        $params = array(
            'endDate'   => $endDate,
            'startDate' => $startDate,
        );

        if (is_array($filter) && isset($filter[0]['value'])) {
            $params['filter'] = '%' . $filter[0]['value'] . '%';
            $filter = 'AND (s_articles.name LIKE :filter OR s_order_basket.ordernumber LIKE :filter)';
        } else {
            $filter = '';
        }

        if ($sort !== null && isset($sort[0]['property'])) {
            if (isset($sort['0']['direction']) && $sort['0']['direction'] === 'DESC') {
                $direction = 'DESC';
            } else {
                $direction = 'ASC';
            }

            switch ($sort[0]['property']) {
                case 'number':
                    $sort = 'b1.number';
                    break;
                case 'ordernumber':
                    $sort = 'b1.ordernumber';
                    break;
                case 'article':
                    $sort = 'b1.article';
                    break;
                default:
                    $sort = '';
                    break;
            }

            $sort = "ORDER BY $sort $direction";
        } else {
            $sort = '';
        }


        $sql = "SELECT b1.number AS number, b1.id, b1.article, b1.ordernumber
                FROM
                (
                    SELECT COUNT( s_order_basket.ordernumber ) AS number, s_articles.id, s_articles.name AS article, s_order_basket.ordernumber
                    FROM s_order_basket
                    LEFT JOIN s_articles ON s_order_basket.articleID = s_articles.id
                    WHERE s_order_basket.datum >= :startDate AND s_order_basket.datum <= DATE_ADD(:endDate,INTERVAL 1 DAY)
                    $filter
                    GROUP BY s_articles.name
                ) AS b1
                $sort";

        $data = Shopware()->Db()->fetchAll($sql, $params);

        $this->View()->assign([
            'success' => true,
            'data' => $data,
            'total' => count($data),
        ]);
    }

    /**
     * Read canceled baskets
     */
    public function getBasketAction()
    {
        $startDate = $this->Request()->getParam('fromDate', date("Y-m-d", mktime(0, 0, 0, 1, 1, date("Y"))));
        $endDate   = $this->Request()->getParam('toDate', date("Y-m-d"));
        $sort = $this->Request()->getParam('sort', null);
        $filter = $this->Request()->getParam('filter', null);

        $params = array(
            'endDate'   => $endDate,
            'startDate' => $startDate,
        );

        if (is_array($filter) && isset($filter[0]['value'])) {
            $params['filter'] = '%' . $filter[0]['value'] . '%';
            $filter = 'AND s_order_basket.datum LIKE :filter';
        } else {
            $filter = '';
        }

        if ($sort !== null && isset($sort[1]['property'])) {
            if (isset($sort['1']['direction']) && $sort['1']['direction'] === 'DESC') {
                $direction = 'DESC';
            } else {
                $direction = 'ASC';
            }

            switch ($sort[1]['property']) {
                case 'basket.date':
                    $sort = 'date';
                    break;
                case 'basket.price':
                    $sort = 'price';
                    break;
                case 'average':
                    $sort = 'average';
                    break;
                case 'number':
                    $sort = 'number';
                    break;
                default:
                    $sort = '';
                    break;
            }

            $sort = "ORDER BY $sort $direction";
        } else {
            $sort = '';
        }

        // if modus = 0 turns out to be wrong, this statement might me helpful
        //        WHERE s_order_basket.ordernumber = s_order.ordernumber AND s_order.status = -1
        $sql = "SELECT  date, price, average, number, year, month FROM
        (
        SELECT DATE_FORMAT(datum, '%Y-%m-%d') as date, sum(price) as price, AVG(price) as average,
                  COUNT(DiSTINCT sessionID) as number, YEAR(datum) as year, MONTH(datum) as month
                FROM `s_order_basket`
                WHERE s_order_basket.modus = 0
                    AND datum >= :startDate AND datum <= DATE_ADD(:endDate,INTERVAL 1 DAY)
                    $filter
                GROUP BY DAY(datum), MONTH(datum), YEAR(datum)
        ) AS derived
        $sort";


        $data = Shopware()->Db()->fetchAll($sql, $params);

        $this->View()->assign([
            'success' => true,
            'data' => $data,
            'total' => count($data),
        ]);
    }

    /**
     * Read canceled orders from database
     */
    public function getOrderAction()
    {
        $limit = $this->Request()->getParam('limit', 20);
        $offset = $this->Request()->getParam('start', 0);
        $filter = $this->Request()->getParam('filter', null);
        $filter = $filter[0]['value'];
        $sort = $this->Request()->getParam('sort', array(array('property' => 'orders.orderTime', 'direction'=>'DESC')));

        $startDate = $this->Request()->getParam('fromDate', date("Y-m-d", mktime(0, 0, 0, 1, 1, date("Y"))));
        $endDate   = $this->Request()->getParam('toDate', date("Y-m-d"));

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('orders', 'customer', 'billing', 'payment', 'details'))
                ->from('Shopware\Models\Order\Order', 'orders')
                ->leftJoin('orders.details', 'details')
                ->leftJoin('orders.customer', 'customer')
                ->leftJoin('orders.payment', 'payment')
                ->leftJoin('customer.billing', 'billing')
                ->where("orders.status = ?1 AND orders.orderTime >= ?2 AND orders.orderTime <= DATE_ADD(?3, 1, 'DAY')")
                ->setParameter(1, -1)
                ->setParameter(2, $startDate)
                ->setParameter(3, $endDate);

        if ($filter !== null) {
            $builder->andWhere('billing.lastName LIKE ?4 OR billing.firstName LIKE ?4 OR payment.description LIKE ?4 OR orders.invoiceAmount LIKE ?4')
                    ->setParameter(4, $filter . '%');
        }

        if ($sort !== null) {
            $builder->addOrderBy($sort);
        }

        $builder->setFirstResult($offset)
                ->setMaxResults($limit);

        $query = $builder->getQuery();
        $query->setHydrationMode(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        $paginator = $this->getModelManager()->createPaginator($query);
        $total = $paginator->count();
        $orders = $paginator->getIterator()->getArrayCopy();

        $this->View()->assign([
            'success' => true,
            'data' => $orders,
            'total' => $total,
        ]);
    }

    /**
     * Delete a order
     * todo@dn: fix naming e.g. deleteOrderAction
     */
    public function deleteOrderAction()
    {
        $orders = $this->Request()->getParam('orders', array(array('id' => $this->Request()->getParam('id'))));

        if (empty($orders)) {
            $this->View()->assign(array(
                'success' => false,
                'noId' => true
            ));
            return;
        }

        //iterate the posted orders and remove them.
        foreach ($orders as $order) {
            if (empty($order['id'])) {
                continue;
            }

            $model = Shopware()->Models()->find('\Shopware\Models\Order\Order', $order['id']);
            if (!$model instanceof \Shopware\Models\Order\Order) {
                continue;
            }
            Shopware()->Models()->remove($model);
        }

        Shopware()->Models()->flush();
        $this->View()->assign(['success' => true]);
    }

    /**
     * @param Order $order
     * @return \Shopware\Models\Article\Detail[]
     */
    private function getOutOfStockProducts(Order $order)
    {
        $products = $this->getProductsOfOrder($order);

        $invalid = [];
        foreach ($products as $product) {
            $position = $this->getOrderPositionByProduct($product, $order);

            if (!$position) {
                continue;
            }

            $newStock = $product->getInStock() - $position->getQuantity();

            if (!$this->isValidStock($product, $newStock)) {
                $invalid[] = $product;
            }
        }

        return $invalid;
    }

    /**
     * @param \Shopware\Models\Article\Detail $variant
     * @param Order $order
     * @return null|\Shopware\Models\Order\Detail
     */
    private function getOrderPositionByProduct(\Shopware\Models\Article\Detail $variant, Order $order)
    {
        /**@var $detail \Shopware\Models\Order\Detail*/
        foreach ($order->getDetails() as $detail) {
            if (!$this->isProductPosition($detail)) {
                continue;
            }
            if ($detail->getArticleNumber() === $variant->getNumber()) {
                return $detail;
            }
        }

        return null;
    }

    /**
     * @param Order $order
     * @return \Shopware\Models\Article\Detail[]
     */
    private function getProductsOfOrder(Order $order)
    {
        /**@var $repository \Shopware\Components\Model\ModelRepository*/
        $repository = $this->get('models')->getRepository('Shopware\Models\Article\Detail');

        $products = [];
        foreach ($order->getDetails() as $detail) {
            /**@var $detail \Shopware\Models\Order\Detail*/
            if (!$this->isProductPosition($detail)) {
                continue;
            }
            $variant = $repository->findOneBy(['number' => $detail->getArticleNumber()]);
            $products[] = $variant;
        }

        return $products;
    }


    /**
     * Function which calculates, validates and updates the new in stock when a cancelled order will be transformed into
     * a regular order
     *
     * @param Shopware\Models\Order\Order $orderModel
     * @return bool
     */
    private function convertCancelledOrderInStock(Shopware\Models\Order\Order $orderModel)
    {
        /** @var $entityManager \Shopware\Components\Model\ModelManager */
        $entityManager = $this->get('models');

        $products = $this->getProductsOfOrder($orderModel);

        foreach ($products as $product) {
            $position = $this->getOrderPositionByProduct($product, $orderModel);
            if (!$position) {
                continue;
            }

            $product->setInStock(
                $product->getInStock() - $position->getQuantity()
            );

            $entityManager->persist($product);
        }

        return true;
    }

    /**
     * Helper function to check if the stock is valid if the article is on sale
     *
     * @param \Shopware\Models\Article\Detail $variant
     * @param int $newStock
     * @return bool
     */
    private function isValidStock(Shopware\Models\Article\Detail $variant, $newStock)
    {
        if ($variant->getArticle()->getLastStock() && $newStock < 0) {
            return false;
        }
        return true;
    }

    /**
     * Checks if the order position is a regular product
     *
     * @param Shopware\Models\Order\Detail $orderDetailModel
     * @return bool
     */
    private function isProductPosition(Shopware\Models\Order\Detail $orderDetailModel)
    {
        return ($orderDetailModel->getMode() == 0);
    }

    /**
     * Helper function to get the correct translation
     *
     * @param string $name
     * @param string $default
     * @return string
     */
    private function translateMessage($name, $default = null)
    {
        $namespace = Shopware()->Snippets()->getNamespace('backend/canceled_order/controller/main');
        $translation = $namespace->get($name, $default);

        return $translation;
    }
}
