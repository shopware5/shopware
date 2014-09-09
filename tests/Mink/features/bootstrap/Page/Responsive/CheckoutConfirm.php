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

    /** @var array $namedSelectors */
    public $namedSelectors = array(
        'confirmButton'         => array('de' => 'Zahlungspflichtig bestellen', 'en' => 'Send order'),
        'changePaymentButton'   => array('de' => 'Weiter',                      'en' => 'Next'),
    );

    /**
     * Changes the payment method
     * @param array   $data
     */
    public function changePayment($data = array())
    {
        $data[0]['field'] = 'payment';
        $this->changeShipping($data);
    }

    /**
     * @param array $data
     */
    public function changeShipping($data = array())
    {
        $element = $this->getElement('CheckoutPayment');
        $language = \Helper::getCurrentLanguage($this);
        \Helper::clickNamedLink($element, 'changeButton', null, $language);

        \Helper::fillForm($this, 'shippingPaymentForm', $data);
        \Helper::pressNamedButton($this, 'changePaymentButton');
    }
}
