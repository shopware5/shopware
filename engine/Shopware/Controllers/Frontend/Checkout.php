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
use Shopware\Components\BasketSignature\Basket;
use Shopware\Components\BasketSignature\BasketPersister;
use Shopware\Components\BasketSignature\BasketSignatureGeneratorInterface;
use Shopware\Components\Cart\Struct\DiscountContext;
use Shopware\Components\CSRFGetProtectionAware;
use Shopware\Models\Customer\Address;

class Shopware_Controllers_Frontend_Checkout extends Enlight_Controller_Action implements CSRFGetProtectionAware
{
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
     * @return array
     */
    public function getCSRFProtectedActions()
    {
        return [
            'ajaxAddArticle',
            'addArticle',
            'ajaxAddArticleCart',
            'ajaxDeleteArticle',
            'ajaxDeleteArticleCart',
            'deleteArticle',
            'addAccessories',
            'changeQuantity',
            'addPremium',
            'setAddress',
        ];
    }

    /**
     * Pre dispatch method
     */
    public function preDispatch()
    {
        $events = Shopware()->Container()->get('events');
        $events->addListener('Shopware_Modules_Admin_Payment_Fallback', [$this, 'flagPaymentBlocked']);

        $this->View()->setScope(Enlight_Template_Manager::SCOPE_PARENT);

        $this->View()->assign('sUserLoggedIn', $this->admin->sCheckUser());
        $this->View()->assign('sUserData', $this->getUserData());
    }

    /**
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Called if the sAdmin resets the selected customer payment to the shop preset
     */
    public function flagPaymentBlocked()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be protected with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

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

        if (($messageType = $this->Request()->query->get('removeMessage')) && $messageType === 'voucher') {
            $this->session->offsetUnset('sBasketVoucherRemovedInCart');
        }

        if ($this->session->offsetExists('sBasketVoucherRemovedInCart')) {
            $this->View()->assign('sBasketVoucherRemovedInCart', true);
        }
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
     * Read all data from objects / models that are required in cart view
     * (User-Data / Payment-Data / Basket-Data etc.)
     */
    public function cartAction()
    {
        $this->View()->assign('sCountry', $this->getSelectedCountry());
        $this->View()->assign('sPayment', $this->getSelectedPayment());
        $this->View()->assign('sDispatch', $this->getSelectedDispatch());
        $this->View()->assign('sCountryList', $this->getCountryList());
        $this->View()->assign('sPayments', $this->getPayments());
        $this->View()->assign('sDispatches', $this->getDispatches());
        $this->View()->assign('sDispatchNoOrder', $this->View()->sUserData['additional']['user']['accountmode'] == 0 && $this->getDispatchNoOrder());
        $this->View()->assign('sState', $this->getSelectedState());

        $this->View()->assign('sUserData', $this->getUserData());
        $this->View()->assign('sBasket', $this->getBasket());
        $this->View()->assign('sInvalidCartItems', $this->getInvalidProducts($this->View()->getAssign('sBasket')));

        $this->View()->assign('sShippingcosts', $this->View()->sBasket['sShippingcosts']);
        $this->View()->assign('sShippingcostsDifference', $this->View()->sBasket['sShippingcostsDifference']);
        $this->View()->assign('sAmount', $this->View()->sBasket['sAmount']);
        $this->View()->assign('sAmountWithTax', $this->View()->sBasket['sAmountWithTax']);
        $this->View()->assign('sAmountTax', $this->View()->sBasket['sAmountTax']);
        $this->View()->assign('sAmountNet', $this->View()->sBasket['AmountNetNumeric']);

        $this->View()->assign('sMinimumSurcharge', $this->getMinimumCharge());
        $this->View()->assign('sPremiums', $this->getPremiums());

        $this->View()->assign('sInquiry', $this->getInquiry());
        $this->View()->assign('sInquiryLink', $this->getInquiryLink());

        $this->View()->assign('sTargetAction', 'cart');
    }

