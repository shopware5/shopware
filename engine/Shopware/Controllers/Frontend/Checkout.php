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

use Enlight_Controller_Request_Request as Request;
use Shopware\Bundle\CartBundle\Domain\Cart\CartContextInterface;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItem;
use Shopware\Bundle\CartBundle\Domain\Payment\PaymentMethod;
use Shopware\Bundle\CartBundle\Domain\Product\ProductProcessor;
use Shopware\Bundle\CartBundle\Infrastructure\StoreFrontCartService;
use Shopware\Bundle\CartBundle\Infrastructure\View\ViewCart;
use Shopware\Components\BasketSignature\BasketPersister;
use Shopware\Components\BasketSignature\BasketSignatureGeneratorInterface;
use Shopware\Models\Customer\Address;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Frontend_Checkout extends Enlight_Controller_Action
{
    const ACTION_AJAX_CART = 'ajaxCart';
    const ACTION_CART = 'cart';
    const ACTION_CONFIRM = 'confirm';

    public function cartAction()
    {
        /** @var StoreFrontCartService $service */
        $service = $this->get('shopware_cart.store_front_cart_service');

        $cart = json_decode(json_encode($service->getCart()), true);

        $this->View()->assign([
            'cart' => $cart,
            'sTargetAction' => self::ACTION_CART
        ]);
    }

    public function ajaxCartAction()
    {
        Shopware()->Plugins()->Controller()->Json()->setPadding();

        /** @var StoreFrontCartService $service */
        $service = $this->get('shopware_cart.store_front_cart_service');

        $cart = json_decode(json_encode($service->getCart()), true);

        $this->View()->assign([
            'cart' => $cart,
            'sTargetAction' => self::ACTION_AJAX_CART
        ]);
    }

    public function ajaxAmountAction()
    {
        Shopware()->Plugins()->Controller()->Json()->setPadding();

        /** @var StoreFrontCartService $service */
        $service = $this->get('shopware_cart.store_front_cart_service');

        $cart = $service->getCart();

        $quantity = $cart->getCalculatedCart()->getLineItems()->filterGoods()->count();

        $this->View()->assign([
            'sBasketQuantity' => $quantity,
            'sBasketAmount' => $cart->getPrice()->getTotalPrice()
        ]);

        $template = Shopware()->Template()->fetch('frontend/checkout/ajax_amount.tpl');

        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        $this->Response()->setBody(json_encode(['amount' => $template, 'quantity' => $quantity]));
    }

    public function addProductAction()
    {
        $number = $this->Request()->getParam('number');
        if (!$number) {
            throw new Exception("No product number given");
        }

        $quantity = (int) $this->Request()->getParam('quantity', 1);

        /** @var StoreFrontCartService $service */
        $service = $this->get('shopware_cart.store_front_cart_service');
        $service->add(
            new LineItem($number, ProductProcessor::TYPE_PRODUCT, $quantity)
        );

        $this->forward(
            $this->Request()->getParam('sTargetAction', self::ACTION_AJAX_CART)
        );
    }

    public function removeLineItemAction()
    {
        $identifier = $this->Request()->getParam('identifier');

        /** @var StoreFrontCartService $service */
        $service = $this->get('shopware_cart.store_front_cart_service');
        $service->remove($identifier);

        $this->forward(
            $this->Request()->getParam('sTargetAction', self::ACTION_AJAX_CART)
        );
    }

    public function changeQuantityAction()
    {
        if (!$this->Request()->isPost()) {
            throw new Exception('Only post request allowed');
        }

        /** @var StoreFrontCartService $service */
        $service = $this->get('shopware_cart.store_front_cart_service');

        $identifier = $this->Request()->getPost('identifier');
        if (!$identifier) {
            throw new Exception("Missing parameter identifier");
        }

        $quantity = $this->Request()->getPost('quantity');
        if (!$quantity) {
            throw new Exception("Missing parameter quantity");
        }

        $service->changeQuantity($identifier, $quantity);

        $this->forward(
            $this->Request()->getParam('sTargetAction', self::ACTION_CART)
        );
    }

    /**
     * Ajax add article action
     *
     * This action will get redirected from the default addArticleAction
     * when the request was an AJAX request.
     *
     * The json padding will be set so that the content type will get to
     * 'text/javascript' so the template can be returned via jsonp
     */
    public function ajaxAddArticleAction()
    {
        Shopware()->Plugins()->Controller()->Json()->setPadding();

        /** @var StoreFrontCartService $service */
        $service = $this->get('shopware_cart.store_front_cart_service');

        $number = $this->Request()->getParam('number');

        $product = $service->getCart()->getLineItems()->get($number);

        $this->View()->assign(['lineItem' => json_decode(json_encode($product), true)]);
    }

    public function confirmAction(): void
    {
        /** @var CartContextInterface $context */
        $context = $this->get('shopware_cart.cart_context_service')->getCartContext();

        if ($context->getCustomer() === null) {
            $this->forwardToLogin();
            return;
        }

        /** @var ViewCart $cart */
        $cart = $this->get('shopware_cart.store_front_cart_service')->getCart();

        if ($cart->getLineItems()->count() === 0) {
            $this->forward(self::ACTION_CART);
            return;
        }

        $this->View()->assign([
            'cart' => json_decode(json_encode($cart), true),
            'sTargetAction' => self::ACTION_CONFIRM
        ]);
    }

    public function shippingPaymentAction()
    {
        /** @var ViewCart $cart */
        $cart = $this->get('shopware_cart.store_front_cart_service')->getCart();

        /** @var CartContextInterface $context */
        $context = $this->get('shopware_cart.cart_context_service')->getCartContext();

        /** @var PaymentMethod[] $payments */
        $payments = $this->get('shopware_cart.payment_method_service')->getAvailable(
            $cart->getCalculatedCart(),
            $context
        );

        $payments = json_decode(json_encode($payments), true);

        $this->View()->assign([
            'cart' => json_decode(json_encode($cart), true),
            'payments' => $payments,
            'currentPaymentId' => $context->getPaymentMethod()->getId()
        ]);

        if ($this->Request()->getParam('isXHR')) {
            return $this->View()->loadTemplate('frontend/checkout/shipping_payment_core.tpl');
        }
    }

    private function forwardToLogin()
    {
        $this->forward('login', 'account', null, ['sTarget' => 'checkout', 'sTargetAction' => self::ACTION_CONFIRM, 'showNoAccount' => true]);
    }


    /**
     * Reference to sAdmin object (core/class/sAdmin.php)
     *
     * @var sAdmin
     */
    protected $admin;

    /**
     * Reference to sBasket object (core/class/sBasket.php)
     *
     * @var sBasket
     */
    protected $basket;

    /**
     * Reference to Shopware session object (Shopware()->Session)
     *
     * @var Enlight_Components_Session_Namespace
     */
    protected $session;

    /**
     * Init method that get called automatically
     *
     * Set class properties
     */
    public function init()
    {
        $this->admin = Shopware()->Modules()->Admin();
        $this->basket = Shopware()->Modules()->Basket();
        $this->session = Shopware()->Session();
    }

    /**
     * Pre dispatch method
     */
    public function preDispatch()
    {
        $events = Shopware()->Container()->get('events');
        $events->addListener('Shopware_Modules_Admin_Payment_Fallback', [$this, 'flagPaymentBlocked']);

        $this->View()->setScope(Enlight_Template_Manager::SCOPE_PARENT);

        $this->View()->sUserLoggedIn = $this->admin->sCheckUser();
        $this->View()->sUserData = $this->getUserData();
    }

    /**
     * Called if the sAdmin resets the selected customer payment to the shop preset
     */
    public function flagPaymentBlocked()
    {
        $this->View()->assign('paymentBlocked', true);
    }

    /**
     * Save basket to session
     */
    public function postDispatch()
    {
        $this->session->sBasketCurrency = Shopware()->Shop()->getCurrency()->getId();
        $this->session->sBasketQuantity = $this->basket->sCountBasket();
        $amount = $this->basket->sGetAmount();
        $this->session->sBasketAmount = empty($amount) ? 0 : array_shift($amount);
    }

    /**
     * Forward to cart or confirm action depending on user state
     */
    public function indexAction()
    {
        if ($this->basket->sCountBasket() < 1 || empty($this->View()->sUserLoggedIn)) {
            $this->forward('cart');
        } else {
            $this->forward('confirm');
        }
    }

    /**
     * Called from confirmAction View
     * Customers requests to finish current order
     * Check if all conditions match and save order
     */
    public function finishAction()
    {
        if ($this->Request()->getParam('sUniqueID') && !empty($this->session['sOrderVariables'])) {
            $sql = '
                SELECT transactionID as sTransactionumber, ordernumber as sOrderNumber
                FROM s_order
                WHERE temporaryID=? AND userID=?
            ';

            $order = Shopware()->Db()->fetchRow($sql, [$this->Request()->getParam('sUniqueID'), Shopware()->Session()->sUserId]);
            if (!empty($order)) {
                $this->View()->assign($order);
                $orderVariables = $this->session['sOrderVariables']->getArrayCopy();

                if (!empty($orderVariables['sOrderNumber'])) {
                    $orderVariables['sAddresses']['billing'] = $this->getOrderAddress($orderVariables['sOrderNumber'], 'billing');
                    $orderVariables['sAddresses']['shipping'] = $this->getOrderAddress($orderVariables['sOrderNumber'], 'shipping');
                    $orderVariables['sAddresses']['equal'] = $this->areAddressesEqual($orderVariables['sAddresses']['billing'], $orderVariables['sAddresses']['shipping']);
                }

                $this->View()->assign($orderVariables);

                return;
            }
        }

        if (empty($this->session['sOrderVariables']) || $this->getMinimumCharge() || $this->getEsdNote() || $this->getDispatchNoOrder()) {
            return $this->forward('confirm');
        }

        $checkQuantities = $this->basket->sCheckBasketQuantities();
        if (!empty($checkQuantities['hideBasket'])) {
            return $this->forward('confirm');
        }

        $orderVariables = $this->session['sOrderVariables']->getArrayCopy();

        if (!empty($orderVariables['sOrderNumber'])) {
            $orderVariables['sAddresses']['billing'] = $this->getOrderAddress($orderVariables['sOrderNumber'], 'billing');
            $orderVariables['sAddresses']['shipping'] = $this->getOrderAddress($orderVariables['sOrderNumber'], 'shipping');
            $orderVariables['sAddresses']['equal'] = $this->areAddressesEqual($orderVariables['sAddresses']['billing'], $orderVariables['sAddresses']['shipping']);
        }

        $this->View()->assign($orderVariables);

        if ($this->basket->sCountBasket() <= 0) {
            return;
        }

        if (!empty($this->View()->sUserData['additional']['payment']['embediframe'])) {
            return;
        }

        if ($this->Request()->getParam('sNewsletter') !== null) {
            $this->session['sNewsletter'] = $this->Request()->getParam('sNewsletter') ? true : false;
        }
        if ($this->Request()->getParam('sComment') !== null) {
            $this->session['sComment'] = trim(strip_tags($this->Request()->getParam('sComment')));
        }

        $basket = $this->View()->sBasket;
        $agreements = $this->getInvalidAgreements($basket, $this->Request());

        if (!empty($agreements)) {
            $this->View()->sAGBError = array_key_exists('agbError', $agreements);

            return $this->forward(
                'confirm',
                null,
                null,
                ['agreementErrors' => $agreements]
            );
        }

        if (!$this->basket->validateVoucher($this->session['sessionId'], $this->session['sUserId'])) {
            $namespace = $this->container->get('snippets')->getNamespace('frontend/basket/internalMessages');

            return $this->forward(
                'confirm',
                null,
                null,
                ['voucherErrors' => [
                    $namespace->get('VoucherFailureAlreadyUsed', 'This voucher was used in an previous order'),
                ]]
            );
        }

        if (empty($activeBillingAddressId = $this->session->offsetGet('checkoutBillingAddressId', null))) {
            $activeBillingAddressId = $this->View()->sUserData['additional']['user']['default_billing_address_id'];
        }

        if (empty($activeShippingAddressId = $this->session->offsetGet('checkoutShippingAddressId', null))) {
            $activeShippingAddressId = $this->View()->sUserData['additional']['user']['default_shipping_address_id'];
        }

        if (!$this->isValidAddress($activeBillingAddressId) || !$this->isValidAddress($activeShippingAddressId)) {
            $this->forward('confirm');

            return;
        }

        if (!empty($this->session['sNewsletter'])) {
            $this->admin->sUpdateNewsletter(true, $this->admin->sGetUserMailById(), true);
        }

        $this->saveOrder();
        $this->saveDefaultAddresses();
        $this->resetTemporaryAddresses();

        $orderVariables = $this->session['sOrderVariables']->getArrayCopy();

        $orderVariables['sAddresses']['billing'] = $this->getOrderAddress($orderVariables['sOrderNumber'], 'billing');
        $orderVariables['sAddresses']['shipping'] = $this->getOrderAddress($orderVariables['sOrderNumber'], 'shipping');
        $orderVariables['sAddresses']['equal'] = $this->areAddressesEqual($orderVariables['sAddresses']['billing'], $orderVariables['sAddresses']['shipping']);

        $this->View()->assign($orderVariables);
    }

    /**
     * Used during the checkout process
     * Returns the user to the shop homepage
     * If the user has a noAccount account, it is automatically logged out
     */
    public function returnAction()
    {
        if ($this->View()->sUserData['additional']['user']['accountmode'] == 1) {
            Shopware()->Session()->unsetAll();
            $this->get('shopware.csrftoken_validator')->invalidateToken($this->Response());
            Shopware()->Modules()->Basket()->sRefreshBasket();
        }

        return $this->redirect(['controller' => 'index']);
    }

    /**
     * If any external payment mean chooses by customer
     * Forward to payment page after order submitting
     */
    public function paymentAction()
    {
        if (empty($this->session['sOrderVariables'])
            || $this->getMinimumCharge()
            || $this->getEsdNote()
            || $this->getDispatchNoOrder()) {
            return $this->forward('confirm');
        }

        if ($this->Request()->getParam('sNewsletter') !== null) {
            $this->session['sNewsletter'] = $this->Request()->getParam('sNewsletter') ? true : false;
        }
        if ($this->Request()->getParam('sComment') !== null) {
            $this->session['sComment'] = trim(strip_tags($this->Request()->getParam('sComment')));
        }

        if (!Shopware()->Config()->get('IgnoreAGB') && !$this->Request()->getParam('sAGB')) {
            $this->View()->sAGBError = true;

            return $this->forward('confirm');
        }

        $this->View()->assign($this->session['sOrderVariables']->getArrayCopy());
        $this->View()->sAGBError = false;

        if (empty($this->View()->sPayment['embediframe'])
            && empty($this->View()->sPayment['action'])) {
            return $this->forward('confirm');
        }

        if (!empty($this->session['sNewsletter'])) {
            $this->admin->sUpdateNewsletter(true, $this->admin->sGetUserMailById(), true);
        }

        if (!empty($this->View()->sPayment['embediframe'])) {
            $embedded = $this->View()->sPayment['embediframe'];
            $embedded = preg_replace('#^[./]+#', '', $embedded);
            $embedded .= '?sCoreId=' . Shopware()->Session()->get('sessionId');
            $embedded .= '&sAGB=1';
            $embedded .= '&__basket_signature=' . $this->persistBasket();
            $this->View()->sEmbedded = $embedded;
        } else {
            $action = explode('/', $this->View()->sPayment['action']);
            $this->redirect([
                'controller' => $action[0],
                'action' => empty($action[1]) ? 'index' : $action[1],
            ]);
        }
    }

    /**
     * Add an article to cart directly from cart / confirm view
     *
     * @param sAdd = ordernumber
     * @param sQuantity = quantity
     */
    public function addArticleAction()
    {
        $ordernumber = $this->Request()->getParam('sAdd');
        $quantity = $this->Request()->getParam('sQuantity');
        $articleID = Shopware()->Modules()->Articles()->sGetArticleIdByOrderNumber($ordernumber);

        $this->View()->sBasketInfo = $this->getInstockInfo($ordernumber, $quantity);

        if (!empty($articleID)) {
            $insertID = $this->basket->sAddArticle($ordernumber, $quantity);
            $this->View()->sArticleName = Shopware()->Modules()->Articles()->sGetArticleNameByOrderNumber($ordernumber);
            if (!empty($insertID)) {
                $basket = $this->getBasket();
                foreach ($basket['content'] as $item) {
                    if ($item['id'] == $insertID) {
                        $this->View()->sArticle = $item;
                        break;
                    }
                }
            }

            if (Shopware()->Config()->get('similarViewedShow', true)) {
                $this->View()->sCrossSimilarShown = $this->getSimilarShown($articleID);
            }

            if (Shopware()->Config()->get('alsoBoughtShow', true)) {
                $this->View()->sCrossBoughtToo = $this->getBoughtToo($articleID);
            }
        }

        if ($this->Request()->getParam('isXHR') || !empty($this->Request()->callback)) {
            $this->Request()->setParam('sTargetAction', 'ajax_add_article');
        }

        if ($this->Request()->getParam('sAddAccessories')) {
            $this->forward('addAccessories');
        } else {
            $this->forward($this->Request()->getParam('sTargetAction', 'cart'));
        }
    }

    /**
     * Add more then one article directly from cart / confirm view
     *
     * @param sAddAccessories = List of article order numbers separated by ;
     * @param sAddAccessoriesQuantity = List of article quantities separated by ;
     */
    public function addAccessoriesAction()
    {
        $this->addAccessories(
            $this->Request()->getParam('sAddAccessories'),
            $this->Request()->getParam('sAddAccessoriesQuantity')
        );

        $this->forward($this->Request()->getParam('sTargetAction', 'cart'));
    }

    /**
     * Delete an article from cart -
     *
     * @param sDelete = id from s_basket identifying the product to delete
     * Forward to cart / confirmation page after success
     */
    public function deleteArticleAction()
    {
        if ($this->Request()->getParam('sDelete')) {
            $this->basket->sDeleteArticle($this->Request()->getParam('sDelete'));
        }
        $this->forward($this->Request()->getParam('sTargetAction', 'index'));
    }

    /**
     * Add voucher to cart
     *
     * At failure view variable sVoucherError will give further information
     * At success return to cart / confirm view
     */
    public function addVoucherAction()
    {
        if ($this->Request()->isPost()) {
            $voucher = $this->basket->sAddVoucher($this->Request()->getParam('sVoucher'));
            if (!empty($voucher['sErrorMessages'])) {
                $this->View()->sVoucherError = $voucher['sErrorMessages'];
            }
        }
        $this->forward($this->Request()->getParam('sTargetAction', 'index'));
    }

    /**
     * Add premium / bonus article to cart
     *
     * @param sAddPremium - ordernumber of bonus article (defined in s_articles_premiums)
     * Return to cart / confirm page on success
     */
    public function addPremiumAction()
    {
        if ($this->Request()->isPost()) {
            if (!$this->Request()->getParam('sAddPremium')) {
                $this->View()->sBasketInfo = Shopware()->Snippets()->getNamespace()->get(
                    'CheckoutSelectPremiumVariant',
                    'Please select an option to place the required premium to the cart',
                    true
                );
            } else {
                $this->basket->sSYSTEM->_GET['sAddPremium'] = $this->Request()->getParam('sAddPremium');
                $this->basket->sInsertPremium();
            }
        }
        $this->forward($this->Request()->getParam('sTargetAction', 'index'));
    }

    /**
     * On any change on country, payment or dispatch recalculate shipping costs
     * and forward to cart / confirm view
     */
    public function calculateShippingCostsAction()
    {
        if ($this->Request()->getPost('sCountry')) {
            $this->session['sCountry'] = (int) $this->Request()->getPost('sCountry');
            $this->session['sState'] = 0;
            $this->session['sArea'] = Shopware()->Db()->fetchOne('
            SELECT areaID FROM s_core_countries WHERE id = ?
            ', [$this->session['sCountry']]);
        }

        if ($this->Request()->getPost('sPayment')) {
            $this->session['sPaymentID'] = (int) $this->Request()->getPost('sPayment');
        }

        if ($this->Request()->getPost('sDispatch')) {
            $this->session['sDispatch'] = (int) $this->Request()->getPost('sDispatch');
        }

        if ($this->Request()->getPost('sState')) {
            $this->session['sState'] = (int) $this->Request()->getPost('sState');
        }

        // We might change the shop context here so we need to initialize it again
        $this->get('shopware_storefront.context_service')->initializeShopContext();

        // We need an indicator in the view to expand the shipping costs pre-calculation on page load
        $this->View()->assign('calculateShippingCosts', true);

        $this->forward($this->Request()->getParam('sTargetAction', 'index'));
    }


    /**
     * Action to simultaneously save shipping and payment details
     */
    public function saveShippingPaymentAction()
    {
        if (!$this->Request()->isPost()) {
            return $this->forward('shippingPayment');
        }

        // Load data from request
        $dispatch = $this->Request()->getPost('sDispatch');
        $payment = $this->Request()->getPost('payment');

        // If request is ajax, we skip the validation, because the user is still editing
        if ($this->Request()->getParam('isXHR')) {
            // Save payment and shipping method data.
            $this->admin->sUpdatePayment($payment);
            $this->setDispatch($dispatch, $payment);
            $this->get('shopware_cart.cart_context_service')->initializeContext();

            return $this->forward('shippingPayment');
        }

        $sErrorFlag = [];
        $sErrorMessages = [];

        if (is_null($dispatch) && Shopware()->Config()->get('premiumshippingnoorder') === true && !$this->getDispatches($payment)) {
            $sErrorFlag['sDispatch'] = true;
            $sErrorMessages[] = Shopware()->Snippets()->getNamespace('frontend/checkout/error_messages')
                ->get('ShippingPaymentSelectShipping', 'Please select a shipping method');
        }
        if (is_null($payment)) {
            $sErrorFlag['payment'] = true;
            $sErrorMessages[] = Shopware()->Snippets()->getNamespace('frontend/checkout/error_messages')
                ->get('ShippingPaymentSelectPayment', 'Please select a payment method');
        }

        // If any basic info is missing, return error messages
        if (!empty($sErrorFlag) || !empty($sErrorMessages)) {
            $this->View()->assign('sErrorFlag', $sErrorFlag);
            $this->View()->assign('sErrorMessages', $sErrorMessages);

            return $this->forward('shippingPayment');
        }

        // Validate the payment details
        Shopware()->Modules()->Admin()->sSYSTEM->_POST['sPayment'] = $payment;
        $checkData = $this->admin->sValidateStep3();

        // Problem with the payment details, return error
        if (!empty($checkData['checkPayment']['sErrorMessages']) || empty($checkData['sProcessed'])) {
            $this->View()->assign('sErrorFlag', $checkData['checkPayment']['sErrorFlag']);
            $this->View()->assign('sErrorMessages', $checkData['checkPayment']['sErrorMessages']);

            return $this->forward('shippingPayment');
        }

        // Save payment method details db
        if ($checkData['sPaymentObject'] instanceof \ShopwarePlugin\PaymentMethods\Components\BasePaymentMethod) {
            $checkData['sPaymentObject']->savePaymentData(Shopware()->Session()->sUserId, $this->Request());
        }

        // Save the payment info
        $previousPayment = Shopware()->Modules()->Admin()->sGetUserData();
        $previousPayment = $previousPayment['additional']['user']['paymentID'];

        $previousPayment = $this->admin->sGetPaymentMeanById($previousPayment);
        if ($previousPayment['paymentTable']) {
            Shopware()->Db()->delete(
                $previousPayment['paymentTable'],
                ['userID = ?' => Shopware()->Session()->sUserId]
            );
        }

        // Save payment and shipping method data.
        $this->admin->sUpdatePayment($payment);
        $this->setDispatch($dispatch, $payment);

        $this->redirect([
            'controller' => $this->Request()->getParam('sTarget', 'checkout'),
            'action' => $this->Request()->getParam('sTargetAction', 'confirm'),
        ]);
    }

    /**
     * Get complete user-data as an array to use in view
     *
     * @return array
     */
    public function getUserData()
    {
        $system = Shopware()->System();
        $userData = $this->admin->sGetUserData();
        if (!empty($userData['additional']['countryShipping'])) {
            $system->sUSERGROUPDATA = Shopware()->Db()->fetchRow('
                SELECT * FROM s_core_customergroups
                WHERE groupkey = ?
            ', [$system->sUSERGROUP]);

            if ($this->isTaxFreeDelivery($userData)) {
                $system->sUSERGROUPDATA['tax'] = 0;
                $system->sCONFIG['sARTICLESOUTPUTNETTO'] = 1; //Old template
                Shopware()->Session()->sUserGroupData = $system->sUSERGROUPDATA;
                $userData['additional']['charge_vat'] = false;
                $userData['additional']['show_net'] = false;
                Shopware()->Session()->sOutputNet = true;
            } else {
                $userData['additional']['charge_vat'] = true;
                $userData['additional']['show_net'] = !empty($system->sUSERGROUPDATA['tax']);
                Shopware()->Session()->sOutputNet = empty($system->sUSERGROUPDATA['tax']);
            }
        }

        return $userData;
    }

    /**
     * Create temporary order in s_order_basket on confirm page
     * Used to track failed / aborted orders
     */
    public function saveTemporaryOrder()
    {
        $order = Shopware()->Modules()->Order();

        $order->sUserData = $this->View()->sUserData;
        $order->sComment = isset($this->session['sComment']) ? $this->session['sComment'] : '';
        $order->sBasketData = $this->View()->sBasket;
        $order->sAmount = $this->View()->sBasket['sAmount'];
        $order->sAmountWithTax = !empty($this->View()->sBasket['AmountWithTaxNumeric']) ? $this->View()->sBasket['AmountWithTaxNumeric'] : $this->View()->sBasket['AmountNumeric'];
        $order->sAmountNet = $this->View()->sBasket['AmountNetNumeric'];
        $order->sShippingcosts = $this->View()->sBasket['sShippingcosts'];
        $order->sShippingcostsNumeric = $this->View()->sBasket['sShippingcostsWithTax'];
        $order->sShippingcostsNumericNet = $this->View()->sBasket['sShippingcostsNet'];
        $order->dispatchId = $this->session['sDispatch'];
        $order->sNet = !$this->View()->sUserData['additional']['charge_vat'];
        $order->deviceType = $this->Request()->getDeviceType();

        $order->sDeleteTemporaryOrder();    // Delete previous temporary orders
        $order->sCreateTemporaryOrder();    // Create new temporary order
    }

    /**
     * Finish order - set some object properties to do this
     */
    public function saveOrder()
    {
        $order = Shopware()->Modules()->Order();

        $order->sUserData = $this->View()->sUserData;
        $order->sComment = isset($this->session['sComment']) ? $this->session['sComment'] : '';
        $order->sBasketData = $this->View()->sBasket;
        $order->sAmount = $this->View()->sBasket['sAmount'];
        $order->sAmountWithTax = !empty($this->View()->sBasket['AmountWithTaxNumeric']) ? $this->View()->sBasket['AmountWithTaxNumeric'] : $this->View()->sBasket['AmountNumeric'];
        $order->sAmountNet = $this->View()->sBasket['AmountNetNumeric'];
        $order->sShippingcosts = $this->View()->sBasket['sShippingcosts'];
        $order->sShippingcostsNumeric = $this->View()->sBasket['sShippingcostsWithTax'];
        $order->sShippingcostsNumericNet = $this->View()->sBasket['sShippingcostsNet'];
        $order->dispatchId = $this->session['sDispatch'];
        $order->sNet = !$this->View()->sUserData['additional']['charge_vat'];
        $order->deviceType = $this->Request()->getDeviceType();

        return $order->sSaveOrder();
    }

    /**
     * Used in ajax add cart action
     * Check availability of product and return info / error - messages
     *
     * @param string $orderNumber article order number
     * @param int    $quantity    quantity
     *
     * @return string|null
     */
    public function getInstockInfo($orderNumber, $quantity)
    {
        if (empty($orderNumber)) {
            return Shopware()->Snippets()->getNamespace('frontend')->get('CheckoutSelectVariant',
                'Please select an option to place the required product in the cart', true);
        }

        $quantity = max(1, (int) $quantity);
        $inStock = $this->getAvailableStock($orderNumber);
        $inStock['quantity'] += $quantity;

        if (empty($inStock['articleID'])) {
            return Shopware()->Snippets()->getNamespace('frontend')->get('CheckoutArticleNotFound',
                'Product could not be found.', true);
        }
        if (!empty($inStock['laststock']) || !empty(Shopware()->Config()->InstockInfo)) {
            if ($inStock['instock'] <= 0 && !empty($inStock['laststock'])) {
                return Shopware()->Snippets()->getNamespace('frontend')->get('CheckoutArticleNoStock',
                    'Unfortunately we can not deliver the desired product in sufficient quantity', true);
            } elseif ($inStock['instock'] < $inStock['quantity']) {
                $result = 'Unfortunately we can not deliver the desired product in sufficient quantity. (#0 of #1 in stock).';
                $result = Shopware()->Snippets()->getNamespace('frontend')->get('CheckoutArticleLessStock', $result,
                    true);

                return str_replace(['#0', '#1'], [$inStock['instock'], $inStock['quantity']], $result);
            }
        }

        return null;
    }

    /**
     * Get current stock from a certain product defined by $ordernumber
     * Support for multidimensional variants
     *
     * @param unknown_type $ordernumber
     *
     * @return array with article id / current basket quantity / instock / laststock
     */
    public function getAvailableStock($ordernumber)
    {
        $sql = '
            SELECT
                a.id as articleID,
                ob.quantity,
                IF(ad.instock < 0, 0, ad.instock) as instock,
                a.laststock,
                ad.ordernumber as ordernumber
            FROM s_articles a
            LEFT JOIN s_articles_details ad
            ON ad.ordernumber=?
            LEFT JOIN s_order_basket ob
            ON ob.sessionID=?
            AND ob.ordernumber=ad.ordernumber
            AND ob.modus=0
            WHERE a.id=ad.articleID
        ';
        $row = Shopware()->Db()->fetchRow($sql, [
            $ordernumber,
            Shopware()->Session()->get('sessionId'),
        ]);

        return $row;
    }

    /**
     * Get shipping costs as an array (brutto / netto) depending on selected country / payment
     *
     * @return array
     */
    public function getShippingCosts()
    {
        $country = $this->getSelectedCountry();
        $payment = $this->getSelectedPayment();
        if (empty($country) || empty($payment)) {
            return ['brutto' => 0, 'netto' => 0];
        }
        $shippingcosts = $this->admin->sGetPremiumShippingcosts($country);

        return empty($shippingcosts) ? ['brutto' => 0, 'netto' => 0] : $shippingcosts;
    }

    /**
     * Return complete basket data to view
     * Basket items / Shippingcosts / Amounts / Tax-Rates
     *
     * @return array
     */
    public function getBasket()
    {
        $this->updateArticles();

        $shippingcosts = $this->getShippingCosts();

        $basket = $this->basket->sGetBasket();

        $basket['sCurrencyId'] = Shopware()->Shop()->getCurrency()->getId();
        $basket['sCurrencyName'] = Shopware()->Shop()->getCurrency()->getCurrency();
        $basket['sShippingcostsWithTax'] = $shippingcosts['brutto'];
        $basket['sShippingcostsNet'] = $shippingcosts['netto'];
        $basket['sShippingcostsTax'] = $shippingcosts['tax'];

        if (!empty($shippingcosts['brutto'])) {
            $basket['AmountNetNumeric'] += $shippingcosts['netto'];
            $basket['AmountNumeric'] += $shippingcosts['brutto'];
            $basket['sShippingcostsDifference'] = $shippingcosts['difference']['float'];
        }
        if (!empty($basket['AmountWithTaxNumeric'])) {
            $basket['AmountWithTaxNumeric'] += $shippingcosts['brutto'];
        }
        if ((!Shopware()->System()->sUSERGROUPDATA['tax'] && Shopware()->System()->sUSERGROUPDATA['id'])) {
            $basket['sTaxRates'] = $this->getTaxRates($basket);

            $basket['sShippingcosts'] = $shippingcosts['netto'];
            $basket['sAmount'] = round($basket['AmountNetNumeric'], 2);
            $basket['sAmountTax'] = round($basket['AmountWithTaxNumeric'] - $basket['AmountNetNumeric'], 2);
            $basket['sAmountWithTax'] = round($basket['AmountWithTaxNumeric'], 2);
        } else {
            $basket['sTaxRates'] = $this->getTaxRates($basket);

            $basket['sShippingcosts'] = $shippingcosts['brutto'];
            $basket['sAmount'] = $basket['AmountNumeric'];

            $basket['sAmountTax'] = round($basket['AmountNumeric'] - $basket['AmountNetNumeric'], 2);
        }

        return $basket;
    }

    /**
     * Returns tax rates for all basket positions
     *
     * @param unknown_type $basket array returned from this->getBasket
     *
     * @return array
     */
    public function getTaxRates($basket)
    {
        $result = [];

        if (!empty($basket['sShippingcostsTax'])) {
            $basket['sShippingcostsTax'] = number_format(floatval($basket['sShippingcostsTax']), 2);

            $result[$basket['sShippingcostsTax']] = $basket['sShippingcostsWithTax'] - $basket['sShippingcostsNet'];
            if (empty($result[$basket['sShippingcostsTax']])) {
                unset($result[$basket['sShippingcostsTax']]);
            }
        } elseif ($basket['sShippingcostsWithTax']) {
            $result[number_format(floatval(Shopware()->Config()->get('sTAXSHIPPING')), 2)] = $basket['sShippingcostsWithTax'] - $basket['sShippingcostsNet'];
            if (empty($result[number_format(floatval(Shopware()->Config()->get('sTAXSHIPPING')), 2)])) {
                unset($result[number_format(floatval(Shopware()->Config()->get('sTAXSHIPPING')), 2)]);
            }
        }

        if (empty($basket['content'])) {
            ksort($result, SORT_NUMERIC);

            return $result;
        }

        foreach ($basket['content'] as $item) {
            if (!empty($item['tax_rate'])) {
            } elseif (!empty($item['taxPercent'])) {
                $item['tax_rate'] = $item['taxPercent'];
            } elseif ($item['modus'] == 2) {
                // Ticket 4842 - dynamic tax-rates
                $resultVoucherTaxMode = Shopware()->Db()->fetchOne(
                    'SELECT taxconfig FROM s_emarketing_vouchers WHERE ordercode=?
                ', [$item['ordernumber']]);
                // Old behaviour
                if (empty($resultVoucherTaxMode) || $resultVoucherTaxMode == 'default') {
                    $tax = Shopware()->Config()->get('sVOUCHERTAX');
                } elseif ($resultVoucherTaxMode == 'auto') {
                    // Automatically determinate tax
                    $tax = $this->basket->getMaxTax();
                } elseif ($resultVoucherTaxMode == 'none') {
                    // No tax
                    $tax = '0';
                } elseif (intval($resultVoucherTaxMode)) {
                    // Fix defined tax
                    $tax = Shopware()->Db()->fetchOne('
                    SELECT tax FROM s_core_tax WHERE id = ?
                    ', [$resultVoucherTaxMode]);
                }
                $item['tax_rate'] = $tax;
            } else {
                // Ticket 4842 - dynamic tax-rates
                $taxAutoMode = Shopware()->Config()->get('sTAXAUTOMODE');
                if (!empty($taxAutoMode)) {
                    $tax = $this->basket->getMaxTax();
                } else {
                    $tax = Shopware()->Config()->get('sDISCOUNTTAX');
                }
                $item['tax_rate'] = $tax;
            }

            if (empty($item['tax_rate']) || empty($item['tax'])) {
                continue;
            } // Ignore 0 % tax

            $taxKey = number_format(floatval($item['tax_rate']), 2);

            $result[$taxKey] += str_replace(',', '.', $item['tax']);
        }

        ksort($result, SORT_NUMERIC);

        return $result;
    }

    /**
     * Get similar shown products to display in ajax add dialog
     *
     * @param int $articleID
     *
     * @return array
     */
    public function getSimilarShown($articleID)
    {
        Shopware()->Modules()->Marketing()->sBlacklist = $this->basket->sGetBasketIds();

        $similarId = Shopware()->Modules()->Marketing()->sGetSimilaryShownArticles($articleID);

        $similars = [];
        if (!empty($similarId)) {
            foreach ($similarId as $similarID) {
                $temp = Shopware()->Modules()->Articles()->sGetPromotionById('fix', 0, (int) $similarID['id']);
                if (!empty($temp)) {
                    $similars[] = $temp;
                }
            }
        }

        return $similars;
    }

    /**
     * Get articles that bought in combination with last added product to
     * display on cart page
     *
     * @param int $articleID
     *
     * @return array
     */
    public function getBoughtToo($articleID)
    {
        Shopware()->Modules()->Marketing()->sBlacklist = $this->basket->sGetBasketIds();

        $alsoBoughtId = Shopware()->Modules()->Marketing()->sGetAlsoBoughtArticles($articleID);
        $alsoBoughts = [];
        if (!empty($alsoBoughtId)) {
            foreach ($alsoBoughtId as $alsoBoughtItem) {
                $temp = Shopware()->Modules()->Articles()->sGetPromotionById('fix', 0, (int) $alsoBoughtItem['id']);
                if (!empty($temp)) {
                    $alsoBoughts[] = $temp;
                }
            }
        }

        return $alsoBoughts;
    }

    /**
     * Get configured minimum charge to check in order processing
     *
     * @return bool
     */
    public function getMinimumCharge()
    {
        return $this->basket->sCheckMinimumCharge();
    }

    /**
     * Check if order is possible under current conditions (dispatch)
     *
     * @return bool
     */
    public function getDispatchNoOrder()
    {
        return !empty(Shopware()->Config()->PremiumShippingNoOrder) && (empty($this->session['sDispatch']) || empty($this->session['sCountry']));
    }

    /**
     * Get all premium products that are configured and available for this order
     *
     * @return array
     */
    public function getPremiums()
    {
        $sql = 'SELECT `id` FROM `s_order_basket` WHERE `sessionID`=? AND `modus`=1';
        $result = Shopware()->Db()->fetchOne($sql, [Shopware()->Session()->get('sessionId')]);
        if (!empty($result)) {
            return [];
        }

        return Shopware()->Modules()->Marketing()->sGetPremiums();
    }

    /**
     * Check if any electronically distribution product is in basket
     *
     * @return bool
     */
    public function getEsdNote()
    {
        $payment = empty($this->View()->sUserData['additional']['payment']) ? $this->session['sOrderVariables']['sUserData']['additional']['payment'] : $this->View()->sUserData['additional']['payment'];

        return $this->basket->sCheckForESD() && !$payment['esdactive'];
    }

    /**
     * Check if a custom inquiry possibility should displayed on cart page
     * Compare configured inquirevalue with current amount
     *
     * @return bool
     */
    public function getInquiry()
    {
        if (Shopware()->Config()->get('sINQUIRYVALUE')) {
            $factor = Shopware()->System()->sCurrency['factor'] ? 1 : Shopware()->System()->sCurrency['factor'];
            $value = Shopware()->Config()->get('sINQUIRYVALUE') * $factor;
            if ((!Shopware()->System()->sUSERGROUPDATA['tax'] && Shopware()->System()->sUSERGROUPDATA['id'])) {
                $amount = $this->View()->sBasket['AmountWithTaxNumeric'];
            } else {
                $amount = $this->View()->sBasket['AmountNumeric'];
            }
            if (!empty($amount) && $amount >= $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get link to inquiry form if getInquiry returend true
     *
     * @return string
     */
    public function getInquiryLink()
    {
        return Shopware()->Config()->get('sBASEFILE') . '?sViewport=support&sFid=' . Shopware()->Config()->get('sINQUIRYID') . '&sInquiry=basket';
    }

    /**
     * Get all countries from database via sAdmin object
     *
     * @return array list of countries
     */
    public function getCountryList()
    {
        return $this->admin->sGetCountryList();
    }

    /**
     * Get all dispatches available in selected country from sAdmin object
     *
     * @param null $paymentId
     *
     * @return array list of dispatches
     */
    public function getDispatches($paymentId = null)
    {
        $country = $this->getSelectedCountry();
        $state = $this->getSelectedState();
        if (empty($country)) {
            return false;
        }
        $stateId = !empty($state['id']) ? $state['id'] : null;

        return $this->admin->sGetPremiumDispatches($country['id'], $paymentId, $stateId);
    }

    /**
     * Returns all available payment methods from sAdmin object
     *
     * @return array list of payment methods
     */
    public function getPayments()
    {
        return $this->admin->sGetPaymentMeans();
    }

    /**
     * Get current selected country - if no country is selected, choose first one from list
     * of available countries
     *
     * @return array with country information
     */
    public function getSelectedCountry()
    {
        if (!empty($this->View()->sUserData['additional']['countryShipping'])) {
            $this->session['sCountry'] = (int) $this->View()->sUserData['additional']['countryShipping']['id'];
            $this->session['sArea'] = (int) $this->View()->sUserData['additional']['countryShipping']['areaID'];

            return $this->View()->sUserData['additional']['countryShipping'];
        }
        $countries = $this->getCountryList();
        if (empty($countries)) {
            unset($this->session['sCountry']);

            return false;
        }
        $country = reset($countries);
        $this->session['sCountry'] = (int) $country['id'];
        $this->session['sArea'] = (int) $country['areaID'];
        $this->View()->sUserData['additional']['countryShipping'] = $country;

        return $country;
    }

    /**
     * Get current selected country - if no country is selected, choose first one from list
     * of available countries
     *
     * @return array with country information
     */
    public function getSelectedState()
    {
        if (!empty($this->View()->sUserData['additional']['stateShipping'])) {
            $this->session['sState'] = (int) $this->View()->sUserData['additional']['stateShipping']['id'];

            return $this->View()->sUserData['additional']['stateShipping'];
        }

        return ['id' => $this->session['sState']];
    }

    /**
     * Get selected payment or do payment mean selection automatically
     *
     * @return array
     */
    public function getSelectedPayment()
    {
        $paymentMethods = $this->getPayments();

        if (!empty($this->View()->sUserData['additional']['payment'])) {
            $payment = $this->View()->sUserData['additional']['payment'];
        } elseif (!empty($this->session['sPaymentID'])) {
            $payment = $this->admin->sGetPaymentMeanById($this->session['sPaymentID'], $this->View()->sUserData);
        }

        if ($payment && !$this->checkPaymentAvailability($payment, $paymentMethods)) {
            $payment = null;
        }

        $paymentClass = $this->admin->sInitiatePaymentClass($payment);
        if ($payment && $paymentClass instanceof \ShopwarePlugin\PaymentMethods\Components\BasePaymentMethod) {
            $data = $paymentClass->getCurrentPaymentDataAsArray(Shopware()->Session()->sUserId);
            $payment['validation'] = $paymentClass->validate($data);
            if (!empty($data)) {
                $payment['data'] = $data;
            }
        }

        if (!empty($payment)) {
            return $payment;
        }

        if (empty($paymentMethods)) {
            unset($this->session['sPaymentID']);

            return false;
        }

        $payment = $this->getDefaultPaymentMethod($paymentMethods);

        $this->session['sPaymentID'] = (int) $payment['id'];
        $this->front->Request()->setPost('sPayment', (int) $payment['id']);
        $this->admin->sUpdatePayment();

        //if customer logged in and payment switched to fallback, display cart notice. Otherwise anonymous customers will see the message too
        if (Shopware()->Session()->sUserId) {
            $this->flagPaymentBlocked();
        }

        return $payment;
    }

    /**
     * Get selected dispatch or select a default dispatch
     *
     * @return bool|array
     */
    public function getSelectedDispatch()
    {
        if (empty($this->session['sCountry'])) {
            return false;
        }

        $dispatches = $this->admin->sGetPremiumDispatches($this->session['sCountry'], null, $this->session['sState']);
        if (empty($dispatches)) {
            unset($this->session['sDispatch']);

            return false;
        }

        foreach ($dispatches as $dispatch) {
            if ($dispatch['id'] == $this->session['sDispatch']) {
                return $dispatch;
            }
        }
        $dispatch = reset($dispatches);
        $this->session['sDispatch'] = (int) $dispatch['id'];

        return $dispatch;
    }

    /**
     * Set the provided dispatch method
     *
     * @param $dispatchId ID of the dispatch method to set
     * @param int|null $paymentId Payment id to validate
     *
     * @return int set dispatch method id
     */
    public function setDispatch($dispatchId, $paymentId = null)
    {
        $supportedDispatches = $this->getDispatches($paymentId);

        // Iterate over supported dispatches, look for the provided one
        foreach ($supportedDispatches as $dispatch) {
            if ($dispatch['id'] == $dispatchId) {
                $this->session['sDispatch'] = $dispatchId;

                return $dispatchId;
            }
        }

        // If it was not found, we fallback to the default (head of supported)
        $defaultDispatch = array_shift($supportedDispatches);
        $this->session['sDispatch'] = $defaultDispatch['id'];

        return $this->session['sDispatch'];
    }



    /**
     * Ajax add article cart action
     *
     * This action is a lightweight way to add an article by the passed
     * article order number and quantity.
     *
     * The order number is expected to get passed by the 'sAdd' parameter
     * This quantity is expected to get passed by the 'sQuantity' parameter.
     *
     * After the article was added to the basket, the whole cart content will be returned.
     */
    public function ajaxAddArticleCartAction()
    {
        $orderNumber = $this->Request()->getParam('sAdd');
        $quantity = $this->Request()->getParam('sQuantity');

        $this->View()->assign(
            'basketInfoMessage',
            $this->getInstockInfo($orderNumber, $quantity)
        );

        if ($this->Request()->get('sAddAccessories')) {
            $this->addAccessories(
                $this->Request()->getParam('sAddAccessories'),
                $this->Request()->getParam('sAddAccessoriesQuantity')
            );
        }

        $this->basket->sAddArticle($orderNumber, $quantity);

        $this->forward('ajaxCart');
    }

    /**
     * Ajax delete article action
     *
     * This action is a lightweight way to delete an article by the passed
     * basket item id.
     *
     * This id is expected to get passed by the 'sDelete' parameter.
     *
     * After the article was removed from the basket, the whole cart content will be returned.
     */
    public function ajaxDeleteArticleCartAction()
    {
        $itemId = $this->Request()->getParam('sDelete');

        if ($itemId) {
            $this->basket->sDeleteArticle($itemId);
        }

        $this->forward('ajaxCart');
    }

    /**
     * Sets a temporary session variable which holds an address for the current order
     */
    public function setAddressAction()
    {
        $this->View()->loadTemplate('');
        $target = $this->Request()->getParam('target', 'shipping');
        $sessionKey = $target == 'shipping' ? 'checkoutShippingAddressId' : 'checkoutBillingAddressId';

        $this->session->offsetSet($sessionKey, $this->Request()->getParam('addressId', null));

        if ($target === 'both') {
            $this->session->offsetSet('checkoutShippingAddressId', $this->Request()->getParam('addressId', null));
            $this->session->offsetSet('checkoutBillingAddressId', $this->Request()->getParam('addressId', null));
        }
    }

    /**
     * Validates if the provided customer should get a tax free delivery
     *
     * @param array $userData
     *
     * @return bool
     */
    protected function isTaxFreeDelivery($userData)
    {
        if (!empty($userData['additional']['countryShipping']['taxfree'])) {
            return true;
        }

        if (empty($userData['additional']['countryShipping']['taxfree_ustid'])) {
            return false;
        }

        return !empty($userData['shippingaddress']['ustid']);
    }

    /**
     * @param $basket
     * @param Request $request
     *
     * @throws Exception
     *
     * @return array
     */
    private function getInvalidAgreements($basket, Request $request)
    {
        $errors = [];

        if (!$this->container->get('config')->get('IgnoreAGB') && !$this->Request()->getParam('sAGB')) {
            $errors['agbError'] = true;
        }

        $esdAgreement = $request->getParam('esdAgreementChecked');
        if ($this->container->get('config')->get('showEsdWarning')
            && $this->basketHasEsdArticles($basket)
            && empty($esdAgreement)
        ) {
            $errors['esdError'] = true;
        }

        $serviceChecked = $request->getParam('serviceAgreementChecked');
        if ($this->basketHasServiceArticles($basket) && empty($serviceChecked)) {
            $errors['serviceError'] = true;
        }

        return $errors;
    }

    /**
     * checks if the current user selected an available payment method
     *
     * @param array $currentPayment
     * @param array $payments
     *
     * @return bool
     */
    private function checkPaymentAvailability($currentPayment, $payments)
    {
        foreach ($payments as $availablePayment) {
            if ($availablePayment['id'] === $currentPayment['id']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string|array $accessories
     * @param array        $quantities
     */
    private function addAccessories($accessories, $quantities)
    {
        if (is_string($accessories)) {
            $accessories = explode(';', $accessories);
        }

        if (empty($accessories) || !is_array($accessories)) {
            return;
        }

        foreach ($accessories as $key => $accessory) {
            try {
                $quantity = 1;
                if (!empty($quantities[$key])) {
                    $quantity = intval($quantities[$key]);
                }

                $this->basket->sAddArticle($accessory, $quantity);
            } catch (Exception $e) {
            }
        }
    }

    /**
     * Helper function that checks whether or not the given basket has an esd article in it.
     *
     * @param array $basket
     *
     * @return bool
     */
    private function basketHasEsdArticles($basket)
    {
        if (!isset($basket['content'])) {
            return false;
        }

        foreach ($basket['content'] as $article) {
            if ($article['esd']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Helper function that iterates through the basket articles.
     * It checks if an article is a service article by comparing its attributes
     * with the plugin config serviceAttrField value.
     *
     * @param array $basket
     *
     * @return bool
     */
    private function basketHasServiceArticles($basket)
    {
        $config = Shopware()->Config();

        if (!$config->offsetExists('serviceAttrField')) {
            return false;
        }

        $attrName = $config->serviceAttrField;
        if (empty($attrName) || !isset($basket['content'])) {
            return false;
        }

        foreach ($basket['content'] as $article) {
            $serviceAttr = $article['additional_details'][$attrName];

            if ($serviceAttr && $serviceAttr != 'false') {
                return true;
            }
        }

        return false;
    }

    /**
     * Helper function that iterates through the basket articles.
     * If checks if the basket has a normal article e.g. not an esd article
     * and not a article with the service attribute is set to true.
     *
     * @param array $basket
     *
     * @return bool
     */
    private function basketHasMixedArticles($basket)
    {
        $config = Shopware()->Config();
        $attrName = $config->serviceAttrField;

        if (!isset($basket['content'])) {
            return false;
        }

        foreach ($basket['content'] as $article) {
            if ($article['modus'] == 4 || $article['esd']) {
                continue;
            }

            $serviceAttr = $article['additional_details'][$attrName];
            if (empty($attrName) || ($serviceAttr && $serviceAttr != 'false')) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * Handles payment method validation fail on checkout
     * Redirects the user to the payment edit page
     */
    private function onPaymentMethodValidationFail()
    {
        $target = ['controller' => 'checkout', 'action' => 'shippingPayment'];
        $this->redirect($target);
    }

    /**
     * Selects the default payment method defined in the backend. If no payment method is defined,
     * the first payment method of the provided list will be returned.
     *
     * @param array $paymentMethods
     *
     * @return array
     */
    private function getDefaultPaymentMethod(array $paymentMethods)
    {
        $payment = null;

        foreach ($paymentMethods as $paymentMethod) {
            if ($paymentMethod['id'] == Shopware()->Config()->offsetGet('defaultpayment')) {
                $payment = $paymentMethod;
                break;
            }
        }

        if (!$payment) {
            $payment = reset($paymentMethods);
        }

        return $payment;
    }

    /**
     * Resets the temporary session address ids back to default
     */
    private function resetTemporaryAddresses()
    {
        $this->session->offsetUnset('checkoutBillingAddressId');
        $this->session->offsetUnset('checkoutShippingAddressId');
    }

    /**
     * Sets the default addresses for the user if he decided to use the temporary addresses as new default
     */
    private function saveDefaultAddresses()
    {
        $billingId = $this->session->offsetGet('checkoutBillingAddressId', false);
        $shippingId = $this->session->offsetGet('checkoutShippingAddressId', false);
        $setBoth = $this->Request()->getPost('setAsDefaultAddress', false);

        if (!$this->Request()->getPost('setAsDefaultBillingAddress') && !$setBoth) {
            $billingId = false;
        }

        if (!$this->Request()->getPost('setAsDefaultShippingAddress') && !$setBoth) {
            $shippingId = false;
        }

        if ($billingId && $billingId != $this->View()->sUserData['additional']['user']['default_billing_address_id']) {
            $address = $this->get('models')
                ->getRepository(Address::class)
                ->getOneByUser(
                    $billingId,
                    $this->View()->sUserData['additional']['user']['id']
                );

            $this->get('shopware_account.address_service')->setDefaultBillingAddress($address);
        }

        if ($shippingId && $shippingId != $this->View()->sUserData['additional']['user']['default_shipping_address_id']) {
            $address = $this->get('models')
                ->getRepository(Address::class)
                ->getOneByUser(
                    $shippingId,
                    $this->View()->sUserData['additional']['user']['id']
                );

            $this->get('shopware_account.address_service')->setDefaultShippingAddress($address);
        }
    }

    /**
     * Validates the given address id with current shop configuration
     *
     * @param $addressId
     *
     * @return bool
     */
    private function isValidAddress($addressId)
    {
        $address = $this->get('models')->find(Address::class, $addressId);

        return $this->get('shopware_account.address_validator')->isValid($address);
    }

    /**
     * @param int    $orderNumber
     * @param string $source
     *
     * @return array
     */
    private function getOrderAddress($orderNumber, $source)
    {
        /** @var \Doctrine\DBAL\Query\QueryBuilder $builder */
        $builder = $this->get('dbal_connection')->createQueryBuilder();
        $context = $this->get('shopware_storefront.context_service')->getShopContext();

        $sourceTable = $source === 'billing' ? 's_order_billingaddress' : 's_order_shippingaddress';

        $address = $builder->select(['address.*'])
            ->from($sourceTable, 'address')
            ->join('address', 's_order', '', 'address.orderID = s_order.id AND s_order.ordernumber = :orderNumber')
            ->setParameter('orderNumber', $orderNumber)
            ->execute()
            ->fetch();

        $countryStruct = $this->get('shopware_storefront.country_gateway')->getCountry($address['countryID'], $context);
        $stateStruct = $this->get('shopware_storefront.country_gateway')->getState($address['stateID'], $context);

        $address['country'] = json_decode(json_encode($countryStruct), true);
        $address['state'] = json_decode(json_encode($stateStruct), true);
        $address['attribute'] = $this->get('shopware_attribute.data_loader')->load($sourceTable . '_attributes', $address['id']);

        return $address;
    }

    /**
     * @param array $addressA
     * @param array $addressB
     *
     * @return bool
     */
    private function areAddressesEqual(array $addressA, array $addressB)
    {
        $unset = ['id', 'customernumber', 'phone', 'ustid'];
        foreach ($unset as $key) {
            unset($addressA[$key], $addressB[$key]);
        }

        return count(array_diff($addressA, $addressB)) == 0;
    }

    /**
     * Updates all articles in the basket
     */
    private function updateArticles()
    {
        $query = $this->container->get('dbal_connection')->createQueryBuilder();
        $query->select(['id', 'quantity']);
        $query->from('s_order_basket', 'basket');
        $query->where('basket.modus = 0');
        $query->andWhere('basket.sessionID = :sessionId');
        $query->setParameter(':sessionId', Shopware()->Session()->get('sessionId'));

        $articles = $query->execute()->fetchAll(PDO::FETCH_KEY_PAIR);
        foreach ($articles as $id => $quantity) {
            $this->basket->sUpdateArticle($id, $quantity);
        }
    }

    /**
     * @return string
     */
    private function persistBasket()
    {
        /** @var BasketSignatureGeneratorInterface $generator */
        $generator = $this->get('basket_signature_generator');
        $basket = $this->session->offsetGet('sOrderVariables')->getArrayCopy();
        $signature = $generator->generateSignature($basket, $this->session->get('sUserId'));

        /** @var BasketPersister $persister */
        $persister = $this->get('basket_persister');
        $persister->persist($signature, $basket);

        return $signature;
    }
}
