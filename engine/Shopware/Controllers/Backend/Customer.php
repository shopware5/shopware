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

use Shopware\Bundle\AccountBundle\Service\CustomerUnlockServiceInterface;
use Shopware\Components\CSRFWhitelistAware;
use Shopware\Components\NumberRangeIncrementerInterface;
use Shopware\Components\OptinServiceInterface;
use Shopware\Components\Random;
use Shopware\Components\StateTranslatorService;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Customer\PaymentData;
use Shopware\Models\Payment\Payment;
use Symfony\Component\HttpFoundation\Cookie;

class Shopware_Controllers_Backend_Customer extends Shopware_Controllers_Backend_ExtJs implements CSRFWhitelistAware
{
    /**
     * Customer repository. Declared for an fast access to the customer repository.
     * Initialed in the init() function.
     *
     * @var \Shopware\Models\Customer\Repository|null
     */
    public static $repository;

    /**
     * Contains the shopware model manager
     *
     * @var \Shopware\Components\Model\ModelManager
     */
    public static $manager;

    /**
     * @var \Shopware\Components\Model\ModelRepository
     */
    protected $groupRepository;

    /**
     * @var \Shopware\Models\Shop\Repository
     */
    protected $shopRepository;

    /**
     * @var \Shopware\Models\Order\Repository
     */
    protected $orderRepository;

    /**
     * @var \Shopware\Models\Payment\Repository
     */
    protected $paymentRepository;

    /**
     * @var \Shopware\Models\Dispatch\Repository
     */
    protected $dispatchRepository;

    /**
     * @var \Shopware\Models\Country\Repository
     */
    protected $countryRepository;

