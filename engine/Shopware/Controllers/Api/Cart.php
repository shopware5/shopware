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

use Shopware\Bundle\CartBundle\Domain\Cart\CartContainer;
use Shopware\Bundle\CartBundle\Domain\Exception\CartTokenNotFoundException;
use Shopware\Bundle\CartBundle\Domain\Exception\LineItemNotFoundException;
use Shopware\Bundle\CartBundle\Domain\LineItem\LineItem;
use Shopware\Bundle\CartBundle\Domain\Price\PriceDefinition;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRuleCollection;
use Shopware\Bundle\StoreFrontBundle\Context\CheckoutScope;
use Shopware\Bundle\StoreFrontBundle\Context\CustomerScope;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;
use Shopware\Bundle\StoreFrontBundle\Context\ShopScope;
use Shopware\Components\Api\Exception\ParameterMissingException;

class Shopware_Controllers_Api_Cart extends Shopware_Controllers_Api_Rest
{
    public function pickUpAction(): void
    {
        $cart = CartContainer::createNew('api');

        $context = $this->container->get('storefront.context.factory')
            ->create(
                new ShopScope(
                    (int) $this->Request()->getParam('shopId'),
                    (int) $this->Request()->getParam('currencyId')
                ),
                new CustomerScope(
                    $this->getIntParam('customerId'),
                    $this->Request()->getParam('customerGroupKey', null),
                    $this->getIntParam('billingId'),
                    $this->getIntParam('shippingId')
                ),
                new CheckoutScope(
                    $this->getIntParam('paymentId'),
                    $this->getIntParam('dispatchId'),
                    $this->getIntParam('countryId'),
                    $this->getIntParam('stateId')
                )
            );

        $this->container->get('shopware.cart.persister')->save($cart);
        $this->saveContext($cart->getToken(), $context);

        $this->View()->assign([
            'token' => $cart->getToken(),
        ]);
    }

    public function changeContextAction(): void
    {
        $token = $this->Request()->getParam('token');
        if (!$token) {
            throw new ParameterMissingException('token');
        }

        $context = $this->loadContext($token);

        $shopScope = ShopScope::createFromContext($context);

        $customerScope = CustomerScope::createFromContext($context);

        $checkoutScope = CheckoutScope::createFromContext($context);

        $context = $this->container->get('storefront.context.factory')
            ->create(
                new ShopScope(
                    $this->getIntParam('shopId', $shopScope->getShopId()),
                    $this->getIntParam('currencyId', $shopScope->getCurrencyId())
                ),
                new CustomerScope(
                    $this->getIntParam('customerId', $customerScope->getCustomerId()),
                    $this->Request()->getParam('customerGroupKey', $customerScope->getCustomerGroupKey()),
                    $this->getIntParam('billingId', $customerScope->getBillingId()),
                    $this->getIntParam('shippingId', $customerScope->getShippingId())
                ),
                new CheckoutScope(
                    $this->getIntParam('paymentId', $checkoutScope->getPaymentId()),
                    $this->getIntParam('dispatchId', $checkoutScope->getDispatchId()),
                    $this->getIntParam('countryId', $checkoutScope->getCountryId()),
                    $this->getIntParam('stateId', $checkoutScope->getStateId())
                )
            );

        $this->saveContext($token, $context);
    }

    public function addLineItemAction(): void
    {
        $token = $this->Request()->getParam('token');
        if (!$token) {
            throw new ParameterMissingException('token');
        }

        $identifier = $this->Request()->getParam('identifier');
        if (!$identifier) {
            throw new ParameterMissingException('identifier');
        }

        $quantity = $this->Request()->getParam('quantity');
        if (!$quantity) {
            throw new ParameterMissingException('quantity');
        }

        $type = $this->Request()->getParam('type');
        if (!$type) {
            throw new ParameterMissingException('type');
        }

        $price = $this->Request()->getParam('price', null);

        if ($price !== null) {
            $deserializer = $this->container->get('storefront.serializer.json');

            $taxes = new TaxRuleCollection([
                $deserializer->deserialize(
                    $this->Request()->getParam('taxes', [])
                ),
            ]);

            $isCalculated = (bool) $this->Request()->getParam('isCalculated', true);

            $price = new PriceDefinition($price, $taxes, $quantity, $isCalculated);
        }

        $cart = $this->container->get('shopware.cart.persister')->load($token);

        $cart->getLineItems()->add(
            new LineItem($identifier, $type, $quantity, $this->Request()->getParams(), $price)
        );

        $this->container->get('shopware.cart.persister')->save($cart);
    }