    /**
     * Mostly equivalent to cartAction
     * Get user, basket and payment data for view assignment
     * Create temporary entry in s_order table
     * Check some conditions (minimum charge)
     */
    public function confirmAction()
    {
        if (empty($this->View()->sUserLoggedIn)) {
            return $this->forward(
                'login',
                'account',
                null,
                ['sTarget' => 'checkout', 'sTargetAction' => 'confirm', 'showNoAccount' => true]
            );
        } elseif ($this->basket->sCountBasket() < 1) {
            return $this->forward('cart');
        }

        $this->View()->assign('sCountry', $this->getSelectedCountry());
        $this->View()->assign('sState', $this->getSelectedState());

        $payment = $this->getSelectedPayment();
        if (array_key_exists('validation', $payment) && !empty($payment['validation'])) {
            $this->onPaymentMethodValidationFail();

            return;
        }

        $this->View()->assign('sPayment', $payment);

        /** @var array<string, mixed> $userData */
        $userData = $this->View()->sUserData;
        $userData['additional']['payment'] = $this->View()->sPayment;
        $this->View()->assign('sUserData', $userData);

        $this->View()->assign('sDispatch', $this->getSelectedDispatch());
        $this->View()->assign('sPayments', $this->getPayments());
        $this->View()->assign('sDispatches', $this->getDispatches());

        $this->View()->assign('sBasket', $this->getBasket());
        $this->View()->assign('sInvalidCartItems', $this->getInvalidProducts($this->View()->getAssign('sBasket')));

        $this->View()->assign('sLaststock', $this->basket->sCheckBasketQuantities());
        $this->View()->assign('sShippingcosts', $this->View()->sBasket['sShippingcosts']);
        $this->View()->assign('sShippingcostsDifference', $this->View()->sBasket['sShippingcostsDifference']);
        $this->View()->assign('sAmount', $this->View()->sBasket['sAmount']);
        $this->View()->assign('sAmountWithTax', $this->View()->sBasket['sAmountWithTax']);
        $this->View()->assign('sAmountTax', $this->View()->sBasket['sAmountTax']);
        $this->View()->assign('sAmountNet', $this->View()->sBasket['AmountNetNumeric']);

        $this->View()->assign('sPremiums', $this->getPremiums());

        $this->View()->assign('sNewsletter', isset($this->session['sNewsletter']) ? $this->session['sNewsletter'] : null);
        $this->View()->assign('sComment', isset($this->session['sComment']) ? $this->session['sComment'] : null);

        $this->View()->assign('sShowEsdNote', $this->getEsdNote());
        $this->View()->assign('sDispatchNoOrder', $this->getDispatchNoOrder());
        $this->View()->assign('sRegisterFinished', !empty($this->session['sRegisterFinished']));

        $this->saveTemporaryOrder();

        if ($this->getMinimumCharge() || count($this->View()->sBasket['content']) <= 0 || $this->View()->getAssign('sInvalidCartItems')) {
            return $this->forward('cart');
        }

        $sOrderVariables = $this->View()->getAssign();
        $sOrderVariables['sBasketView'] = $sOrderVariables['sBasket'];
        $sOrderVariables['sBasket'] = $this->getBasket(false);

        $this->session['sOrderVariables'] = new ArrayObject($sOrderVariables, ArrayObject::ARRAY_AS_PROPS);

        $agbChecked = $this->Request()->getParam('sAGB');
        if (!empty($agbChecked)) {
            $this->View()->assign('sAGBChecked', true);
        }

        $this->View()->assign('sTargetAction', 'confirm');

        $this->View()->assign('hasMixedArticles', $this->basketHasMixedProducts($this->View()->sBasket));
        $this->View()->assign('hasServiceArticles', $this->basketHasServiceProducts($this->View()->sBasket));

        if (Shopware()->Config()->get('showEsdWarning')) {
            $this->View()->assign('hasEsdArticles', $this->basketHasEsdProducts($this->View()->sBasket));
        }

        $serviceChecked = $this->Request()->getParam('serviceAgreementChecked');
        if (!empty($serviceChecked)) {
            $this->View()->assign('serviceAgreementChecked', true);
        }

        $esdChecked = $this->Request()->getParam('esdAgreementChecked');
        if (!empty($esdChecked)) {
            $this->View()->assign('esdAgreementChecked', true);
        }

        $errors = $this->Request()->getParam('agreementErrors');
        if (!empty($errors)) {
            $this->View()->assign('agreementErrors', $errors);
        }

        $voucherErrors = $this->Request()->getParam('voucherErrors');
        if (!empty($voucherErrors)) {
            $this->View()->assign('sVoucherError', $voucherErrors);
        }

        /** @var array<array<array>> $activeBillingAddressId */
        if (empty($activeBillingAddressId = $this->session->offsetGet('checkoutBillingAddressId'))) {
            $activeBillingAddressId = $userData['additional']['user']['default_billing_address_id'];
        }
        /** @var array<array<array>> $activeShippingAddressId */
        if (empty($activeShippingAddressId = $this->session->offsetGet('checkoutShippingAddressId'))) {
            $activeShippingAddressId = $userData['additional']['user']['default_shipping_address_id'];
        }

        $activeBillingAddressId = (int) $activeBillingAddressId;
        $activeShippingAddressId = (int) $activeShippingAddressId;

        $this->View()->assign('activeBillingAddressId', $activeBillingAddressId);
        $this->View()->assign('activeShippingAddressId', $activeShippingAddressId);

        $this->View()->assign('invalidBillingAddress', !$this->isValidAddress($activeBillingAddressId, $activeShippingAddressId === $activeBillingAddressId));
        $this->View()->assign('invalidShippingAddress', !$this->isValidAddress($activeShippingAddressId, true));
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

            if (empty($order)) {
                if ($this->Request()->isGet()) {
                    return $this->forward('confirm');
                }
            } else {
                $this->View()->assign($order);
                $orderVariables = $this->session['sOrderVariables']->getArrayCopy();

                if (!empty($orderVariables['sOrderNumber'])) {
                    $orderVariables['sAddresses']['billing'] = $this->getOrderAddress($orderVariables['sOrderNumber'], 'billing');
                    $orderVariables['sAddresses']['shipping'] = $this->getOrderAddress($orderVariables['sOrderNumber'], 'shipping');
                    $orderVariables['sAddresses']['equal'] = $this->areAddressesEqual($orderVariables['sAddresses']['billing'], $orderVariables['sAddresses']['shipping']);
                }

                $this->View()->assign($orderVariables);

                if ($this->View()->getAssign('sBasketView')) {
                    $this->View()->assign('sBasket', $this->View()->getAssign('sBasketView'));
                    $this->View()->clearAssign('sBasketView');
                }

                $this->View()->assign('sInvalidCartItems', $this->getInvalidProducts($this->View()->getAssign('sBasket')));

                return;
            }
        }

