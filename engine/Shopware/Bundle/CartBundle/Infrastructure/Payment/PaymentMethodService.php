<?php

namespace Shopware\Bundle\CartBundle\Infrastructure\Payment;

use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\Cart\CartContextInterface;

class PaymentMethodService
{
    /**
     * @var PaymentMethodGateway
     */
    private $gateway;

    /**
     * @param PaymentMethodGateway $gateway
     */
    public function __construct(PaymentMethodGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    public function getAvailable(CalculatedCart $cart, CartContextInterface $context)
    {
        $payments = $this->gateway->getAll($context);

        return $payments;
    }
}