    public function removeLineItemAction(): void
    {
        $token = $this->Request()->getParam('token');
        if (!$token) {
            throw new ParameterMissingException('token');
        }

        $identifier = $this->Request()->getParam('identifier');
        if (!$identifier) {
            throw new ParameterMissingException('identifier');
        }

        $cart = $this->container->get('shopware.cart.persister')->load($token);

        if (!$cart->getLineItems()->has($identifier)) {
            throw new LineItemNotFoundException($identifier);
        }

        $cart->getLineItems()->remove($identifier);

        $this->container->get('shopware.cart.persister')->save($cart);
    }

    public function changeQuantityAction(): void
    {
        $token = $this->Request()->getParam('token');
        if (!$token) {
            throw new ParameterMissingException('token');
        }

        $identifier = $this->Request()->getParam('identifier');
        if (!$identifier) {
            throw new ParameterMissingException('identifier');
        }

        $quantity = $this->Request()->getParam('quantity');
        if (!$quantity) {
            throw new ParameterMissingException('quantity');
        }

        $cart = $this->container->get('shopware.cart.persister')->load($token);

        if (!$cart->getLineItems()->has($identifier)) {
            throw new LineItemNotFoundException($identifier);
        }

        $cart->getLineItems()->get($identifier)->setQuantity($quantity);

        $this->container->get('shopware.cart.persister')->save($cart);
    }

    public function getAction(): void
    {
        $token = $this->Request()->getParam('token');
        if (!$token) {
            throw new ParameterMissingException('token');
        }

        $cart = $this->container->get('shopware.cart.persister')->load($token);

        $context = $this->loadContext($token);

        $calculated = $this->container->get('cart.calculator')
            ->calculate($cart, $context);

        $this->container->get('shopware.cart.persister')->save($cart);

        $this->saveContext($cart->getToken(), $this->loadContext($token));

        $this->View()->assign(['cart' => $calculated]);
    }

    public function orderAction(): void
    {
        $token = $this->Request()->getParam('token');
        if (!$token) {
            throw new ParameterMissingException('token');
        }

        $cart = $this->container->get('shopware.cart.persister')->load($token);

        $context = $this->loadContext($token);

        $calculated = $this->container->get('cart.calculator')
            ->calculate($cart, $context);

        $this->container->get('shopware.cart.order.persister')
            ->persist($calculated, $context);
    }

    private function getIntParam(string $param, ?int $fallback = null): ?int
    {
        if ($this->Request()->getParam($param)) {
            return (int) $this->Request()->getParam($param);
        }

        return $fallback;
    }

    private function saveContext($token, ShopContextInterface $context): void
    {
        $this->container->get('dbal_connection')->executeUpdate(
            'INSERT INTO `s_cart_api_context` (`token`, `content`) 
             VALUES (:token, :content)
             ON DUPLICATE KEY UPDATE `content` = :content',
            [
                ':token' => $token,
                ':content' => $this->container->get('storefront.serializer.json')->serialize($context),
            ]
        );
    }

    private function loadContext($token): ShopContextInterface
    {
        $content = $this->container->get('dbal_connection')->fetchColumn(
            'SELECT content FROM s_cart_api_context WHERE `token` = :token',
            [':token' => $token]
        );

        if ($content === false) {
            throw new CartTokenNotFoundException($token);
        }

        return $this->container->get('storefront.serializer.json')->deserialize($content);
    }
}