        if (empty($this->session['sOrderVariables']) || $this->getMinimumCharge() || $this->getEsdNote() || $this->getDispatchNoOrder() || $this->View()->getAssign('sInvalidCartItems')) {
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
        if ($this->View()->getAssign('sBasketView')) {
            $this->View()->assign('sBasket', $this->View()->getAssign('sBasketView'));
            $this->View()->clearAssign('sBasketView');
        }

        $this->View()->assign('sInvalidCartItems', $this->getInvalidProducts($this->View()->getAssign('sBasket')));

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
            $this->View()->assign('sAGBError', array_key_exists('agbError', $agreements));

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

        if (empty($activeBillingAddressId = $this->session->offsetGet('checkoutBillingAddressId'))) {
            $activeBillingAddressId = $this->View()->sUserData['additional']['user']['default_billing_address_id'];
        }

        if (empty($activeShippingAddressId = $this->session->offsetGet('checkoutShippingAddressId'))) {
            $activeShippingAddressId = $this->View()->sUserData['additional']['user']['default_shipping_address_id'];
        }

        $activeBillingAddressId = (int) $activeBillingAddressId;
        $activeShippingAddressId = (int) $activeShippingAddressId;

        if (!$this->isValidAddress($activeBillingAddressId, false) || !$this->isValidAddress($activeShippingAddressId, true)) {
            $this->forward('confirm');

            return;
        }

        if (!empty($this->session['sNewsletter'])) {
            $this->admin->sUpdateNewsletter(true, $this->admin->sGetUserMailById(), true);
        }

        if ($this->Request()->isGet()) {
            return $this->forward('confirm');
        }

        $this->updateCurrencyDependencies($basket['sCurrencyId']);

        $this->saveOrder();
        $this->saveDefaultAddresses();
        $this->resetTemporaryAddresses();

        $this->session->offsetUnset('sComment');

        $orderVariables = $this->session['sOrderVariables']->getArrayCopy();

        $orderVariables['sAddresses']['billing'] = $this->getOrderAddress($orderVariables['sOrderNumber'], 'billing');
        $orderVariables['sAddresses']['shipping'] = $this->getOrderAddress($orderVariables['sOrderNumber'], 'shipping');
        $orderVariables['sAddresses']['equal'] = $this->areAddressesEqual($orderVariables['sAddresses']['billing'], $orderVariables['sAddresses']['shipping']);

        $this->View()->assign($orderVariables);

        if ($this->View()->getAssign('sBasketView')) {
            $this->View()->assign('sBasket', $this->View()->getAssign('sBasketView'));
            $this->View()->clearAssign('sBasketView');
        }
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
            $this->View()->assign('sAGBError', true);

            return $this->forward('confirm');
        }

