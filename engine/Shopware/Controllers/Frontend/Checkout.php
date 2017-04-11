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
use Shopware\Bundle\CartBundle\Domain\Error\PaymentBlockedError;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItem;
use Shopware\Bundle\CartBundle\Domain\Product\ProductProcessor;
use Shopware\Bundle\CartBundle\Domain\Voucher\VoucherProcessor;
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

    const TARGET_ACTION_KEY = 'sTargetAction';

    public function cartAction(): void
    {
        $context = $this->container->get('storefront.context.service')->getShopContext();

        $cart = $this->container->get('shopware.cart.storefront_service')->getCart();

        $this->View()->assign([
            'context' => $this->serialize($context),
            'cart' => $this->serialize($cart),
            self::TARGET_ACTION_KEY => self::ACTION_CART,
        ]);
    }

    public function confirmAction(): void
    {
        $context = $this->container->get('storefront.context.service')->getShopContext();

        if ($context->getCustomer() === null) {
            $this->forwardToLogin();

            return;
        }

        $cart = $this->container->get('shopware.cart.storefront_service')->getCart();

        if ($cart->getViewLineItems()->count() === 0) {
            $this->redirect(['action' => self::ACTION_CART]);

            return;
        }

        if ($cart->getErrors()->has(PaymentBlockedError::class)) {
            $context = $this->container->get('storefront.context.switcher')
                ->switchContext(null, $context->getShop()->getPaymentMethod()->getId());
        }

        $this->View()->assign([
            'context' => $this->serialize($context),
            'cart' => $this->serialize($cart),
            self::TARGET_ACTION_KEY => self::ACTION_CONFIRM,
        ]);
    }

    public function shippingPaymentAction()
    {
        $cart = $this->container->get('shopware.cart.storefront_service')->getCart();

        $context = $this->container->get('storefront.context.service')->getShopContext();

        $paymentMethods = $this->container->get('storefront.payment_method.service')->getAvailable(
            $cart->getCalculatedCart(),
            $context
        );

        $shippingMethods = $this->container->get('storefront.shipping_method.service')
            ->getAvailable($cart->getCalculatedCart(), $context);

        if ($this->Request()->getParam('isXHR')) {
            $this->View()->loadTemplate('frontend/checkout/shipping_payment_core.tpl');
        }

        $this->View()->assign([
            'context' => $this->serialize($context),
            'cart' => $this->serialize($cart),
            'paymentMethods' => $this->serialize($paymentMethods),
            'shippingMethods' => $this->serialize($shippingMethods),
            'currentShippingMethodId' => $context->getShippingMethod()->getId(),
            'currentPaymentId' => $context->getPaymentMethod()->getId(),
            self::TARGET_ACTION_KEY => 'shippingPayment',
        ]);
    }

    public function ajaxCartAction()
    {
        Shopware()->Plugins()->Controller()->Json()->setPadding();

        $context = $this->container->get('storefront.context.service')->getShopContext();

        $cart = $this->container->get('shopware.cart.storefront_service')->getCart();

        $this->View()->assign([
            'context' => $this->serialize($context),
            'cart' => $this->serialize($cart),
            self::TARGET_ACTION_KEY => self::ACTION_AJAX_CART,
        ]);
    }

    public function ajaxAmountAction()
    {
        $cart = $this->container->get('shopware.cart.storefront_service')->getCart();

        $quantity = $cart->getCalculatedCart()->getCalculatedLineItems()->filterGoods()->count();

        $this->View()->assign(['amount' => $cart->getPrice()->getTotalPrice()]);

        $template = $this->View()->fetch('frontend/checkout/ajax_amount.tpl');

        Shopware()->Plugins()->Controller()->Json()->setPadding();

        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        $this->Response()->setBody(json_encode(['amount' => $template, 'quantity' => $quantity]));
    }

    public function addProductAction()
    {
        $number = $this->Request()->getParam('number');
        if (!$number) {
            throw new Exception('No product number given');
        }

        $quantity = (int) $this->Request()->getParam('quantity', 1);

        $this->container->get('shopware.cart.storefront_service')->add(
            new LineItem($number, ProductProcessor::TYPE_PRODUCT, $quantity)
        );

        $this->forward(
            $this->Request()->getParam(self::TARGET_ACTION_KEY, self::ACTION_AJAX_CART)
        );
    }

    public function removeLineItemAction()
    {
        $identifier = $this->Request()->getParam('identifier');

        $this->container->get('shopware.cart.storefront_service')->remove($identifier);

        $this->forward(
            $this->Request()->getParam(self::TARGET_ACTION_KEY, self::ACTION_AJAX_CART)
        );
    }

    public function addVoucherAction()
    {
        $code = $this->Request()->getParam('code');
        if (!$code) {
            throw new Exception('No voucher code provided');
        }

        $this->container->get('shopware.cart.storefront_service')->add(
            new LineItem('voucher', VoucherProcessor::TYPE_VOUCHER, 1, ['code' => $code])
        );

        $this->redirect([
            'action' => $this->Request()->getParam(self::TARGET_ACTION_KEY, self::ACTION_CART),
        ]);
    }

    public function changeQuantityAction()
    {
        if (!$this->Request()->isPost()) {
            throw new Exception('Only post request allowed');
        }

        $service = $this->container->get('shopware.cart.storefront_service');

        $identifier = $this->Request()->getPost('identifier');
        if (!$identifier) {
            throw new Exception('Missing parameter identifier');
        }

        $quantity = $this->Request()->getPost('quantity');
        if (!$quantity) {
            throw new Exception('Missing parameter quantity');
        }

        $service->changeQuantity($identifier, $quantity);

        $this->forward(
            $this->Request()->getParam(self::TARGET_ACTION_KEY, self::ACTION_CART)
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

        $service = $this->container->get('shopware.cart.storefront_service');

        $number = $this->Request()->getParam('number');

        $product = $service->getCart()->getViewLineItems()->get($number);

        $this->View()->assign(['lineItem' => $this->serialize($product)]);
    }

    /**
     * Called from confirmAction View
     * Customers requests to finish current order
     * Check if all conditions match and save order
     */
    public function finishAction()
    {
        $cart = $this->container->get('shopware.cart.storefront_service')->getCart();

        $context = $this->container->get('storefront.context.service')->getShopContext();

        $this->container->get('shopware.cart.storefront_service')->order();

        $this->View()->assign([
            'cart' => $this->serialize($cart),
            'context' => $this->serialize($context),
        ]);
    }

    /**
     * Action to simultaneously save shipping and payment details
     */
    public function saveShippingPaymentAction()
    {
        if (!$this->Request()->isPost()) {
            $this->forward('shippingPayment');

            return;
        }

        $this->container->get('storefront.context.switcher')
            ->switchContext(
                (int) $this->Request()->getPost('shippingMethodId'),
                (int) $this->Request()->getPost('paymentMethodId')
            );

        if ($this->Request()->getParam('isXHR')) {
            $this->forward('shippingPayment');

            return;
        }

        $this->redirect([
            'controller' => $this->Request()->getParam('sTarget', 'checkout'),
            'action' => $this->Request()->getParam(self::TARGET_ACTION_KEY, 'confirm'),
        ]);
    }

    /**
     * Sets a temporary session variable which holds an address for the current order
     */
    public function setAddressAction()
    {
        $this->View()->loadTemplate('');
        $target = $this->Request()->getParam('target', 'shipping');
        $sessionKey = $target === 'shipping' ? 'checkoutShippingAddressId' : 'checkoutBillingAddressId';

        $session = $this->container->get('session');
        $session->offsetSet($sessionKey, $this->Request()->getParam('addressId', null));
        if ($target === 'both') {
            $session->offsetSet('checkoutShippingAddressId', $this->Request()->getParam('addressId', null));
            $session->offsetSet('checkoutBillingAddressId', $this->Request()->getParam('addressId', null));
        }
    }

    private function forwardToLogin()
    {
        $this->forward('login', 'account', null, ['sTarget' => 'checkout', self::TARGET_ACTION_KEY => self::ACTION_CONFIRM, 'showNoAccount' => true]);
    }
}