    /**
     * Deactivates the authentication for the performOrderRedirect action
     * This is used in the performOrder action
     */
    public function init()
    {
        if ($this->Request()->getActionName() === 'performOrderRedirect') {
            Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        }
        $currency = Shopware()->Db()->fetchRow(
            'SELECT templatechar as sign, (symbol_position = 16) currencyAtEnd
            FROM s_core_currencies
            WHERE standard = 1'
        );

        $this->View()->assign('currency', $currency);

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'performOrderRedirect',
        ];
    }

    /**
     * Disable template engine for all actions
     *
     * @codeCoverageIgnore
     */
    public function preDispatch()
    {
        if (!in_array($this->Request()->getActionName(), ['index', 'load', 'validateEmail'])) {
            $this->Front()->Plugins()->Json()->setRenderer(true);
        }
    }

    public function loadStoresAction()
    {
        $orderStatus = $this->getOrderRepository()->getOrderStatusQuery()->getArrayResult();
        $paymentStatus = $this->getOrderRepository()->getPaymentStatusQuery()->getArrayResult();
        $payment = $this->getPaymentRepository()->getAllPaymentsQuery()->getArrayResult();
        $dispatch = $this->getDispatchRepository()->getDispatchesQuery()->getArrayResult();
        $shop = $this->getShopRepository()->getBaseListQuery()->getArrayResult();
        $country = $this->getCountryRepository()->getCountriesQuery()->getArrayResult();
        $customerGroups = $this->getRepository()->getCustomerGroupsQuery()->getArrayResult();

        /** @var \Shopware\Components\StateTranslatorServiceInterface $stateTranslator */
        $stateTranslator = $this->get('shopware.components.state_translator');
        $orderStatus = array_map(function ($orderStateItem) use ($stateTranslator) {
            return $stateTranslator->translateState(StateTranslatorService::STATE_ORDER, $orderStateItem);
        }, $orderStatus);

        $paymentStatus = array_map(function ($paymentStateItem) use ($stateTranslator) {
            return $stateTranslator->translateState(StateTranslatorService::STATE_PAYMENT, $paymentStateItem);
        }, $paymentStatus);

        // Translate payment and dispatch method names.
        $translationComponent = $this->get('translation');
        $payment = $translationComponent->translatePaymentMethods($payment);
        $dispatch = $translationComponent->translateDispatchMethods($dispatch);

        $this->View()->assign([
            'success' => true,
            'data' => [
                'orderStatus' => $orderStatus,
                'paymentStatus' => $paymentStatus,
                'payment' => $payment,
                'dispatch' => $dispatch,
                'shop' => $shop,
                'country' => $country,
                'customerGroup' => $customerGroups,
            ],
        ]);
    }

    /**
     * Event listener method which fires when the customer detail
     * store is loaded. Returns an array with all data about one customer.
     * Expects an customer id as parameter to read the detail data
     * only for one customer.
     */
    public function getDetailAction()
    {
        $customerId = $this->Request()->getParam('customerID');
        if ($customerId === null || $customerId === 0) {
            $this->View()->assign(['success' => false, 'message' => 'No customer id passed']);

            return;
        }

        $data = $this->getCustomer($customerId);
        $data['serverTime'] = new DateTime($this->get('dbal_connection')->fetchColumn('SELECT NOW()'));

        $this->View()->assign(['success' => true, 'data' => $data, 'total' => 1]);
    }

    /**
     * Event listener method which fires when the customer order store is loaded.
     * Returns an array of all customer orders to display them in an Ext.grid.Panel.
     * Grants by the limit and start parameter a paging for the customer order data.
     * The filter parameter allows the user a full text search
     * over the displayed fields.
     */
    public function getOrdersAction()
    {
        if (!$this->_isAllowed('read', 'order')) {
            /** @var Enlight_Components_Snippet_Namespace $namespace */
            $namespace = Shopware()->Snippets()->getNamespace('backend/customer');

            $this->View()->assign([
                'success' => false,
                'data' => $this->Request()->getParams(),
                'message' => $namespace->get('no_order_rights', 'You do not have sufficient rights to view customer orders.'), ]
            );

            return;
        }

        $customerId = $this->Request()->getParam('customerID');
        if ($customerId === null || $customerId === 0) {
            $this->View()->assign(['success' => false, 'message' => 'No customer id passed']);

            return;
        }

        $defaultSort = ['0' => ['property' => 'orderTime', 'direction' => 'DESC']];

        $limit = (int) $this->Request()->getParam('limit', 20);
        $offset = (int) $this->Request()->getParam('start', 0);
        $sort = $this->Request()->getParam('sort', $defaultSort);
        $filter = $this->Request()->getParam('filter');
        $filter = $filter[0]['value'];

        // Get access on the customer getRepository()
        $query = $this->getRepository()->getOrdersQuery($customerId, $filter, $sort, $limit, $offset);

        // Returns the total count of the query
        $totalResult = $this->getManager()->getQueryCount($query);

        // Returns the customer data
        $orders = $query->getArrayResult();

        $this->View()->assign(['success' => true, 'data' => $orders, 'total' => $totalResult]);
    }

    /**
     * Event listener method which fires when the detail page of a customer is loaded.
     * Returns an array of grouped order data to display them in a line chart.
     */
    public function getOrderChartAction()
    {
        if (!$this->_isAllowed('read', 'order')) {
            /** @var Enlight_Components_Snippet_Namespace $namespace */
            $namespace = Shopware()->Snippets()->getNamespace('backend/customer');

            $this->View()->assign([
                'success' => false,
                'data' => $this->Request()->getParams(),
                'message' => $namespace->get('no_order_rights', 'You do not have sufficient rights to view customer orders.'), ]
            );

            return;
        }

        // Customer id passed?
        $customerId = $this->Request()->getParam('customerID');
        if ($customerId === null || $customerId === 0) {
            $this->View()->assign(['success' => false, 'message' => 'No customer id passed']);

            return;
        }
        $orders = $this->getChartData($customerId);

        $this->View()->assign(['success' => true, 'data' => $orders]);
    }

    /**
     * Saves a single customer. If no customer id passed,
     * the save function creates a new customer model and persist
     * it by the shopware model manager.
     * The sub models billing, shipping and debit will be filled
     * by the passed parameter arrays billing, shipping and debit.
     */
    public function saveAction()
    {
        $id = $this->Request()->getParam('id');
        $paymentId = $this->Request()->getParam('paymentId');
        $params = $this->Request()->getParams();
        $paymentData = null;

        /** @var Enlight_Components_Snippet_Namespace $namespace */
        $namespace = Shopware()->Snippets()->getNamespace('backend/customer');

        // Customer id passed? If this is the case the customer was edited
        if (!empty($id)) {
            // Check if the user has the rights to update an existing customer
            if (!$this->_isAllowed('update', 'customer')) {
                $this->View()->assign([
                    'success' => false,
                    'data' => $this->Request()->getParams(),
                    'message' => $namespace->get('no_edit_rights', 'You do not have sufficient rights to edit a customer.'),
                ]);

                return;
            }

            /** @var Customer $customer */
            $customer = $this->getRepository()->find((int) $id);
            /** @var PaymentData $paymentData */
            $paymentData = $this->getManager()->getRepository(PaymentData::class)->findOneBy(
                ['customer' => $customer, 'paymentMean' => (int) $paymentId]
            );

            if ($customer->getChanged() !== null) {
                // Check whether the customer has been modified in the meantime
                try {
                    $changed = new \DateTime($params['changed']);
                } catch (Exception $e) {
                    // If we have a invalid date caused by imports
                    $changed = $customer->getChanged();
                }

                if ($changed->getTimestamp() < 0 && $customer->getChanged()->getTimestamp() < 0) {
                    $changed = $customer->getChanged();
                }

                $diff = abs($customer->getChanged()->getTimestamp() - $changed->getTimestamp());

                // We have timestamp conversion issues on Windows Users
                if ($diff > 1) {
                    $namespace = Shopware()->Snippets()->getNamespace('backend/customer/controller/main');

                    $this->View()->assign([
                        'success' => false,
                        'data' => $this->getCustomer($customer->getId()),
                        'overwriteAble' => true,
                        'message' => $namespace->get('customer_has_been_changed', 'The customer has been changed in the meantime. To prevent overwriting these changes, saving the customer was aborted. Please close the customer and re-open it.'),
                    ]);

                    return;
                }
            }
        } else {
            // Check if the user has the rights to create a new customer
            if (!$this->_isAllowed('create', 'customer')) {
                $this->View()->assign([
                    'success' => false,
                    'data' => $this->Request()->getParams(),
                    'message' => $namespace->get('no_create_rights', 'You do not have sufficient rights to view create a customer.'),
                ]);

                return;
            }
            $customer = new Customer();
        }

        if (!($paymentData instanceof PaymentData) && !empty($params['paymentData']) && !empty(array_filter($params['paymentData'][0]))) {
            $paymentData = new PaymentData();
            $customer->addPaymentData($paymentData);

            /** @var Payment $payment */
            $payment = $this->getManager()
                ->getRepository(\Shopware\Models\Payment\Payment::class)
                ->find($paymentId);
            $paymentData->setPaymentMean($payment);
        }

        $params = $this->prepareCustomerData($params, $customer, $paymentData);

        // Set parameter to the customer model.
        $customer->fromArray($params);

        // If user will be activated, but the first login is still 0, because he was in doi-process
        if ($customer->getActive() && $customer->getFirstLogin()->getTimestamp() === 0) {
            $customer->setFirstLogin(new DateTime());
        }

        $password = $this->Request()->getParam('newPassword');

        // Encode the password with md5
        if (!empty($password)) {
            $customer->setPassword($password);
        }

        if (!$customer->getNumber() && Shopware()->Config()->get('shopwareManagedCustomerNumbers')) {
            /** @var NumberRangeIncrementerInterface $incrementer */
            $incrementer = Shopware()->Container()->get('shopware.number_range_incrementer');
            $customer->setNumber((string) $incrementer->increment('user'));
        }

        $this->getManager()->persist($customer);
        $this->getManager()->flush();

        $this->View()->assign([
            'success' => true,
            'data' => $this->getCustomer($customer->getId()),
        ]);
    }

    /**
     * Deletes a single customer or an array of customers from the database.
     * Expects a single customer id or an array of customer ids which placed in the parameter customers
     */
    public function deleteAction()
    {
        //get posted customers
        $customers = $this->Request()->getParam('customers', [['id' => $this->Request()->getParam('id')]]);

        //iterate the customers and add the remove action
        foreach ($customers as $customer) {
            $entity = $this->getRepository()->find($customer['id']);
            $this->getManager()->remove($entity);
        }
        //Performs all of the collected actions.
        $this->getManager()->flush();

        $this->View()->assign([
                'success' => true,
                'data' => $this->Request()->getParams(), ]
        );
    }

    /**
     * Validates the inserted email address
     */
    public function validateEmailAction()
    {
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();

        $mail = $this->Request()->get('value');

        $query = $this->getRepository()->getValidateEmailQuery(
            $mail,
            $this->Request()->get('param'),
            $this->Request()->get('subshopId')
        );

        $customer = $query->getArrayResult();

        /** @var \Shopware\Components\Validator\EmailValidatorInterface $emailValidator */
        $emailValidator = $this->container->get('validator.email');

        if (empty($customer) && $emailValidator->isValid($mail)) {
            $this->Response()->setContent(1);
        } else {
            $this->Response()->setContent('');
        }
    }

    /**
     * Redirect the backend user to the frontend, impersonating a customer
     */
    public function performOrderAction()
    {
        $userId = $this->Request()->getParam('id');
        $user = $this->get('dbal_connection')->fetchAssoc(
            'SELECT id, email, password, subshopID, language FROM s_user WHERE id = :userId',
            [
                ':userId' => $userId,
            ]
        );

        if (empty($user['email'])) {
            return;
        }

        /** @var Shopware\Models\Shop\Repository $repository */
        $repository = $this->getShopRepository();
        $shop = $repository->getActiveById($user['language']);

        $this->get('shopware.components.shop_registration_service')->registerShop($shop);

        session_regenerate_id(true);
        $newSessionId = session_id();

        session_write_close();
        session_start();

        Shopware()->Session()->offsetSet('sessionId', $newSessionId);
        Shopware()->Container()->reset('SessionId');
        Shopware()->Container()->set('SessionId', $newSessionId);
        Shopware()->Session()->unsetAll();

        Shopware()->Session()->Admin = true;
        Shopware()->System()->_POST = [
            'email' => $user['email'],
            'passwordMD5' => $user['password'],
        ];
        Shopware()->Modules()->Admin()->sLogin(true);

        $hash = $this->container->get('shopware.components.optin_service')->add(OptinServiceInterface::TYPE_CUSTOMER_LOGIN_FROM_BACKEND, 300, [
            'sessionId' => Shopware()->Session()->get('sessionId'),
            'shopId' => $shop->getId(),
        ]);

        $url = $this->Front()->Router()->assemble([
            'action' => 'performOrderRedirect',
            'hash' => $hash,
            'fullPath' => true,
        ]);

        if ($shop->getHost()) {
            //change the url to the subshop url
            $url = str_replace('://' . $this->Request()->getHttpHost(), '://' . $shop->getHost(), $url);
        }

        $this->redirect($url);
    }

    /**
     * This Action can be called with a different domain.
     * So domain depending cookies can be changed.
     * This is needed when the users want's to perform an order on a different domain.
     * For example in a different Subshop
     */
    public function performOrderRedirectAction()
    {
        $hash = $this->Request()->getQuery('hash');

        $optinService = $this->container->get('shopware.components.optin_service');

        $data = $optinService->get(OptinServiceInterface::TYPE_CUSTOMER_LOGIN_FROM_BACKEND, $hash);

        if ($data === null) {
            $this->redirect(['module' => 'backend', 'controller' => 'index', 'action' => 'index']);
        }

        $optinService->delete(OptinServiceInterface::TYPE_CUSTOMER_LOGIN_FROM_BACKEND, $hash);

        /** @var Shopware\Models\Shop\Repository $repository */
        $repository = $this->getShopRepository();
        $shop = $repository->getActiveById($data['shopId']);

        $path = rtrim($shop->getBasePath(), '/') . '/';

        // Update right domain cookies
        $this->Response()->headers->setCookie(new Cookie('shop', $data['shopId'], 0, $path));
        $this->Response()->headers->setCookie(new Cookie('sUniqueID', Random::getString(20), 0, $path));
        $this->Response()->headers->setCookie(new Cookie('session-' . $data['shopId'], $data['sessionId'], 0, '/'));

        $this->redirect($shop->getBaseUrl());
    }

    public function unlockCustomerAction()
    {
        $customerId = (int) $this->Request()->getParam('customerId');

        try {
            /** @var CustomerUnlockServiceInterface $unlockService */
            $unlockService = $this->get('shopware_account.customer_unlock_service');

            $unlockService->unlock($customerId);
        } catch (Exception $e) {
            $this->View()->assign('success', false);

            return;
        }

        $this->View()->assign('success', true);
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
     * @return Shopware\Models\Customer\Repository
     */
    protected function getRepository()
    {
        if (self::$repository === null) {
            self::$repository = Shopware()->Models()->getRepository(\Shopware\Models\Customer\Customer::class);
        }

        return self::$repository;
    }

    /**
     * Registers the different acl permission for the different controller actions.
     */
    protected function initAcl()
    {
        $this->addAclPermission('getList', 'read', 'You do not have sufficient rights to view the list of customers.');
        $this->addAclPermission('getDetail', 'detail', 'You do not have sufficient rights to view the customer detail page.');
        $this->addAclPermission('getOrders', 'read', 'You do not have sufficient rights to view customer orders.');
        $this->addAclPermission('getOrderChart', 'read', 'You do not have sufficient rights to view customer orders.');
        $this->addAclPermission('delete', 'delete', 'You do not have sufficient rights to delete a customers.');
    }

    /**
     * Helper function to get access to the shop repository.
     *
     * @return \Shopware\Models\Shop\Repository
     */
    private function getShopRepository()
    {
        if ($this->shopRepository === null) {
            $this->shopRepository = Shopware()->Models()->getRepository(\Shopware\Models\Shop\Shop::class);
        }

        return $this->shopRepository;
    }

    /**
     * Helper function to get access to the group repository.
     *
     * @return \Shopware\Components\Model\ModelRepository
     */
    private function getGroupRepository()
    {
        if ($this->groupRepository === null) {
            $this->groupRepository = Shopware()->Models()->getRepository(\Shopware\Models\Customer\Group::class);
        }

        return $this->groupRepository;
    }

    /**
     * Helper function to get access to the country repository.
     *
     * @return \Shopware\Models\Country\Repository
     */
    private function getCountryRepository()
    {
        if ($this->countryRepository === null) {
            $this->countryRepository = Shopware()->Models()->getRepository('Shopware\Models\Country\Country');
        }

        return $this->countryRepository;
    }

    /**
     * Helper function to get access to the order repository.
     *
     * @return \Shopware\Models\Order\Repository
     */
    private function getOrderRepository()
    {
        if ($this->orderRepository === null) {
            $this->orderRepository = Shopware()->Models()->getRepository('Shopware\Models\Order\Order');
        }

        return $this->orderRepository;
    }

    /**
     * Helper function to get access to the payment repository.
     *
     * @return \Shopware\Models\Payment\Repository
     */
    private function getPaymentRepository()
    {
        if ($this->paymentRepository === null) {
            $this->paymentRepository = Shopware()->Models()->getRepository('Shopware\Models\Payment\Payment');
        }

        return $this->paymentRepository;
    }

    /**
     * Helper function to get access to the dispatch repository.
     *
     * @return \Shopware\Models\Dispatch\Repository
     */
    private function getDispatchRepository()
    {
        if ($this->dispatchRepository === null) {
            $this->dispatchRepository = Shopware()->Models()->getRepository('Shopware\Models\Dispatch\Dispatch');
        }

        return $this->dispatchRepository;
    }

    /**
     * Select the customer orders grouped by year and month. Can be filtered over the fromDate and toDate parameter.
     * If the date of the first founded order not equals with the fromDate, an empty row will be prepend.
     * If the date of the last founded order  not equals with the fromDate, an empty row will be append.
     *
     * @param int $customerId
     *
     * @return array
     */
    private function getChartData($customerId)
    {
        // If a from date passed, format it over the \DateTime object. Otherwise create a new date with today - 1 year
        $fromDate = $this->Request()->getParam('fromDate');
        if (empty($fromDate)) {
            $fromDate = new \DateTime();
            $fromDate->setDate((int) $fromDate->format('Y') - 1, (int) $fromDate->format('m'), (int) $fromDate->format('d'));
        } else {
            $fromDate = new \DateTime($fromDate);
        }
        $fromDateFilter = $fromDate->format('Y-m-d');

        // If a to date passed, format it over the \DateTime object. Otherwise create a new date with today
        $toDate = $this->Request()->getParam('toDate');
        if (empty($toDate)) {
            $toDate = new \DateTime();
        } else {
            $toDate = new \DateTime($toDate);
        }
        $toDateFilter = $toDate->format('Y-m-d');

        $sql = "
            SELECT
                SUM(invoice_amount) as amount,
                DATE_FORMAT(ordertime, '%Y-%m-01') as `date`
            FROM s_order
            WHERE userID = ?
            AND s_order.status NOT IN (-1, 4)
            AND ordertime >= ?
            AND ordertime <= ?
            GROUP by YEAR(ordertime), MONTH(ordertime)
        ";

        // Select the orders from the database
        $orders = Shopware()->Db()->fetchAll($sql, [$customerId, $fromDateFilter, $toDateFilter]);

        if (!empty($orders)) {
            $first = new \DateTime($orders[0]['date']);
            $last = new \DateTime($orders[count($orders) - 1]['date']);

            // To display the whole time range the user inserted, check if the date of the first order equals the fromDate parameter
            if ($fromDate->format('Y-m') !== $first->format('Y-m')) {
                // Create a new dummy order with amount 0 and the date the user inserted.
                $fromDate->setDate((int) $fromDate->format('Y'), (int) $fromDate->format('m'), 1);
                $emptyOrder = ['amount' => '0.00', 'date' => $fromDate->format('Y-m-d')];
                array_unshift($orders, $emptyOrder);
            }

            // To display the whole time range the user inserted, check if the date of the last order equals the toDate parameter
            if ($toDate->format('Y-m') !== $last->format('Y-m')) {
                $toDate->setDate((int) $toDate->format('Y'), (int) $toDate->format('m'), 1);
                $orders[] = ['amount' => '0.00', 'date' => $toDate->format('Y-m-d')];
            }
        }

        return $orders;
    }

    /**
     * Internal helper function to get a single customer
     *
     * @param int $id
     *
     * @return array|mixed
     */
    private function getCustomer($id)
    {
        $query = $this->getRepository()->getCustomerDetailQuery($id);

        $data = $query->getOneOrNullResult(Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $orderInfo = [
            'orderCount' => $data['orderCount'],
            'amount' => $data['amount'],
            'shopName' => $data['shopName'],
            'language' => $data['language'],
            'canceledOrderAmount' => $data['canceledOrderAmount'],
            'default_billing_address_id' => $data['default_billing_address_id'],
            'default_shipping_address_id' => $data['default_shipping_address_id'],
        ];

        $data = array_merge($orderInfo, $data[0]);
        $birthday = $data['birthday'];

        /** @var \DateTimeInterface $birthday */
        if ($birthday instanceof \DateTimeInterface) {
            $data['birthday'] = $birthday->format('d.m.Y');
        }

        $namespace = Shopware()->Container()->get('snippets')->getNamespace('frontend/salutation');
        $data['defaultBillingAddress']['salutationSnippet'] = $namespace->get($data['defaultBillingAddress']['salutation']);
        $data['defaultShippingAddress']['salutationSnippet'] = $namespace->get($data['defaultShippingAddress']['salutation']);
        $data['customerStreamIds'] = $this->fetchCustomerStreams($id);

        if ($data['firstLogin'] instanceof \DateTimeInterface && $data['firstLogin']->getTimestamp() < 0) {
            $data['firstLogin'] = new \DateTime('@0');
        }

        if ($data['lastLogin'] instanceof \DateTimeInterface && $data['lastLogin']->getTimestamp() < 0) {
            $data['lastLogin'] = new \DateTime('@0');
        }

        return $data;
    }

    /**
     * Helper method to prepare the customer for saving
     *
     * @param array                                      $params
     * @param \Shopware\Models\Customer\PaymentData|null $paymentData
     *
     * @return array
     */
    private function prepareCustomerData($params, Shopware\Models\Customer\Customer $customer, $paymentData)
    {
        if (!empty($params['groupKey'])) {
            $params['group'] = $this->getGroupRepository()->findOneBy(['key' => $params['groupKey']]);
        } else {
            unset($params['group']);
        }

        if (!empty($params['languageId'])) {
            /** @var \Shopware\Models\Shop\Repository $shopRepository */
            $shopRepository = $this->getShopRepository();
            $params['languageSubShop'] = $shopRepository->find($params['languageId']);
        } else {
            unset($params['languageSubShop'], $params['shop']);
        }

        if (!empty($params['priceGroupId'])) {
            $params['priceGroup'] = Shopware()->Models()->find('Shopware\Models\Customer\PriceGroup', $params['priceGroupId']);
        } else {
            $params['priceGroup'] = null;
        }

        // If a different payment method is selected, it must also be placed in the "paymentPreset" so that the risk management that does not reset.
        if ($customer->getPaymentId() !== $params['paymentId']) {
            $params['paymentPreset'] = $params['paymentId'];
        }

        if (empty($params['shipping'][0]['firstName']) && empty($params['shipping'][0]['lastName'])) {
            // Shipping params are empty use the billing ones
            $params['shipping'][0] = $params['billing'][0];
        }

        if ($paymentData && !empty($params['paymentData'])) {
            $paymentData->fromArray(array_shift($params['paymentData']));
        }

        unset($params['paymentData'], $params['attribute']);

        if (isset($params['billing'])) {
            $params['billing'] = $params['billing'][0];
        }
        if (isset($params['shipping'])) {
            $params['shipping'] = $params['shipping'][0];
        }

        if (!isset($params['birthday'])) {
            $params['birthday'] = null;
        }

        return $params;
    }

    /**
     * @param int $id
     *
     * @return string
     */
    private function fetchCustomerStreams($id)
    {
        $query = $this->container->get('dbal_connection')->createQueryBuilder();

        $ids = $query->select(['mapping.stream_id'])
            ->from('s_customer_streams_mapping', 'mapping')
            ->innerJoin('mapping', 's_customer_streams', 'streams', 'streams.id = mapping.stream_id')
            ->where('mapping.customer_id = :id')
            ->addOrderBy('streams.name', 'ASC')
            ->setParameter(':id', $id)
            ->execute()
            ->fetchAll(PDO::FETCH_COLUMN);

        return implode('|', $ids);
    }
}
