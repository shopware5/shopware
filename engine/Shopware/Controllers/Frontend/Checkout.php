<?php

declare(strict_types=1);
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

use Doctrine\DBAL\Connection;
use Enlight_Controller_Request_Request as Request;
use Shopware\Bundle\AccountBundle\Service\AddressServiceInterface;
use Shopware\Bundle\AccountBundle\Service\Validator\AddressValidatorInterface;
use Shopware\Bundle\AttributeBundle\Service\DataLoader;
use Shopware\Bundle\CartBundle\CartKey;
use Shopware\Bundle\CartBundle\CartPositionsMode;
use Shopware\Bundle\CartBundle\CheckoutKey;
use Shopware\Bundle\StoreFrontBundle\Gateway\CountryGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\Cart\BasketHelperInterface;
use Shopware\Components\Cart\Struct\DiscountContext;
use Shopware\Components\Cart\TaxAggregatorInterface;
use Shopware\Components\CSRFGetProtectionAware;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Country\Country;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Shop\Currency;
use ShopwarePlugin\PaymentMethods\Components\BasePaymentMethod;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * @phpstan-type CheckoutBasketArray array{content?: array<string, mixed>, Amount?: string, AmountNet?: string, Quantity?: int, AmountNumeric: float, AmountNetNumeric: float, AmountWithTax?: string, AmountWithTaxNumeric?: float, sCurrencyId: int, sCurrencyName: string, sCurrencyFactor: float, sShippingcosts: float, sShippingcostsTax: float|null, sShippingcostsTaxProportional?: array<\Shopware\Components\Cart\Struct\Price>, sShippingcostsNet: float, sShippingcostsWithTax: float, sShippingcostsDifference?: float|null, sTaxRates: array<string, float>, sAmount: float, sAmountTax: float, sAmountWithTax?: float}
 *
 * @phpstan-import-type ShippingCostArray from \sAdmin
 */
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

    protected TaxAggregatorInterface $taxAggregator;

    /**
     * Init method that get called automatically
     *
     * Set class properties
     *
     * @return void
     */
    public function init()
    {
        $this->admin = Shopware()->Modules()->Admin();
        $this->basket = Shopware()->Modules()->Basket();
        $this->session = Shopware()->Session();
        $this->taxAggregator = Shopware()->Container()->get(TaxAggregatorInterface::class);
    }

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
     *
     * @return void
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
        $this->session->set('sBasketCurrency', Shopware()->Shop()->getCurrency()->getId());
        $this->session->set('sBasketQuantity', $this->basket->sCountBasket());
        $amount = $this->basket->sGetAmount();
        $this->session->set('sBasketAmount', empty($amount) ? 0 : array_shift($amount));

        if (($messageType = $this->Request()->query->get('removeMessage')) && $messageType === 'voucher') {
            $this->session->offsetUnset('sBasketVoucherRemovedInCart');
        }

        if ($this->session->offsetExists('sBasketVoucherRemovedInCart')) {
            $this->View()->assign('sBasketVoucherRemovedInCart', true);
        }
    }

    /**
     * Forward to cart or confirm action depending on user state
     *
     * @return void
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
     *
     * @return void
     */
    public function cartAction()
    {
        $accountMode = (int) ($this->View()->getAssign('sUserData')['additional']['user']['accountmode'] ?? Customer::ACCOUNT_MODE_CUSTOMER);
        $country = $this->getSelectedCountry();
        $this->View()->assign('sCountry', $country);
        $this->View()->assign('sPayment', $this->getSelectedPayment());
        $this->View()->assign('sDispatch', $this->getSelectedDispatch());
        $this->View()->assign('sCountryList', $this->getCountryList());
        $this->View()->assign('sPayments', $this->getPayments());
        $this->View()->assign('sDispatches', $this->getDispatches());
        $this->View()->assign('sDispatchNoOrder', $accountMode === Customer::ACCOUNT_MODE_CUSTOMER && $this->getDispatchNoOrder());
        $this->View()->assign('sState', $this->getSelectedState());

        $this->View()->assign('sUserData', $this->getUserData());
        $this->View()->assign('sBasket', $this->getBasket());
        $this->View()->assign('sInvalidCartItems', $this->getInvalidProducts($this->View()->getAssign('sBasket')));

        $this->View()->assign(CheckoutKey::SHIPPING_COSTS, $this->View()->getAssign('sBasket')[CheckoutKey::SHIPPING_COSTS]);
        $this->View()->assign(CheckoutKey::SHIPPING_COSTS_DIFFERENCE, $this->View()->getAssign('sBasket')[CheckoutKey::SHIPPING_COSTS_DIFFERENCE] ?? null);
        $this->View()->assign(CheckoutKey::AMOUNT, $this->View()->getAssign('sBasket')[CheckoutKey::AMOUNT]);
        $this->View()->assign(CheckoutKey::AMOUNT_WITH_TAX, $this->View()->getAssign('sBasket')[CheckoutKey::AMOUNT_WITH_TAX] ?? 0.0);
        $this->View()->assign(CheckoutKey::AMOUNT_TAX, $this->View()->getAssign('sBasket')[CheckoutKey::AMOUNT_TAX]);
        $this->View()->assign('sAmountNet', $this->View()->getAssign('sBasket')[CartKey::AMOUNT_NET_NUMERIC]);

        $this->View()->assign('sMinimumSurcharge', $this->getMinimumCharge());
        $this->View()->assign('sPremiums', $this->getPremiums());

        $this->View()->assign('sInquiry', $this->getInquiry());
        $this->View()->assign('sInquiryLink', $this->getInquiryLink());

        $this->View()->assign('sTargetAction', 'cart');

        $this->View()->assign('sBasketInfo', $this->session->offsetGet('sErrorMessages'));
        $this->session->offsetUnset('sErrorMessages');

        if (!\is_array($country) || !$this->isShippingAllowed((int) $country['id'])) {
            $this->View()->assign('countryNotAllowedForShipping', true);
        }
    }

    /**
     * Mostly equivalent to cartAction
     * Get user, basket and payment data for view assignment
     * Create temporary entry in s_order table
     * Check some conditions (minimum charge)
     *
     * @return void
     */
    public function confirmAction()
    {
        if (empty($this->View()->getAssign('sUserLoggedIn'))) {
            $this->forward(
                'login',
                'account',
                null,
                ['sTarget' => 'checkout', 'sTargetAction' => 'confirm', 'showNoAccount' => true]
            );

            return;
        }

        if ($this->basket->sCountBasket() < 1) {
            $this->forward('cart');

            return;
        }

        $this->View()->assign('sCountry', $this->getSelectedCountry());
        $this->View()->assign('sState', $this->getSelectedState());

        $payment = $this->getSelectedPayment();
        if (\is_array($payment) && \array_key_exists('validation', $payment) && !empty($payment['validation'])) {
            $this->onPaymentMethodValidationFail();

            return;
        }

        $this->View()->assign('sPayment', $payment);

        $userData = $this->View()->getAssign('sUserData');
        $userData['additional']['payment'] = $this->View()->getAssign('sPayment');
        $this->View()->assign('sUserData', $userData);

        $this->View()->assign('sDispatch', $this->getSelectedDispatch());
        $this->View()->assign('sPayments', $this->getPayments());
        $this->View()->assign('sDispatches', $this->getDispatches());

        $this->View()->assign('sBasket', $this->getBasket());
        $this->View()->assign('sInvalidCartItems', $this->getInvalidProducts($this->View()->getAssign('sBasket')));

        $this->View()->assign('sLaststock', $this->basket->sCheckBasketQuantities());
        $this->View()->assign(CheckoutKey::SHIPPING_COSTS, $this->View()->getAssign('sBasket')[CheckoutKey::SHIPPING_COSTS]);
        $this->View()->assign(CheckoutKey::SHIPPING_COSTS_DIFFERENCE, $this->View()->getAssign('sBasket')[CheckoutKey::SHIPPING_COSTS_DIFFERENCE] ?? null);
        $this->View()->assign(CheckoutKey::AMOUNT, $this->View()->getAssign('sBasket')[CheckoutKey::AMOUNT]);
        $this->View()->assign(CheckoutKey::AMOUNT_WITH_TAX, $this->View()->getAssign('sBasket')[CheckoutKey::AMOUNT_WITH_TAX] ?? 0.0);
        $this->View()->assign(CheckoutKey::AMOUNT_TAX, $this->View()->getAssign('sBasket')[CheckoutKey::AMOUNT_TAX]);
        $this->View()->assign('sAmountNet', $this->View()->getAssign('sBasket')[CartKey::AMOUNT_NET_NUMERIC]);

        $this->View()->assign('sPremiums', $this->getPremiums());

        $this->View()->assign('sNewsletter', isset($this->session['sNewsletter']) ? $this->session['sNewsletter'] : null);
        $this->View()->assign('sComment', isset($this->session['sComment']) ? $this->session['sComment'] : null);

        $this->View()->assign('sShowEsdNote', $this->getEsdNote());
        $this->View()->assign('sDispatchNoOrder', $this->getDispatchNoOrder());
        $this->View()->assign('sRegisterFinished', !empty($this->session['sRegisterFinished']));

        $this->saveTemporaryOrder();

        if ($this->getMinimumCharge() || \count($this->View()->getAssign('sBasket')[CartKey::POSITIONS]) <= 0 || $this->View()->getAssign('sInvalidCartItems')) {
            $this->forward('cart');

            return;
        }

        $normalizer = $this->container->get('shopware.components.cart.proportional_cart_normalizer');
        $sOrderVariables = $this->View()->getAssign();
        $sOrderVariables['sBasketView'] = $sOrderVariables['sBasket'];
        $sOrderVariables['sBasket'] = $normalizer->normalize($sOrderVariables['sBasketView']);

        $this->session['sOrderVariables'] = new ArrayObject($sOrderVariables, ArrayObject::ARRAY_AS_PROPS);

        $agbChecked = $this->Request()->getParam('sAGB');
        if (!empty($agbChecked)) {
            $this->View()->assign('sAGBChecked', true);
        }

        $this->View()->assign('sTargetAction', 'confirm');

        $this->View()->assign('hasMixedArticles', $this->basketHasMixedProducts($this->View()->getAssign('sBasket')));
        $this->View()->assign('hasServiceArticles', $this->basketHasServiceProducts($this->View()->getAssign('sBasket')));

        if (Shopware()->Config()->get('showEsdWarning')) {
            $this->View()->assign('hasEsdArticles', $this->basketHasEsdProducts($this->View()->getAssign('sBasket')));
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

        $activeBillingAddressId = $this->session->offsetGet('checkoutBillingAddressId');
        if (empty($activeBillingAddressId)) {
            $activeBillingAddressId = $userData['additional']['user']['default_billing_address_id'];
        }

        $activeShippingAddressId = $this->session->offsetGet('checkoutShippingAddressId');
        if (empty($activeShippingAddressId)) {
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
     *
     * @return void
     */
    public function finishAction()
    {
        if ($this->Request()->getParam('sUniqueID') && !empty($this->session['sOrderVariables'])) {
            $sql = '
                SELECT transactionID as sTransactionumber, ordernumber as sOrderNumber
                FROM s_order
                WHERE temporaryID=? AND userID=?
            ';

            $order = Shopware()->Db()->fetchRow($sql, [$this->Request()->getParam('sUniqueID'), Shopware()->Session()->get('sUserId')]);

            if (empty($order)) {
                if ($this->Request()->isGet()) {
                    $this->forward('confirm');

                    return;
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
            $this->forward('confirm');

            return;
        }

        $checkQuantities = $this->basket->sCheckBasketQuantities();
        if (!empty($checkQuantities['hideBasket'])) {
            $this->forward('confirm');

            return;
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

        if (!empty($this->View()->getAssign('sUserData')['additional']['payment']['embediframe'])) {
            return;
        }

        if ($this->Request()->getParam('sNewsletter') !== null) {
            $this->session['sNewsletter'] = $this->Request()->getParam('sNewsletter') ? true : false;
        }
        if ($this->Request()->getParam('sComment') !== null) {
            $this->session['sComment'] = trim(strip_tags($this->Request()->getParam('sComment')));
        }

        $basket = $this->View()->getAssign('sBasket');
        $agreements = $this->getInvalidAgreements($basket, $this->Request());

        if (!empty($agreements)) {
            $this->View()->assign('sAGBError', \array_key_exists('agbError', $agreements));

            $this->forward(
                'confirm',
                null,
                null,
                ['agreementErrors' => $agreements]
            );

            return;
        }

        if (!$this->basket->validateVoucher($this->session['sessionId'], $this->session['sUserId'])) {
            $namespace = $this->container->get('snippets')->getNamespace('frontend/basket/internalMessages');

            $this->forward(
                'confirm',
                null,
                null,
                ['voucherErrors' => [
                    $namespace->get('VoucherFailureAlreadyUsed', 'This voucher was used in an previous order'),
                ]]
            );

            return;
        }

        $activeBillingAddressId = $this->session->offsetGet('checkoutBillingAddressId');
        if (empty($activeBillingAddressId)) {
            $activeBillingAddressId = $this->View()->getAssign('sUserData')['additional']['user']['default_billing_address_id'];
        }

        $activeShippingAddressId = $this->session->offsetGet('checkoutShippingAddressId');
        if (empty($activeShippingAddressId)) {
            $activeShippingAddressId = $this->View()->getAssign('sUserData')['additional']['user']['default_shipping_address_id'];
        }

        $activeBillingAddressId = (int) $activeBillingAddressId;
        $activeShippingAddressId = (int) $activeShippingAddressId;

        if (!$this->isValidAddress($activeBillingAddressId) || !$this->isValidAddress($activeShippingAddressId, true)) {
            $this->forward('confirm');

            return;
        }

        $this->updateNewsletter();

        if ($this->Request()->isGet()) {
            $this->forward('confirm');

            return;
        }

        $this->updateCurrencyDependencies((int) $basket[CheckoutKey::CURRENCY_ID]);

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
     *
     * @return void
     */
    public function returnAction()
    {
        if ($this->View()->getAssign('sUserData')['additional']['user']['accountmode'] == 1) {
            $this->Request()->getSession()->clear();
            Shopware()->Modules()->Basket()->sRefreshBasket();
        }

        $this->redirect(['controller' => 'index']);
    }

    /**
     * If any external payment mean chooses by customer
     * Forward to payment page after order submitting
     *
     * @return void
     */
    public function paymentAction()
    {
        if (empty($this->session['sOrderVariables'])
                || $this->getMinimumCharge()
                || $this->getEsdNote()
                || $this->getDispatchNoOrder()) {
            $this->forward('confirm');

            return;
        }

        if ($this->Request()->getParam('sNewsletter') !== null) {
            $this->session['sNewsletter'] = $this->Request()->getParam('sNewsletter') ? true : false;
        }
        if ($this->Request()->getParam('sComment') !== null) {
            $this->session['sComment'] = trim(strip_tags($this->Request()->getParam('sComment')));
        }

        $this->View()->assign($this->session['sOrderVariables']->getArrayCopy());

        $agreements = $this->getInvalidAgreements($this->View()->getAssign('sBasket'), $this->Request());

        if (!empty($agreements)) {
            $this->View()->assign('sAGBError', \array_key_exists('agbError', $agreements));

            $this->forward(
                'confirm',
                null,
                null,
                ['agreementErrors' => $agreements]
            );

            return;
        }

        $this->View()->assign('sAGBError', false);

        if (empty($this->View()->getAssign('sPayment')['embediframe'])
                && empty($this->View()->getAssign('sPayment')['action'])) {
            $this->forward('confirm');

            return;
        }

        $this->updateNewsletter();

        if (!empty($this->View()->getAssign('sPayment')['embediframe'])) {
            $embedded = $this->View()->getAssign('sPayment')['embediframe'];
            $embedded = preg_replace('#^[./]+#', '', $embedded);
            $embedded .= '?sCoreId=' . Shopware()->Session()->get('sessionId');
            $embedded .= '&sAGB=1';
            $embedded .= '&__basket_signature=' . $this->persistBasket();
            $this->View()->assign('sEmbedded', $embedded);
        } else {
            $action = explode('/', $this->View()->getAssign('sPayment')['action']);
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
     *
     * @return void
     */
    public function addArticleAction()
    {
        $this->validatePostMethod();

        $orderNumber = trim($this->Request()->getParam('sAdd'));
        $quantity = (int) $this->Request()->getParam('sQuantity');
        $productId = Shopware()->Modules()->Articles()->sGetArticleIdByOrderNumber($orderNumber);
        $instockInfo = $this->getInstockInfo($orderNumber, $quantity);

        $this->View()->assign('sBasketInfo', $instockInfo);

        if ($instockInfo !== null) {
            $this->session->offsetSet('sErrorMessages', $instockInfo);
        }

        if (!empty($productId)) {
            $insertId = $this->basket->sAddArticle($orderNumber, $quantity);
            $this->View()->assign('sArticleName', Shopware()->Modules()->Articles()->sGetArticleNameByOrderNumber($orderNumber));
            if (!empty($insertId)) {
                $basket = $this->getBasket();
                foreach ($basket[CartKey::POSITIONS] ?? [] as $item) {
                    if ((int) $item['id'] === $insertId) {
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
        } else {
            $this->session->offsetSet('sErrorMessages', $this->container->get('snippets')->getNamespace('frontend/basket/internalMessages')->get('WrongArticleNumberMessage', 'Please enter a valid article number'));
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
     *
     * @return void
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
     *
     * @return void
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
     *
     * @return void
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
     *
     * @return void
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
     *
     * @return void
     */
    public function addPremiumAction()
    {
        if ($this->Request()->isPost()) {
            if (!$this->Request()->getParam('sAddPremium')) {
                $this->session->offsetSet(
                    'sErrorMessages',
                    $this->container->get('snippets')->getNamespace('frontend')->get(
                        'CheckoutSelectPremiumVariant',
                        'Please select an option to place the required premium to the cart'
                    )
                );
            } else {
                $this->front->ensureRequest()->setQuery('sAddPremium', $this->Request()->getParam('sAddPremium'));
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
     *
     * @return void
     */
    public function calculateShippingCostsAction()
    {
        $countryId = $this->Request()->getPost('sCountry');
        if ($countryId) {
            if (!$this->isShippingAllowed((int) $countryId)) {
                $this->View()->assign('countryNotAllowedForShipping', true);
            }

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
        $this->get(ContextServiceInterface::class)->initializeShopContext();

        // We need an indicator in the view to expand the shipping costs pre-calculation on page load
        $this->View()->assign('calculateShippingCosts', true);

        $this->forward($this->Request()->getParam('sTargetAction', 'index'));
    }

    /**
     * Action to handle selection of shipping and payment methods
     *
     * @return void
     */
    public function shippingPaymentAction()
    {
        if (empty($this->View()->sUserLoggedIn)) {
            $this->forward(
                'login',
                'account',
                null,
                ['sTarget' => 'checkout', 'sTargetAction' => 'shippingPayment', 'showNoAccount' => true]
            );

            return;
        }

        // Redirect if basket is empty
        if (!\array_key_exists(CartKey::POSITIONS, $this->getBasket())) {
            $this->redirect(['controller' => 'checkout', 'action' => 'cart']);

            return;
        }

        // Load payment options, select option and details
        $this->View()->assign('sPayments', $this->getPayments());
        $this->View()->assign('sFormData', ['payment' => $this->View()->getAssign('sUserData')['additional']['user']['paymentID']]);
        $getPaymentDetails = $this->admin->sGetPaymentMeanById($this->View()->getAssign('sFormData')['payment']);

        $paymentClass = $this->admin->sInitiatePaymentClass($getPaymentDetails);
        if ($paymentClass instanceof BasePaymentMethod) {
            $data = $paymentClass->getCurrentPaymentDataAsArray(Shopware()->Session()->get('sUserId'));
            if (!empty($data)) {
                $this->View()->assign('sFormData', $this->View()->getAssign('sFormData') + $data);
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
        $this->View()->assign('sDispatches', $this->getDispatches($this->View()->getAssign('sFormData')['payment']));

        // We might change the shop context here, so we need to initialize it again
        $this->get(ContextServiceInterface::class)->initializeShopContext();

        $this->View()->assign('sBasket', $this->getBasket());

        $this->View()->assign('sLaststock', $this->basket->sCheckBasketQuantities());
        $this->View()->assign(CheckoutKey::SHIPPING_COSTS, $this->View()->getAssign('sBasket')[CheckoutKey::SHIPPING_COSTS]);
        $this->View()->assign(CheckoutKey::SHIPPING_COSTS_DIFFERENCE, $this->View()->getAssign('sBasket')[CheckoutKey::SHIPPING_COSTS_DIFFERENCE] ?? null);
        $this->View()->assign(CheckoutKey::AMOUNT, $this->View()->getAssign('sBasket')[CheckoutKey::AMOUNT]);
        $this->View()->assign(CheckoutKey::AMOUNT_WITH_TAX, $this->View()->getAssign('sBasket')[CheckoutKey::AMOUNT_WITH_TAX] ?? 0.0);
        $this->View()->assign(CheckoutKey::AMOUNT_TAX, $this->View()->getAssign('sBasket')[CheckoutKey::AMOUNT_TAX]);
        $this->View()->assign('sAmountNet', $this->View()->getAssign('sBasket')[CartKey::AMOUNT_NET_NUMERIC]);
        $this->View()->assign('sRegisterFinished', !empty($this->session['sRegisterFinished']));
        $this->View()->assign('sTargetAction', 'shippingPayment');

        if ($this->Request()->getParam('isXHR')) {
            $this->View()->loadTemplate('frontend/checkout/shipping_payment_core.tpl');
        }
    }

    /**
     * Action to simultaneously save shipping and payment details
     *
     * @return void
     */
    public function saveShippingPaymentAction()
    {
        if (!$this->Request()->isPost()) {
            $this->forward('shippingPayment');

            return;
        }

        // Load data from request
        $dispatch = $this->Request()->getPost('sDispatch');
        $payment = $this->Request()->getPost('payment');

        // If request is ajax, we skip the validation, because the user is still editing
        if ($this->Request()->getParam('isXHR')) {
            // Save payment and shipping method data.
            $this->admin->sUpdatePayment($payment);
            $this->setDispatch($dispatch, $payment);

            $this->forward('shippingPayment');

            return;
        }

        $sErrorFlag = [];
        $sErrorMessages = [];

        $accountMode = (int) $this->View()->getAssign('sUserData')['additional']['user']['accountmode'];

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

            $this->forward('shippingPayment');

            return;
        }

        // Validate the payment details
        $this->front->ensureRequest()->setPost('sPayment', $payment);
        $checkData = $this->admin->sValidateStep3();

        // Problem with the payment details, return error
        if (!empty($checkData['checkPayment']['sErrorMessages']) || empty($checkData['sProcessed'])) {
            $this->View()->assign('sErrorFlag', $checkData['checkPayment']['sErrorFlag']);
            $this->View()->assign('sErrorMessages', $checkData['checkPayment']['sErrorMessages']);

            $this->forward('shippingPayment');

            return;
        }

        // Save payment method details to db
        if ($checkData['sPaymentObject'] instanceof BasePaymentMethod) {
            $checkData['sPaymentObject']->savePaymentData(Shopware()->Session()->get('sUserId'), $this->Request());
        }

        // Save the payment info
        $customerData = Shopware()->Modules()->Admin()->sGetUserData();
        if (!\is_array($customerData)) {
            throw new RuntimeException('Could not get customer data');
        }
        $previousPayment = $customerData['additional']['user']['paymentID'];

        $previousPayment = $this->admin->sGetPaymentMeanById($previousPayment);
        if ($previousPayment['paymentTable']) {
            Shopware()->Db()->delete(
                $previousPayment['paymentTable'],
                ['userID = ?' => Shopware()->Session()->get('sUserId')]
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
     * @return array<string, mixed>
     */
    public function getUserData()
    {
        $system = Shopware()->System();
        $customerData = $this->admin->sGetUserData();
        if (!\is_array($customerData)) {
            throw new RuntimeException('Could not get customer data');
        }
        if (!empty($customerData['additional']['countryShipping'])) {
            $system->sUSERGROUPDATA = Shopware()->Db()->fetchRow('
                SELECT * FROM s_core_customergroups
                WHERE groupkey = ?
            ', [$system->sUSERGROUP]);

            $taxFree = $this->isTaxFreeDelivery($customerData);
            $this->session->offsetSet('taxFree', $taxFree);

            if ($taxFree) {
                $system->sUSERGROUPDATA['tax'] = 0;
                $system->sCONFIG['sARTICLESOUTPUTNETTO'] = 1; // Old template
                Shopware()->Session()->set('sUserGroupData', $system->sUSERGROUPDATA);
                $customerData['additional']['charge_vat'] = false;
                $customerData['additional']['show_net'] = false;
                Shopware()->Session()->set('sOutputNet', true);
            } else {
                $customerData['additional']['charge_vat'] = true;
                $customerData['additional']['show_net'] = !empty($system->sUSERGROUPDATA['tax']);
                Shopware()->Session()->set('sOutputNet', empty($system->sUSERGROUPDATA['tax']));
            }
        }

        return $customerData;
    }

    /**
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Create temporary order in s_order_basket on confirm page
     * Used to track failed / aborted orders
     *
     * @return void
     */
    public function saveTemporaryOrder()
    {
        $order = Shopware()->Modules()->Order();

        $normalizer = $this->container->get('shopware.components.cart.proportional_cart_normalizer');

        $order->sUserData = $this->View()->getAssign('sUserData');
        $order->sComment = $this->session['sComment'] ?? '';
        $order->sBasketData = $normalizer->normalize($this->View()->getAssign('sBasket'));
        $order->sAmount = $this->View()->getAssign('sBasket')[CheckoutKey::AMOUNT];
        $order->sAmountWithTax = !empty($this->View()->getAssign('sBasket')[CartKey::AMOUNT_WITH_TAX_NUMERIC]) ? $this->View()->getAssign('sBasket')[CartKey::AMOUNT_WITH_TAX_NUMERIC] : $this->View()->getAssign('sBasket')[CartKey::AMOUNT_NUMERIC];
        $order->sAmountNet = $this->View()->getAssign('sBasket')[CartKey::AMOUNT_NET_NUMERIC];
        $order->sShippingcosts = $this->View()->getAssign('sBasket')[CheckoutKey::SHIPPING_COSTS];
        $order->sShippingcostsNumeric = $this->View()->getAssign('sBasket')[CheckoutKey::SHIPPING_COSTS_WITH_TAX];
        $order->sShippingcostsNumericNet = $this->View()->getAssign('sBasket')[CheckoutKey::SHIPPING_COSTS_NET];
        $order->dispatchId = $this->session['sDispatch'];
        $order->sNet = !$this->View()->getAssign('sUserData')['additional']['charge_vat'];
        $order->deviceType = $this->Request()->getDeviceType();

        $order->sDeleteTemporaryOrder();    // Delete previous temporary orders
        $order->sCreateTemporaryOrder();    // Create new temporary order
    }

    /**
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Finish order - set some object properties to do this
     *
     * @return string|false
     */
    public function saveOrder()
    {
        $order = Shopware()->Modules()->Order();

        $normalizer = $this->container->get('shopware.components.cart.proportional_cart_normalizer');

        $order->sUserData = $this->View()->getAssign('sUserData');
        $order->sComment = $this->session['sComment'] ?? '';
        $order->sBasketData = $normalizer->normalize($this->View()->getAssign('sBasket'));
        $order->sAmount = $this->View()->getAssign('sBasket')[CheckoutKey::AMOUNT];
        $order->sAmountWithTax = !empty($this->View()->getAssign('sBasket')[CartKey::AMOUNT_WITH_TAX_NUMERIC]) ? $this->View()->getAssign('sBasket')[CartKey::AMOUNT_WITH_TAX_NUMERIC] : $this->View()->getAssign('sBasket')[CartKey::AMOUNT_NUMERIC];
        $order->sAmountNet = $this->View()->getAssign('sBasket')[CartKey::AMOUNT_NET_NUMERIC];
        $order->sShippingcosts = $this->View()->getAssign('sBasket')[CheckoutKey::SHIPPING_COSTS];
        $order->sShippingcostsNumeric = $this->View()->getAssign('sBasket')[CheckoutKey::SHIPPING_COSTS_WITH_TAX];
        $order->sShippingcostsNumericNet = $this->View()->getAssign('sBasket')[CheckoutKey::SHIPPING_COSTS_NET];
        $order->dispatchId = $this->session['sDispatch'];
        $order->sNet = !$this->View()->getAssign('sUserData')['additional']['charge_vat'];
        $order->deviceType = $this->Request()->getDeviceType();

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
        if (empty($orderNumber)) {
            return Shopware()->Snippets()->getNamespace('frontend')->get(
                'CheckoutSelectVariant',
                'Please select an option to place the required product in the cart',
                true
            );
        }

        $quantity = max(1, (int) $quantity);
        $inStock = $this->getAvailableStock($orderNumber);
        $inStock['quantity'] += $quantity;

        if (empty($inStock['articleID'])) {
            return Shopware()->Snippets()->getNamespace('frontend')->get(
                'CheckoutArticleNotFound',
                'Product could not be found.',
                true
            );
        }
        if (!empty($inStock['laststock']) || !empty(Shopware()->Config()->get('InstockInfo'))) {
            if ($inStock['instock'] <= 0 && !empty($inStock['laststock'])) {
                return Shopware()->Snippets()->getNamespace('frontend')->get(
                    'CheckoutArticleNoStock',
                    'Unfortunately we can not deliver the desired product in sufficient quantity',
                    true
                );
            }

            if ($inStock['instock'] < $inStock['quantity']) {
                $result = 'Unfortunately we can not deliver the desired product in sufficient quantity. (#0 of #1 in stock).';
                $result = Shopware()->Snippets()->getNamespace('frontend')->get(
                    'CheckoutArticleLessStock',
                    $result,
                    true
                );

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
     * @return array<string, mixed> with product id / current basket quantity / instock / laststock
     */
    public function getAvailableStock($ordernumber)
    {
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

        return Shopware()->Db()->fetchRow($sql, [
            $ordernumber,
            Shopware()->Session()->get('sessionId'),
        ]);
    }

    /**
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Get shipping costs as an array (brutto / netto) depending on selected country / payment
     *
     * @return ShippingCostArray
     */
    public function getShippingCosts()
    {
        $country = $this->getSelectedCountry();
        $payment = $this->getSelectedPayment();
        if (empty($country) || empty($payment)) {
            return sAdmin::NO_SHIPPING_COSTS;
        }
        $shippingCosts = $this->admin->sGetPremiumShippingcosts($country);

        return empty($shippingCosts) ? sAdmin::NO_SHIPPING_COSTS : $shippingCosts;
    }

    /**
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Return complete basket data to view
     * Basket items / Shippingcosts / Amounts / Tax-Rates
     *
     * @param bool $mergeProportional
     *
     * @return array<string, mixed>
     *
     * @phpstan-return CheckoutBasketArray
     */
    public function getBasket($mergeProportional = true)
    {
        $shippingCosts = $this->getShippingCosts();

        $basket = $this->basket->sGetBasket();

        $currency = $this->get('shop')->getCurrency();

        $positions = $this->container->get(BasketHelperInterface::class)->getPositionPrices(
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

        $basket[CheckoutKey::CURRENCY_ID] = $currency->getId();
        $basket[CheckoutKey::CURRENCY_NAME] = $currency->getCurrency();
        $basket[CheckoutKey::CURRENCY_FACTOR] = $currency->getFactor();

        if ($hasDifferentTaxes && empty($shippingCosts['taxMode']) && $this->get(Shopware_Components_Config::class)->get('proportionalTaxCalculation') && !$this->session->get('taxFree')) {
            $taxProportional = $taxCalculator->calculate($shippingCosts['brutto'], $positions, false);

            $basket[CheckoutKey::SHIPPING_COSTS_TAX_PROPORTIONAL] = $taxProportional;

            $shippingNet = 0;

            foreach ($taxProportional as $shippingProportional) {
                $shippingNet += $shippingProportional->getNetPrice();
            }

            $basket[CheckoutKey::SHIPPING_COSTS_WITH_TAX] = $shippingCosts['brutto'];
            $basket[CheckoutKey::SHIPPING_COSTS_NET] = $shippingNet;
            $basket[CheckoutKey::SHIPPING_COSTS_TAX] = $shippingCosts['tax'];

            $shippingCosts['netto'] = $shippingNet;
        } else {
            $basket[CheckoutKey::SHIPPING_COSTS_WITH_TAX] = $shippingCosts['brutto'];
            $basket[CheckoutKey::SHIPPING_COSTS_NET] = $shippingCosts['netto'];
            $basket[CheckoutKey::SHIPPING_COSTS_TAX] = $shippingCosts['tax'];
        }

        if (!\array_key_exists(CartKey::AMOUNT_NET_NUMERIC, $basket)) {
            $basket[CartKey::AMOUNT_NET_NUMERIC] = 0.0;
        }

        if (!\array_key_exists(CartKey::AMOUNT_NUMERIC, $basket)) {
            $basket[CartKey::AMOUNT_NUMERIC] = 0.0;
        }

        if (!empty($shippingCosts['brutto'])) {
            $basket[CartKey::AMOUNT_NET_NUMERIC] += $shippingCosts['netto'];
            $basket[CartKey::AMOUNT_NUMERIC] += $shippingCosts['brutto'];
            if (!empty($shippingCosts['difference'])) {
                $basket[CheckoutKey::SHIPPING_COSTS_DIFFERENCE] = $shippingCosts['difference']['float'];
            }
        }

        if (!empty($basket[CartKey::AMOUNT_WITH_TAX_NUMERIC])) {
            $basket[CartKey::AMOUNT_WITH_TAX_NUMERIC] += $shippingCosts['brutto'];
        }

        if (!Shopware()->System()->sUSERGROUPDATA['tax'] && Shopware()->System()->sUSERGROUPDATA['id']) {
            if (!\array_key_exists(CartKey::AMOUNT_WITH_TAX_NUMERIC, $basket)) {
                $basket[CartKey::AMOUNT_WITH_TAX_NUMERIC] = 0.0;
            }

            $basket[CheckoutKey::TAX_RATES] = $this->getTaxRates($basket);

            $basket[CheckoutKey::SHIPPING_COSTS] = $shippingCosts['netto'];
            $basket[CheckoutKey::AMOUNT] = round($basket[CartKey::AMOUNT_NET_NUMERIC], 2);
            $basket[CheckoutKey::AMOUNT_TAX] = round($basket[CartKey::AMOUNT_WITH_TAX_NUMERIC] - $basket[CartKey::AMOUNT_NET_NUMERIC], 2);
            $basket[CheckoutKey::AMOUNT_WITH_TAX] = round($basket[CartKey::AMOUNT_WITH_TAX_NUMERIC], 2);
        } else {
            $basket[CheckoutKey::TAX_RATES] = $this->getTaxRates($basket);

            $basket[CheckoutKey::SHIPPING_COSTS] = $shippingCosts['brutto'];
            $basket[CheckoutKey::AMOUNT] = $basket[CartKey::AMOUNT_NUMERIC];

            $basket[CheckoutKey::AMOUNT_TAX] = round($basket[CartKey::AMOUNT_NUMERIC] - $basket[CartKey::AMOUNT_NET_NUMERIC], 2);
        }

        $this->View()->assign(CheckoutKey::BASKET_PROPORTIONAL, $basket);

        if (\array_key_exists(CartKey::POSITIONS, $basket)
            && $mergeProportional
            && $hasDifferentTaxes
            && $this->get(Shopware_Components_Config::class)->get('proportionalTaxCalculation')
        ) {
            $basket[CartKey::POSITIONS] = $this->get('shopware.cart.proportional_cart_merger')->mergeProportionalItems($basket[CartKey::POSITIONS]);
        }

        return $basket;
    }

    /**
     * @deprecated in v5.6, will be removed in v5.8. Use TaxAggregator::taxSum instead.
     *
     * Returns tax rates for all basket positions
     *
     * @phpstan-param CheckoutBasketArray $basket
     *
     * @param array<string, mixed> $basket
     *
     * @return array<int|string, float>
     */
    public function getTaxRates($basket)
    {
        return $this->taxAggregator->taxSum($basket, (float) $this->basket->getMaxTax());
    }

    /**
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Get similar shown products to display in ajax add dialog
     *
     * @param int $articleID
     *
     * @return array<array<string, mixed>>
     */
    public function getSimilarShown($articleID)
    {
        $productIdsInCart = $this->basket->sGetBasketIds();
        Shopware()->Modules()->Marketing()->sBlacklist = $productIdsInCart ?? [];

        $similarProducts = Shopware()->Modules()->Marketing()->sGetSimilaryShownArticles($articleID);

        $similars = [];
        if (!empty($similarProducts)) {
            foreach ($similarProducts as $similarProduct) {
                $temp = Shopware()->Modules()->Articles()->sGetPromotionById('fix', 0, (int) $similarProduct['id']);
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
     * @return array<array<string, mixed>>
     */
    public function getBoughtToo($articleID)
    {
        $productIdsInCart = $this->basket->sGetBasketIds();
        Shopware()->Modules()->Marketing()->sBlacklist = $productIdsInCart ?? [];

        $alsoBoughtProducts = Shopware()->Modules()->Marketing()->sGetAlsoBoughtArticles($articleID);
        $alsoBoughts = [];
        if (!empty($alsoBoughtProducts)) {
            foreach ($alsoBoughtProducts as $alsoBoughtProduct) {
                $temp = Shopware()->Modules()->Articles()->sGetPromotionById('fix', 0, (int) $alsoBoughtProduct['id']);
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
     * @return float|false
     */
    public function getMinimumCharge()
    {
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
        return !empty(Shopware()->Config()->get('PremiumShippingNoOrder')) && (empty($this->session['sDispatch']) || empty($this->session['sCountry']));
    }

    /**
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Get all premium products that are configured and available for this order
     *
     * @return array<array<string, mixed>>
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
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Check if any electronically distribution product is in basket
     *
     * @return bool
     */
    public function getEsdNote()
    {
        $payment = empty($this->View()->getAssign('sUserData')['additional']['payment']) ? $this->session['sOrderVariables']['sUserData']['additional']['payment'] : $this->View()->getAssign('sUserData')['additional']['payment'];

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
        if (Shopware()->Config()->get('sINQUIRYVALUE')) {
            $factor = Shopware()->System()->sCurrency['factor'] ? 1 : Shopware()->System()->sCurrency['factor'];
            $value = Shopware()->Config()->get('sINQUIRYVALUE') * $factor;
            if (!Shopware()->System()->sUSERGROUPDATA['tax'] && Shopware()->System()->sUSERGROUPDATA['id']) {
                $amount = $this->View()->getAssign('sBasket')[CartKey::AMOUNT_WITH_TAX_NUMERIC];
            } else {
                $amount = $this->View()->getAssign('sBasket')[CartKey::AMOUNT_NUMERIC];
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
        return Shopware()->Config()->get('sBASEFILE') . '?sViewport=support&sFid=' . Shopware()->Config()->get('sINQUIRYID') . '&sInquiry=basket';
    }

    /**
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Get all countries from database via sAdmin object
     *
     * @return array<int, array<string, mixed>> list of countries
     */
    public function getCountryList()
    {
        return $this->admin->sGetCountryList();
    }

    /**
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Get all dispatches available in selected country from sAdmin object
     *
     * @param int|null $paymentId
     *
     * @return array<int, array<string, mixed>>|false list of dispatches
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
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Returns all available payment methods from sAdmin object
     *
     * @return array<array<string, mixed>> list of payment methods
     */
    public function getPayments()
    {
        return $this->admin->sGetPaymentMeans();
    }

    /**
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Get current selected country - if no country is selected, choose first one from list
     * of available countries
     *
     * @return array<string, mixed>|false
     */
    public function getSelectedCountry()
    {
        if (!empty($this->View()->getAssign('sUserData')['additional']['countryShipping'])) {
            $this->session['sCountry'] = (int) $this->View()->getAssign('sUserData')['additional']['countryShipping']['id'];
            $this->session['sArea'] = (int) $this->View()->getAssign('sUserData')['additional']['countryShipping']['areaID'];

            return $this->View()->getAssign('sUserData')['additional']['countryShipping'];
        }
        $countries = $this->getCountryList();
        if (empty($countries)) {
            unset($this->session['sCountry']);

            return false;
        }
        $country = reset($countries);
        $this->session['sCountry'] = (int) $country['id'];
        $this->session['sArea'] = (int) $country['areaID'];
        $this->View()->getAssign('sUserData')['additional']['countryShipping'] = $country;

        return $country;
    }

    /**
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Get current selected country - if no country is selected, choose first one from list
     * of available countries
     *
     * @return array<string, mixed> with country information
     */
    public function getSelectedState()
    {
        if (!empty($this->View()->getAssign('sUserData')['additional']['stateShipping'])) {
            $this->session['sState'] = (int) $this->View()->getAssign('sUserData')['additional']['stateShipping']['id'];

            return $this->View()->getAssign('sUserData')['additional']['stateShipping'];
        }

        return ['id' => $this->session['sState']];
    }

    /**
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Get selected payment or do payment mean selection automatically
     *
     * @return array<string, mixed>|false
     */
    public function getSelectedPayment()
    {
        $payment = null;
        $paymentMethods = $this->getPayments();

        if (!empty($this->View()->getAssign('sUserData')['additional']['payment'])) {
            $payment = $this->View()->getAssign('sUserData')['additional']['payment'];
        } elseif (!empty($this->session['sPaymentID'])) {
            $payment = $this->admin->sGetPaymentMeanById($this->session['sPaymentID'], $this->View()->getAssign('sUserData'));
        }

        if ($payment && !$this->checkPaymentAvailability($payment, $paymentMethods)) {
            $payment = null;
        }

        $paymentClass = $this->admin->sInitiatePaymentClass($payment);
        if ($payment && $paymentClass instanceof BasePaymentMethod) {
            $data = $paymentClass->getCurrentPaymentDataAsArray(Shopware()->Session()->get('sUserId'));
            $payment['validation'] = $paymentClass->validate($data ?? []);
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
        $this->front->ensureRequest()->setPost('sPayment', (int) $payment['id']);
        $this->admin->sUpdatePayment();

        // if customer logged in and payment switched to fallback, display cart notice. Otherwise anonymous customers will see the message too
        if (Shopware()->Session()->get('sUserId')) {
            $this->flagPaymentBlocked();
        }

        return $payment;
    }

    /**
     * @deprecated in 5.6, will be protected in 5.8
     *
     * Get selected dispatch or select a default dispatch
     *
     * @return array<string, mixed>|false
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
     * @param int      $dispatchId $dispatchId ID of the dispatch method to set
     * @param int|null $paymentId  Payment id to validate
     *
     * @return int set dispatch method id
     */
    public function setDispatch($dispatchId, $paymentId = null)
    {
        $supportedDispatches = $this->getDispatches($paymentId);
        if (!\is_array($supportedDispatches)) {
            $supportedDispatches = [];
        }

        // Iterate over supported dispatches, look for the provided one
        foreach ($supportedDispatches as $dispatch) {
            if ($dispatch['id'] == $dispatchId) {
                $this->session['sDispatch'] = $dispatchId;

                return $dispatchId;
            }
        }

        // If it was not found, we fall back to the default (head of supported)
        $defaultDispatch = array_shift($supportedDispatches);
        $this->session['sDispatch'] = (int) ($defaultDispatch['id'] ?? 0);

        return $this->session['sDispatch'];
    }

    /**
     * Ajax add product action
     *
     * This action will get redirected from the default addArticleAction
     * when the request was an AJAX request.
     *
     * @return void
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
     * @return void
     */
    public function ajaxAddArticleCartAction()
    {
        $this->validatePostMethod();

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
     *
     * @return void
     */
    public function ajaxDeleteArticleCartAction()
    {
        $this->validatePostMethod();

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
     *
     * @return void
     */
    public function ajaxCartAction()
    {
        $view = $this->View();

        // Necessary to call this first in order for 'sDispatch' session variable to be set
        // The basket will need this session variable to properly calculate the shipping costs
        $country = $this->getSelectedCountry();
        $dispatch = $this->getSelectedDispatch();
        $basket = $this->getBasket();

        $view->assign('sCountryList', $this->getCountryList());
        $view->assign('sState', $this->getSelectedState());
        $view->assign('sPayments', $this->getPayments());
        $view->assign('sCountry', $country);
        $view->assign('sPayment', $this->getSelectedPayment());

        $view->assign('sDispatch', $dispatch);
        $view->assign('sBasket', $basket);
        $this->View()->assign('sInvalidCartItems', $this->getInvalidProducts($basket));

        $view->assign(CheckoutKey::SHIPPING_COSTS, $basket[CheckoutKey::SHIPPING_COSTS]);
        $view->assign(CheckoutKey::SHIPPING_COSTS_DIFFERENCE, $basket[CheckoutKey::SHIPPING_COSTS_DIFFERENCE] ?? null);
        $view->assign(CheckoutKey::AMOUNT, $basket[CheckoutKey::AMOUNT]);
        $view->assign(CheckoutKey::AMOUNT_WITH_TAX, $basket[CheckoutKey::AMOUNT_WITH_TAX] ?? 0.0);
        $view->assign(CheckoutKey::AMOUNT_TAX, $basket[CheckoutKey::AMOUNT_TAX]);
        $view->assign('sAmountNet', $basket[CartKey::AMOUNT_NET_NUMERIC]);
        $view->assign('sDispatches', $this->getDispatches());
        $accountMode = (int) ($this->View()->getAssign('sUserData')['additional']['user']['accountmode'] ?? null);
        $view->assign('sDispatchNoOrder', $accountMode === 0 && $this->getDispatchNoOrder());
        $view->assign('showShippingCalculation', (bool) $this->Request()->getParam('openShippingCalculations'));
        $view->assign('sMinimumSurcharge', $this->getMinimumCharge());
    }

    /**
     * Get current amount from cart via ajax to display in realtime
     *
     * @return void
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
     *
     * @return void
     */
    public function setAddressAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
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
     * @param array<string, mixed> $userData
     */
    protected function isTaxFreeDelivery(array $userData): bool
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
     * @param CheckoutBasketArray $basket
     *
     * @return array<string, bool>
     */
    private function getInvalidAgreements(array $basket, Request $request): array
    {
        $errors = [];

        if (!$this->container->get(Shopware_Components_Config::class)->get('IgnoreAGB') && !$this->Request()->getParam('sAGB')) {
            $errors['agbError'] = true;
        }

        $esdAgreement = $request->getParam('esdAgreementChecked');
        if ($this->container->get(Shopware_Components_Config::class)->get('showEsdWarning')
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
     * @param array<string, mixed>        $currentPayment
     * @param array<array<string, mixed>> $payments
     */
    private function checkPaymentAvailability(array $currentPayment, array $payments): bool
    {
        foreach ($payments as $availablePayment) {
            if ($availablePayment['id'] === $currentPayment['id']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string|array<string>      $accessories
     * @param array<numeric-string|int> $quantities
     */
    private function addAccessories($accessories, array $quantities): void
    {
        if (\is_string($accessories)) {
            $accessories = explode(';', $accessories);
        }

        if (empty($accessories) || !\is_array($accessories)) {
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
     * Helper function that checks whether the given basket has an esd product in it.
     *
     * @param CheckoutBasketArray $basket
     */
    private function basketHasEsdProducts(array $basket): bool
    {
        if (!isset($basket[CartKey::POSITIONS])) {
            return false;
        }

        foreach ($basket[CartKey::POSITIONS] as $cartItem) {
            if ($cartItem['esd']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Helper function that iterates through the basket products.
     * It checks if a product is a service product by comparing its attributes
     * with the plugin config serviceAttrField value.
     *
     * @param CheckoutBasketArray $basket
     */
    private function basketHasServiceProducts(array $basket): bool
    {
        $config = Shopware()->Config();

        if (!$config->offsetExists('serviceAttrField')) {
            return false;
        }

        $attrName = $config->offsetGet('serviceAttrField');
        if (empty($attrName) || !isset($basket[CartKey::POSITIONS])) {
            return false;
        }

        foreach ($basket[CartKey::POSITIONS] as $cartItem) {
            $serviceAttr = $cartItem['additional_details'][$attrName];

            if ($serviceAttr && $serviceAttr !== 'false') {
                return true;
            }
        }

        return false;
    }

    /**
     * Helper function that iterates through the basket products.
     * It checks if the basket has a normal product e.g. not an esd product
     * and not a product with the service attribute is set to true.
     *
     * @param CheckoutBasketArray $basket
     */
    private function basketHasMixedProducts(array $basket): bool
    {
        $config = Shopware()->Config();
        $attrName = $config->get('serviceAttrField');

        if (!isset($basket[CartKey::POSITIONS])) {
            return false;
        }

        foreach ($basket[CartKey::POSITIONS] as $cartItem) {
            if ((int) $cartItem['modus'] === CartPositionsMode::PAYMENT_SURCHARGE_OR_DISCOUNT || $cartItem['esd']) {
                continue;
            }

            if (empty($attrName)) {
                continue;
            }
            $serviceAttr = $cartItem['additional_details'][$attrName];
            if ($serviceAttr && $serviceAttr !== 'false') {
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
    private function onPaymentMethodValidationFail(): void
    {
        $this->redirect(['controller' => 'checkout', 'action' => 'shippingPayment']);
    }

    /**
     * Selects the default payment method defined in the backend. If no payment method is defined,
     * the first payment method of the provided list will be returned.
     *
     * @param non-empty-array<array<string, mixed>> $paymentMethods
     *
     * @return array<string, mixed>
     */
    private function getDefaultPaymentMethod(array $paymentMethods): array
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
    private function resetTemporaryAddresses(): void
    {
        $this->session->offsetUnset('checkoutBillingAddressId');
        $this->session->offsetUnset('checkoutShippingAddressId');
    }

    /**
     * Sets the default addresses for the user if he decided to use the temporary addresses as new default
     */
    private function saveDefaultAddresses(): void
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

        if ($billingId && $billingId != $this->View()->getAssign('sUserData')['additional']['user']['default_billing_address_id']) {
            $address = $this->get(ModelManager::class)
                ->getRepository(Address::class)
                ->getOneByUser(
                    $billingId,
                    $this->View()->getAssign('sUserData')['additional']['user']['id']
                );

            $this->get(AddressServiceInterface::class)->setDefaultBillingAddress($address);
        }

        if ($shippingId && $shippingId != $this->View()->getAssign('sUserData')['additional']['user']['default_shipping_address_id']) {
            $address = $this->get(ModelManager::class)
                ->getRepository(Address::class)
                ->getOneByUser(
                    $shippingId,
                    $this->View()->getAssign('sUserData')['additional']['user']['id']
                );

            $this->get(AddressServiceInterface::class)->setDefaultShippingAddress($address);
        }
    }

    /**
     * Validates the given address id with current shop configuration
     */
    private function isValidAddress(int $addressId, bool $isShippingAddress = false): bool
    {
        $address = $this->get(ModelManager::class)->find(Address::class, $addressId);
        if (!$address instanceof Address || !$address->getCountry() instanceof Country) {
            $this->View()->assign('invalidShippingCountry', true);

            return false;
        }

        $context = $this->get(ContextServiceInterface::class)->getContext();
        $country = $this->get(CountryGatewayInterface::class)->getCountry($address->getCountry()->getId(), $context);

        if ($isShippingAddress && !$country->allowShipping()) {
            $this->View()->assign('invalidShippingCountry', true);

            return false;
        }

        return $this->get(AddressValidatorInterface::class)->isValid($address);
    }

    /**
     * @return array<string, mixed>
     */
    private function getOrderAddress(string $orderNumber, string $source): array
    {
        $builder = $this->get(Connection::class)->createQueryBuilder();
        $context = $this->get(ContextServiceInterface::class)->getShopContext();

        $sourceTable = $source === 'billing' ? 's_order_billingaddress' : 's_order_shippingaddress';

        $address = $builder->select(['address.*'])
            ->from($sourceTable, 'address')
            ->join('address', 's_order', '', 'address.orderID = s_order.id AND s_order.ordernumber = :orderNumber')
            ->setParameter('orderNumber', $orderNumber)
            ->execute()
            ->fetch();

        $countryStruct = $this->get(CountryGatewayInterface::class)->getCountry($address['countryID'], $context);
        $stateStruct = $this->get(CountryGatewayInterface::class)->getState($address['stateID'], $context);

        $address['country'] = json_decode(json_encode($countryStruct, JSON_THROW_ON_ERROR), true);
        $address['state'] = json_decode(json_encode($stateStruct, JSON_THROW_ON_ERROR), true);
        $address['attribute'] = $this->get(DataLoader::class)->load($sourceTable . '_attributes', $address['id']);

        return $address;
    }

    /**
     * @param array<string, mixed> $addressA
     * @param array<string, mixed> $addressB
     */
    private function areAddressesEqual(array $addressA, array $addressB): bool
    {
        // 'country' and 'state' are arrays, but not relevant as the IDs are also present which will then be compared
        foreach (['id', 'userID', 'orderID', 'customernumber', 'phone', 'ustid', 'country', 'state', 'attribute'] as $key) {
            unset($addressA[$key], $addressB[$key]);
        }

        return \count(array_diff($addressA, $addressB)) === 0;
    }

    private function persistBasket(): string
    {
        $generator = $this->get('basket_signature_generator');
        $basket = $this->session->offsetGet('sOrderVariables')->getArrayCopy();
        $signature = $generator->generateSignature($basket['sBasket'], $this->session->get('sUserId'));

        $persister = $this->get('basket_persister');
        $persister->persist($signature, $basket);

        return $signature;
    }

    /**
     * Updates all currency dependencies (e.g. in the shop model or in the shop context).
     */
    private function updateCurrencyDependencies(int $currencyId): void
    {
        $currencyModel = $this->get(ModelManager::class)->find(Currency::class, $currencyId);

        $shopModel = $this->get('shop');
        $shopModel->setCurrency($currencyModel);

        $currency = $this->get('currency');
        $currency->setFormat($currencyModel->toArray());

        $this->get(ContextServiceInterface::class)->initializeShopContext();
    }

    /**
     * @param CheckoutBasketArray $basket
     *
     * @return list<string>
     */
    private function getInvalidProducts(array $basket): array
    {
        if (!isset($basket[CartKey::POSITIONS])) {
            return [];
        }

        $products = [];
        foreach ($basket[CartKey::POSITIONS] as $item) {
            if ((int) $item['modus'] !== CartPositionsMode::PRODUCT) {
                continue;
            }

            if (!empty($item['additional_details'])) {
                continue;
            }

            $products[] = $item['articlename'];
        }

        return $products;
    }

    private function isShippingAllowed(int $countryId): bool
    {
        $queryBuilder = $this->get('dbal_connection')->createQueryBuilder();

        $allowedByDefault = (bool) $queryBuilder->select('allow_shipping')
            ->from('s_core_countries', 'countries')
            ->where('countries.id = :countryId')
            ->setParameter(':countryId', $countryId)
            ->execute()
            ->fetchOne();

        $countryTranslations = $this->get('modules')->sAdmin()->sGetCountryTranslation();

        if (!$countryTranslations) {
            return $allowedByDefault;
        }

        if (!\array_key_exists($countryId, $countryTranslations)) {
            return $allowedByDefault;
        }

        if (!\array_key_exists('allow_shipping', $countryTranslations[$countryId])) {
            return $allowedByDefault;
        }

        return (bool) $countryTranslations[$countryId]['allow_shipping'];
    }

    private function validatePostMethod(): void
    {
        if (strtoupper($this->Request()->getMethod()) !== SymfonyRequest::METHOD_POST) {
            throw new LogicException('This action only admits post requests');
        }
    }

    private function updateNewsletter(): void
    {
        if (!empty($this->session['sNewsletter'])) {
            $this->admin->sUpdateNewsletter(true, (string) $this->admin->sGetUserMailById(), true);
        }
    }
}
