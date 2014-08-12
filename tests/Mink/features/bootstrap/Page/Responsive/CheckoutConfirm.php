<?php
namespace Responsive;

class CheckoutConfirm extends \Emotion\CheckoutConfirm
{
    public $cssLocator = array(
        'pageIdentifier'  => 'div#confirm--content',
        'shippingPaymentForm' => 'form.payment',
        'proceedCheckoutForm' => 'form#confirm--form',
        'orderNumber' => 'div.finish--details > div.panel--body'
    );
}
