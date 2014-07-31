<?php
namespace Responsive;

class CheckoutConfirm extends \Emotion\CheckoutConfirm
{
    public $cssLocator = array(
        'pageIdentifier'  => 'div#confirm',
        'shippingPaymentForm' => 'form.payment',
        'proceedCheckoutForm' => 'div.additional_footer > form'
    );
}