        $this->View()->assign($this->session['sOrderVariables']->getArrayCopy());
        $this->View()->assign('sAGBError', false);

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
            $this->View()->assign('sEmbedded', $embedded);
        } else {
            $action = explode('/', $this->View()->sPayment['action']);
            $this->redirect([
                'controller' => $action[0],
                'action' => empty($action[1]) ? 'index' : $action[1],
            ]);
        }
    }

    /**
     * Add an product to cart directly from cart / confirm view
     *
     * request param "sAdd" = ordernumber
     * request param "sQuantity" = quantity
     *
     * @throws LogicException
     */
    public function addArticleAction()
    {
        if (strtolower($this->Request()->getMethod()) !== 'post') {
            throw new \LogicException('This action only admits post requests');
        }

        $ordernumber = trim($this->Request()->getParam('sAdd'));
        $quantity = (int) $this->Request()->getParam('sQuantity');
        $productId = Shopware()->Modules()->Articles()->sGetArticleIdByOrderNumber($ordernumber);

        $this->View()->assign('sBasketInfo', $this->getInstockInfo($ordernumber, $quantity));

        if (!empty($productId)) {
            $insertId = $this->basket->sAddArticle($ordernumber, $quantity);
            $this->View()->assign('sArticleName', Shopware()->Modules()->Articles()->sGetArticleNameByOrderNumber($ordernumber));
            if (!empty($insertId)) {
                $basket = $this->getBasket();
                foreach ($basket['content'] as $item) {
                    if ($item['id'] == $insertId) {
                        $this->View()->assign('sArticle', $item);
                        break;
                    }
                }
            }

            if (Shopware()->Config()->get('similarViewedShow', true)) {
                $this->View()->assign('sCrossSimilarShown', $this->getSimilarShown($productId));
            }

            if (Shopware()->Config()->get('alsoBoughtShow', true)) {
                $this->View()->assign('sCrossBoughtToo', $this->getBoughtToo($productId));
            }
        }

        if ($this->Request()->getParam('isXHR') || !empty($this->Request()->callback)) {
            $this->Request()->setParam('sTargetAction', 'ajax_add_article');
        }

        if ($this->Request()->getParam('sAddAccessories')) {
            $this->forward('addAccessories');
        } else {
            if ($this->Request()->isXmlHttpRequest()) {
                $this->forward($this->Request()->getParam('sTargetAction', 'cart'));
            } else {
                $this->redirect(['action' => $this->Request()->getParam('sTargetAction', 'cart')]);
            }
        }
    }

    /**
     * Add more then one product directly from cart / confirm view
     *
     * request param "sAddAccessories" = List of product order numbers separated by ;
     * request param "sAddAccessoriesQuantity" = List of product quantities separated by ;
     */
    public function addAccessoriesAction()
    {
        $this->addAccessories(
            $this->Request()->getParam('sAddAccessories'),
            $this->Request()->getParam('sAddAccessoriesQuantity')
        );

        $this->redirect(['action' => $this->Request()->getParam('sTargetAction', 'cart')]);
    }

    /**
     * Delete a product from cart -
     *
     * request param "sDelete" = id from s_basket identifying the product to delete
     * Forward to cart / confirmation page after success
     */
    public function deleteArticleAction()
    {
        if ($this->Request()->getParam('sDelete')) {
            $this->basket->sDeleteArticle($this->Request()->getParam('sDelete'));
        }
        $this->redirect(['action' => $this->Request()->getParam('sTargetAction', 'index')]);
    }

    /**
     * Change quantity of a certain product
     *
     * request param "sArticle" = The product to update
     * request param "sQuantity" = new quantity
     * Forward to cart / confirm view after success
     */
    public function changeQuantityAction()
    {
        if ($this->Request()->getParam('sArticle') && $this->Request()->getParam('sQuantity')) {
            $this->View()->assign('sBasketInfo', $this->basket->sUpdateArticle((int) $this->Request()->getParam('sArticle'), (int) $this->Request()->getParam('sQuantity')));
        }
        $this->redirect(['action' => $this->Request()->getParam('sTargetAction', 'index')]);
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
                $this->View()->assign('sVoucherError', $voucher['sErrorMessages']);
            }
        }
        $this->forward($this->Request()->getParam('sTargetAction', 'index'));
    }

    /**
     * Add premium / bonus product to cart
     *
     * request param "sAddPremium" - ordernumber of bonus product (defined in s_articles_premiums)
     * Return to cart / confirm page on success
     */
    public function addPremiumAction()
    {
        if ($this->Request()->isPost()) {
            if (!$this->Request()->getParam('sAddPremium')) {
                $this->View()->assign('sBasketInfo', Shopware()->Snippets()->getNamespace()->get(
                    'CheckoutSelectPremiumVariant',
                    'Please select an option to place the required premium to the cart',
                    true
                ));
            } else {
                $this->basket->sSYSTEM->_GET['sAddPremium'] = $this->Request()->getParam('sAddPremium');
                $this->basket->sInsertPremium();
            }
        }
        $this->redirect([
            'controller' => $this->Request()->getParam('sTarget', 'checkout'),
            'action' => $this->Request()->getParam('sTargetAction', 'index'),
        ]);
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
     * Action to handle selection of shipping and payment methods
     */
    public function shippingPaymentAction()
    {
        if (empty($this->View()->sUserLoggedIn)) {
            return $this->forward(
                'login',
                'account',
                null,
                ['sTarget' => 'checkout', 'sTargetAction' => 'shippingPayment', 'showNoAccount' => true]
            );
        }

        // Redirect if basket is empty
        if (!array_key_exists('content', $this->getBasket())) {
            return $this->redirect(['controller' => 'checkout', 'action' => 'cart']);
        }

        // Load payment options, select option and details
        $this->View()->assign('sPayments', $this->getPayments());
        $this->View()->assign('sFormData', ['payment' => $this->View()->sUserData['additional']['user']['paymentID']]);
        $getPaymentDetails = $this->admin->sGetPaymentMeanById($this->View()->sFormData['payment']);

        $paymentClass = $this->admin->sInitiatePaymentClass($getPaymentDetails);
        if ($paymentClass instanceof \ShopwarePlugin\PaymentMethods\Components\BasePaymentMethod) {
            $data = $paymentClass->getCurrentPaymentDataAsArray(Shopware()->Session()->sUserId);
            if (!empty($data)) {
                $this->View()->sFormData += $data;
            }
        }
        if ($this->Request()->isPost()) {
            $values = $this->Request()->getPost();
            $values['payment'] = $this->Request()->getPost('payment');
            $values['isPost'] = true;
            $this->View()->assign('sFormData', $values);
        }

        // Load current and all shipping methods
        $this->View()->assign('sDispatch', $this->getSelectedDispatch());
        $this->View()->assign('sDispatches', $this->getDispatches($this->View()->sFormData['payment']));

        // We might change the shop context here so we need to initialize it again
        $this->get('shopware_storefront.context_service')->initializeShopContext();

        $this->View()->assign('sBasket', $this->getBasket());

        $this->View()->assign('sLaststock', $this->basket->sCheckBasketQuantities());
        $this->View()->assign('sShippingcosts', $this->View()->sBasket['sShippingcosts']);
        $this->View()->assign('sShippingcostsDifference', $this->View()->sBasket['sShippingcostsDifference']);
        $this->View()->assign('sAmount', $this->View()->sBasket['sAmount']);
        $this->View()->assign('sAmountWithTax', $this->View()->sBasket['sAmountWithTax']);
        $this->View()->assign('sAmountTax', $this->View()->sBasket['sAmountTax']);
        $this->View()->assign('sAmountNet', $this->View()->sBasket['AmountNetNumeric']);
        $this->View()->assign('sRegisterFinished', !empty($this->session['sRegisterFinished']));
        $this->View()->assign('sTargetAction', 'shippingPayment');

        if ($this->Request()->getParam('isXHR')) {
            return $this->View()->loadTemplate('frontend/checkout/shipping_payment_core.tpl');
        }
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

            return $this->forward('shippingPayment');
        }

        $sErrorFlag = [];
        $sErrorMessages = [];

        $accountMode = (int) $this->View()->sUserData['additional']['user']['accountmode'];

        if ($dispatch === null && Shopware()->Config()->get('premiumshippingnoorder') === true && !$this->getDispatches($payment) && $accountMode === 0) {
            $sErrorFlag['sDispatch'] = true;
            $sErrorMessages[] = Shopware()->Snippets()->getNamespace('frontend/checkout/error_messages')
                ->get('ShippingPaymentSelectShipping', 'Please select a shipping method');
        }
        if ($payment === null) {
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

        // Save payment method details to db
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
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Get complete user-data as an array to use in view
     *
     * @return array
     */
    public function getUserData()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be protected with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $system = Shopware()->System();
        $userData = $this->admin->sGetUserData();
        if (!empty($userData['additional']['countryShipping'])) {
            $system->sUSERGROUPDATA = Shopware()->Db()->fetchRow('
                SELECT * FROM s_core_customergroups
                WHERE groupkey = ?
            ', [$system->sUSERGROUP]);

            $taxFree = $this->isTaxFreeDelivery($userData);
            $this->session->offsetSet('taxFree', $taxFree);

            if ($taxFree) {
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
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Create temporary order in s_order_basket on confirm page
     * Used to track failed / aborted orders
     */
    public function saveTemporaryOrder()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be protected with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $order = Shopware()->Modules()->Order();

        $orgBasketData = $this->View()->sBasket;
        $this->View()->assign('sBasket', $this->getBasket(false));

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

        $this->View()->assign('sBasket', $orgBasketData);

        $order->sDeleteTemporaryOrder();    // Delete previous temporary orders
        $order->sCreateTemporaryOrder();    // Create new temporary order
    }

    /**
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Finish order - set some object properties to do this
     */
    public function saveOrder()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be protected with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $order = Shopware()->Modules()->Order();

        $orgBasketData = $this->View()->getAssign('sBasket');
        $normalizer = $this->container->get('shopware.components.cart.proportional_cart_normalizer');

        $order->sUserData = $this->View()->sUserData;
        $order->sComment = isset($this->session['sComment']) ? $this->session['sComment'] : '';
        $order->sBasketData = $normalizer->normalize($orgBasketData);
        $order->sAmount = $this->View()->sBasket['sAmount'];
        $order->sAmountWithTax = !empty($this->View()->sBasket['AmountWithTaxNumeric']) ? $this->View()->sBasket['AmountWithTaxNumeric'] : $this->View()->sBasket['AmountNumeric'];
        $order->sAmountNet = $this->View()->sBasket['AmountNetNumeric'];
        $order->sShippingcosts = $this->View()->sBasket['sShippingcosts'];
        $order->sShippingcostsNumeric = $this->View()->sBasket['sShippingcostsWithTax'];
        $order->sShippingcostsNumericNet = $this->View()->sBasket['sShippingcostsNet'];
        $order->dispatchId = $this->session['sDispatch'];
        $order->sNet = !$this->View()->sUserData['additional']['charge_vat'];
        $order->deviceType = $this->Request()->getDeviceType();

        $this->View()->assign('sBasket', $orgBasketData);

        return $order->sSaveOrder();
    }

    /**
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Used in ajax add cart action
     * Check availability of product and return info / error - messages
     *
     * @param string $orderNumber product order number
     * @param int    $quantity    quantity
     *
     * @return string|null
     */
    public function getInstockInfo($orderNumber, $quantity)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be protected with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

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
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Get current stock from a certain product defined by $ordernumber
     * Support for multidimensional variants
     *
     * @param string $ordernumber
     *
     * @return array with product id / current basket quantity / instock / laststock
     */
    public function getAvailableStock($ordernumber)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be protected with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $sql = '
            SELECT
                a.id as articleID,
                ob.quantity,
                IF(ad.instock < 0, 0, ad.instock) as instock,
                ad.laststock,
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
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Get shipping costs as an array (brutto / netto) depending on selected country / payment
     *
     * @return array
     */
    public function getShippingCosts()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be protected with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $country = $this->getSelectedCountry();
        $payment = $this->getSelectedPayment();
        if (empty($country) || empty($payment)) {
            return ['brutto' => 0, 'netto' => 0];
        }
        $shippingcosts = $this->admin->sGetPremiumShippingcosts($country);

        return empty($shippingcosts) ? ['brutto' => 0, 'netto' => 0] : $shippingcosts;
    }

    /**
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Return complete basket data to view
     * Basket items / Shippingcosts / Amounts / Tax-Rates
     *
     * @param bool $mergeProportional
     *
     * @return array
     */
    public function getBasket($mergeProportional = true)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be protected with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $shippingCosts = $this->getShippingCosts();

        $basket = $this->basket->sGetBasket();

        /** @var \Shopware\Models\Shop\Currency $currency */
        $currency = $this->get('shop')->getCurrency();

        $positions = $this->container->get('shopware.cart.basket_helper')->getPositionPrices(
            new DiscountContext(
                $this->session->get('sessionId'),
                null,
                null,
                null,
                null,
                null,
                null,
                null
            )
        );

        $taxCalculator = $this->container->get('shopware.cart.proportional_tax_calculator');
        $hasDifferentTaxes = $taxCalculator->hasDifferentTaxes($positions);

        $basket['sCurrencyId'] = $currency->getId();
        $basket['sCurrencyName'] = $currency->getCurrency();
        $basket['sCurrencyFactor'] = $currency->getFactor();

        if ($hasDifferentTaxes && empty($shippingCosts['taxMode']) && $this->get('config')->get('proportionalTaxCalculation') && !$this->session->get('taxFree')) {
            $taxProportional = $taxCalculator->calculate($shippingCosts['brutto'], $positions, false);

            $basket['sShippingcostsTaxProportional'] = $taxProportional;

            $shippingNet = 0;

            foreach ($taxProportional as $shippingProportional) {
                $shippingNet += $shippingProportional->getNetPrice();
            }

            $basket['sShippingcostsWithTax'] = $shippingCosts['brutto'];
            $basket['sShippingcostsNet'] = $shippingNet;
            $basket['sShippingcostsTax'] = $shippingCosts['tax'];

            $shippingCosts['netto'] = $shippingNet;
        } else {
            $basket['sShippingcostsWithTax'] = $shippingCosts['brutto'];
            $basket['sShippingcostsNet'] = $shippingCosts['netto'];
            $basket['sShippingcostsTax'] = $shippingCosts['tax'];
        }

        if (!empty($shippingCosts['brutto'])) {
            $basket['AmountNetNumeric'] += $shippingCosts['netto'];
            $basket['AmountNumeric'] += $shippingCosts['brutto'];
            $basket['sShippingcostsDifference'] = $shippingCosts['difference']['float'];
        }
        if (!empty($basket['AmountWithTaxNumeric'])) {
            $basket['AmountWithTaxNumeric'] += $shippingCosts['brutto'];
        }
        if (!Shopware()->System()->sUSERGROUPDATA['tax'] && Shopware()->System()->sUSERGROUPDATA['id']) {
            $basket['sTaxRates'] = $this->getTaxRates($basket);

            $basket['sShippingcosts'] = $shippingCosts['netto'];
            $basket['sAmount'] = round($basket['AmountNetNumeric'], 2);
            $basket['sAmountTax'] = round($basket['AmountWithTaxNumeric'] - $basket['AmountNetNumeric'], 2);
            $basket['sAmountWithTax'] = round($basket['AmountWithTaxNumeric'], 2);
        } else {
            $basket['sTaxRates'] = $this->getTaxRates($basket);

            $basket['sShippingcosts'] = $shippingCosts['brutto'];
            $basket['sAmount'] = $basket['AmountNumeric'];

            $basket['sAmountTax'] = round($basket['AmountNumeric'] - $basket['AmountNetNumeric'], 2);
        }

        $this->View()->assign('sBasketProportional', $basket);
        if ($mergeProportional && $hasDifferentTaxes && $this->get('config')->get('proportionalTaxCalculation')) {
            $basket['content'] = $this->get('shopware.cart.proportional_cart_merger')->mergeProportionalItems($basket['content']);
        }

        return $basket;
    }

    /**
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Returns tax rates for all basket positions
     *
     * @param array $basket array returned from this->getBasket
     *
     * @return array
     */
    public function getTaxRates($basket)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be protected with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $result = [];

        if (!empty($basket['sShippingcostsTax'])) {
            if (!empty($basket['sShippingcostsTaxProportional'])) {
                /** @var \Shopware\Components\Cart\Struct\Price $shippingTax */
                foreach ($basket['sShippingcostsTaxProportional'] as $shippingTax) {
                    $result[number_format($shippingTax->getTaxRate(), 2)] += $shippingTax->getTax();
                }
            } else {
                $basket['sShippingcostsTax'] = number_format((float) $basket['sShippingcostsTax'], 2);

                $result[$basket['sShippingcostsTax']] = $basket['sShippingcostsWithTax'] - $basket['sShippingcostsNet'];
                if (empty($result[$basket['sShippingcostsTax']])) {
                    unset($result[$basket['sShippingcostsTax']]);
                }
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

                $tax = null;

                // Old behaviour
                if (empty($resultVoucherTaxMode) || $resultVoucherTaxMode === 'default') {
                    $tax = Shopware()->Config()->get('sVOUCHERTAX');
                } elseif ($resultVoucherTaxMode === 'auto') {
                    // Automatically determinate tax
                    $tax = $this->basket->getMaxTax();
                } elseif ($resultVoucherTaxMode === 'none') {
                    // No tax
                    $tax = '0';
                } elseif ((int) $resultVoucherTaxMode) {
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

            $taxKey = number_format((float) $item['tax_rate'], 2);

            $result[$taxKey] += str_replace(',', '.', $item['tax']);
        }

        ksort($result, SORT_NUMERIC);

        return $result;
    }

    /**
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Get similar shown products to display in ajax add dialog
     *
     * @param int $articleID
     *
     * @return array
     */
    public function getSimilarShown($articleID)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be protected with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

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
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Get articles that bought in combination with last added product to
     * display on cart page
     *
     * @param int $articleID
     *
     * @return array
     */
    public function getBoughtToo($articleID)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be protected with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

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
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Get configured minimum charge to check in order processing
     *
     * @return bool
     */
    public function getMinimumCharge()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be protected with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        return $this->basket->sCheckMinimumCharge();
    }

    /**
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Check if order is possible under current conditions (dispatch)
     *
     * @return bool
     */
    public function getDispatchNoOrder()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be protected with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        return !empty(Shopware()->Config()->PremiumShippingNoOrder) && (empty($this->session['sDispatch']) || empty($this->session['sCountry']));
    }

    /**
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Get all premium products that are configured and available for this order
     *
     * @return array
     */
    public function getPremiums()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be protected with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $sql = 'SELECT `id` FROM `s_order_basket` WHERE `sessionID`=? AND `modus`=1';
        $result = Shopware()->Db()->fetchOne($sql, [Shopware()->Session()->get('sessionId')]);
        if (!empty($result)) {
            return [];
        }

        return Shopware()->Modules()->Marketing()->sGetPremiums();
    }

    /**
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Check if any electronically distribution product is in basket
     *
     * @return bool
     */
    public function getEsdNote()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be protected with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $payment = empty($this->View()->sUserData['additional']['payment']) ? $this->session['sOrderVariables']['sUserData']['additional']['payment'] : $this->View()->sUserData['additional']['payment'];

        return $this->basket->sCheckForESD() && !$payment['esdactive'];
    }

    /**
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Check if a custom inquiry possibility should displayed on cart page
     * Compare configured inquiry value with current amount
     *
     * @return bool
     */
    public function getInquiry()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be protected with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

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
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Get link to inquiry form if getInquiry returned true
     *
     * @return string
     */
    public function getInquiryLink()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be protected with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        return Shopware()->Config()->get('sBASEFILE') . '?sViewport=support&sFid=' . Shopware()->Config()->get('sINQUIRYID') . '&sInquiry=basket';
    }

    /**
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Get all countries from database via sAdmin object
     *
     * @return array list of countries
     */
    public function getCountryList()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be protected with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        return $this->admin->sGetCountryList();
    }

    /**
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Get all dispatches available in selected country from sAdmin object
     *
     * @return array|false list of dispatches
     */
    public function getDispatches($paymentId = null)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be protected with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $country = $this->getSelectedCountry();
        $state = $this->getSelectedState();
        if (empty($country)) {
            return false;
        }
        $stateId = !empty($state['id']) ? $state['id'] : null;

        return $this->admin->sGetPremiumDispatches($country['id'], $paymentId, $stateId);
    }

    /**
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Returns all available payment methods from sAdmin object
     *
     * @return array list of payment methods
     */
    public function getPayments()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be protected with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        return $this->admin->sGetPaymentMeans();
    }

    /**
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Get current selected country - if no country is selected, choose first one from list
     * of available countries
     *
     * @return array|false
     */
    public function getSelectedCountry()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be protected with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

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
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Get current selected country - if no country is selected, choose first one from list
     * of available countries
     *
     * @return array with country information
     */
    public function getSelectedState()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be protected with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        if (!empty($this->View()->sUserData['additional']['stateShipping'])) {
            $this->session['sState'] = (int) $this->View()->sUserData['additional']['stateShipping']['id'];

            return $this->View()->sUserData['additional']['stateShipping'];
        }

        return ['id' => $this->session['sState']];
    }

    /**
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Get selected payment or do payment mean selection automatically
     *
     * @return array|bool
     */
    public function getSelectedPayment()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be protected with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $payment = null;
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
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Get selected dispatch or select a default dispatch
     *
     * @return bool|array
     */
    public function getSelectedDispatch()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be protected with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

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
     * @param int      $dispatchId $dispatchId ID of the dispatch method to set
     * @param int|null $paymentId  Payment id to validate
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
     * Ajax add product action
     *
     * This action will get redirected from the default addArticleAction
     * when the request was an AJAX request.
     */
    public function ajaxAddArticleAction()
    {
        // Empty but can't be removed for legacy reasons
    }

    /**
     * Ajax add product cart action
     *
     * This action is a lightweight way to add an product by the passed
     * product order number and quantity.
     *
     * The order number is expected to get passed by the 'sAdd' parameter
     * This quantity is expected to get passed by the 'sQuantity' parameter.
     *
     * After the product was added to the basket, the whole cart content will be returned.
     *
     * @throws \LogicException
     */
    public function ajaxAddArticleCartAction()
    {
        if (strtolower($this->Request()->getMethod()) !== 'post') {
            throw new \LogicException('This action only admits post requests');
        }

        $orderNumber = $this->Request()->getParam('sAdd');
        $quantity = $this->Request()->getParam('sQuantity');

        $this->View()->assign(
            'basketInfoMessage',
            $this->getInstockInfo($orderNumber, $quantity)
        );

        $this->basket->sAddArticle($orderNumber, $quantity);

        if ($this->Request()->get('sAddAccessories')) {
            $this->addAccessories(
                $this->Request()->getParam('sAddAccessories'),
                $this->Request()->getParam('sAddAccessoriesQuantity')
            );
        }

        $this->forward('ajaxCart');
    }

    /**
     * Ajax delete product action
     *
     * This action is a lightweight way to delete an product by the passed
     * basket item id.
     *
     * This id is expected to get passed by the 'sDelete' parameter.
     *
     * After the product was removed from the basket, the whole cart content will be returned.
     */
    public function ajaxDeleteArticleCartAction()
    {
        if (strtolower($this->Request()->getMethod()) !== 'post') {
            throw new \LogicException('This action only admits post requests');
        }

        $itemId = $this->Request()->getParam('sDelete');

        if ($itemId) {
            $this->basket->sDeleteArticle($itemId);
        }

        $this->forward('ajaxCart');
    }

    /**
     * Ajax cart action
     *
     * This action loads the cart content and returns it.
     * Its purpose is to return all necessary information in a minimal template
     * for a good performance so e.g. ajax requests are finished more quickly.
     */
    public function ajaxCartAction()
    {
        $view = $this->View();

        // Necessary to call this first in order for 'sDispatch' session variable to be set
        // The basket will need this session variable to properly calculate the shipping costs
        $dispatch = $this->getSelectedDispatch();
        $basket = $this->getBasket();

        $view->assign('sCountryList', $this->getCountryList());
        $view->assign('sState', $this->getSelectedState());
        $view->assign('sPayments', $this->getPayments());
        $view->assign('sCountry', $this->getSelectedCountry());
        $view->assign('sPayment', $this->getSelectedPayment());

        $view->assign('sDispatch', $dispatch);
        $view->assign('sBasket', $basket);
        $this->View()->assign('sInvalidCartItems', $this->getInvalidProducts($basket));

        $view->assign('sShippingcosts', $basket['sShippingcosts']);
        $view->assign('sShippingcostsDifference', $basket['sShippingcostsDifference']);
        $view->assign('sAmount', $basket['sAmount']);
        $view->assign('sAmountWithTax', $basket['sAmountWithTax']);
        $view->assign('sAmountTax', $basket['sAmountTax']);
        $view->assign('sAmountNet', $basket['AmountNetNumeric']);
        $view->assign('sDispatches', $this->getDispatches());
        $accountMode = (int) $this->View()->sUserData['additional']['user']['accountmode'];
        $view->assign('sDispatchNoOrder', ($accountMode === 0 && $this->getDispatchNoOrder()));
        $view->assign('showShippingCalculation', (bool) $this->Request()->getParam('openShippingCalculations'));
    }

    /**
     * Get current amount from cart via ajax to display in realtime
     */
    public function ajaxAmountAction()
    {
        $this->Response()->headers->set('content-type', 'application/json');

        $amount = $this->basket->sGetAmount();
        $quantity = $this->basket->sCountBasket();

        $this->View()->assign('sBasketQuantity', $quantity);
        $this->View()->assign('sBasketAmount', empty($amount) ? 0 : array_shift($amount));

        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        $this->Response()->setContent(
            json_encode([
                'amount' => Shopware()->Template()->fetch('frontend/checkout/ajax_amount.tpl'),
                'quantity' => $quantity,
            ])
        );
    }

    /**
     * Sets a temporary session variable which holds an address for the current order
     */
    public function setAddressAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender(true);
        $target = $this->Request()->getParam('target', 'shipping');
        $sessionKey = $target === 'shipping' ? 'checkoutShippingAddressId' : 'checkoutBillingAddressId';

        $this->session->offsetSet($sessionKey, $this->Request()->getParam('addressId'));

        if ($target === 'both') {
            $this->session->offsetSet('checkoutShippingAddressId', $this->Request()->getParam('addressId'));
            $this->session->offsetSet('checkoutBillingAddressId', $this->Request()->getParam('addressId'));
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

        if (empty($userData['shippingaddress']['ustid'])
            && !empty($userData['billingaddress']['ustid'])
            && !empty($userData['additional']['country']['taxfree_ustid'])) {
            return true;
        }

        return !empty($userData['shippingaddress']['ustid']);
    }

    /**
     * @param array $basket
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
            && $this->basketHasEsdProducts($basket)
            && empty($esdAgreement)
        ) {
            $errors['esdError'] = true;
        }

        $serviceChecked = $request->getParam('serviceAgreementChecked');
        if ($this->basketHasServiceProducts($basket) && empty($serviceChecked)) {
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
                    $quantity = (int) $quantities[$key];
                }

                $this->basket->sAddArticle($accessory, $quantity);
            } catch (Exception $e) {
            }
        }
    }

    /**
     * Helper function that checks whether or not the given basket has an esd product in it.
     *
     * @param array $basket
     *
     * @return bool
     */
    private function basketHasEsdProducts($basket)
    {
        if (!isset($basket['content'])) {
            return false;
        }

        foreach ($basket['content'] as $cartItem) {
            if ($cartItem['esd']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Helper function that iterates through the basket products.
     * It checks if an product is a service product by comparing its attributes
     * with the plugin config serviceAttrField value.
     *
     * @param array $basket
     *
     * @return bool
     */
    private function basketHasServiceProducts($basket)
    {
        $config = Shopware()->Config();

        if (!$config->offsetExists('serviceAttrField')) {
            return false;
        }

        $attrName = $config->serviceAttrField;
        if (empty($attrName) || !isset($basket['content'])) {
            return false;
        }

        foreach ($basket['content'] as $cartItem) {
            $serviceAttr = $cartItem['additional_details'][$attrName];

            if ($serviceAttr && $serviceAttr != 'false') {
                return true;
            }
        }

        return false;
    }

    /**
     * Helper function that iterates through the basket products.
     * If checks if the basket has a normal product e.g. not an esd product
     * and not a product with the service attribute is set to true.
     *
     * @param array $basket
     *
     * @return bool
     */
    private function basketHasMixedProducts($basket)
    {
        $config = Shopware()->Config();
        $attrName = $config->serviceAttrField;

        if (!isset($basket['content'])) {
            return false;
        }

        foreach ($basket['content'] as $cartItem) {
            if ((int) $cartItem['modus'] === 4 || $cartItem['esd']) {
                continue;
            }

            $serviceAttr = $cartItem['additional_details'][$attrName];
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
        $billingId = $this->session->offsetGet('checkoutBillingAddressId');
        $shippingId = $this->session->offsetGet('checkoutShippingAddressId');
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
     * @param int  $addressId
     * @param bool $isShippingAddress
     *
     * @return bool
     */
    private function isValidAddress($addressId, $isShippingAddress = false)
    {
        $address = $this->get('models')->find(Address::class, $addressId);

        $context = $this->get('shopware_storefront.context_service')->getContext();
        $country = $this->get('shopware_storefront.country_gateway')->getCountry($address->getCountry()->getId(), $context);

        if ($address && $isShippingAddress && !$country->allowShipping()) {
            $this->View()->assign('invalidShippingCountry', true);

            return false;
        }

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
     * @return bool
     */
    private function areAddressesEqual(array $addressA, array $addressB)
    {
        $unset = ['id', 'customernumber', 'phone', 'ustid'];
        foreach ($unset as $key) {
            unset($addressA[$key], $addressB[$key]);
        }

        return count(array_diff($addressA, $addressB)) === 0;
    }

    /**
     * @return string
     */
    private function persistBasket()
    {
        /** @var BasketSignatureGeneratorInterface $generator */
        $generator = $this->get('basket_signature_generator');
        $basket = $this->session->offsetGet('sOrderVariables')->getArrayCopy();
        $signature = $generator->generateSignature($basket['sBasket'], $this->session->get('sUserId'));

        /** @var BasketPersister $persister */
        $persister = $this->get('basket_persister');
        $persister->persist($signature, $basket);

        return $signature;
    }

    /**
     * Updates all currency dependencies (e.g. in the shop model or in the shop context).
     *
     * @param int $currencyId
     */
    private function updateCurrencyDependencies($currencyId)
    {
        /** @var \Shopware\Models\Shop\Currency $currencyModel */
        $currencyModel = $this->get('models')->find(\Shopware\Models\Shop\Currency::class, $currencyId);

        /** @var Shopware\Models\Shop\Shop $shopModel */
        $shopModel = $this->get('shop');
        $shopModel->setCurrency($currencyModel);

        /** @var \Zend_Currency $currency */
        $currency = $this->get('currency');
        $currency->setFormat($currencyModel->toArray());

        $this->get('shopware_storefront.context_service')->initializeShopContext();
    }

    private function getInvalidProducts(array $basket): array
    {
        $products = [];

        foreach ($basket['content'] as $item) {
            if ((int) $item['modus'] !== 0) {
                continue;
            }

            if (!empty($item['additional_details'])) {
                continue;
            }

            $products[] = $item['articlename'];
        }

        return $products;
    }
}
