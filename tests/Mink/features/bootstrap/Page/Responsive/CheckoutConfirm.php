<?php
namespace Page\Responsive;

class CheckoutConfirm extends \Page\Emotion\CheckoutConfirm
{
    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'shippingPaymentForm' => 'form.payment',
            'proceedCheckoutForm' => 'form#confirm--form',
            'orderNumber' => 'div.finish--details > div.panel--body'
        );
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return array(
            'gtc' => array('de' => 'AGB und Widerrufsbelehrung', 'en' => 'Terms, conditions and cancellation policy'),
            'confirmButton' => array('de' => 'Zahlungspflichtig bestellen', 'en' => 'Send order'),
            'changePaymentButton' => array('de' => 'Weiter', 'en' => 'Next')
        );
    }

    /**
     * Changes the payment method
     * @param array   $data
     */
    public function changePaymentMethod($data = array())
    {
        $data[0]['field'] = 'payment';
        $this->changeShippingMethod($data);
    }

    /**
     * Changes the shipping method
     * @param array $data
     */
    public function changeShippingMethod($data = array())
    {
        $element = $this->getElement('CheckoutPayment');
        $language = \Helper::getCurrentLanguage($this);
        \Helper::clickNamedLink($element, 'changeButton', $language);

        \Helper::fillForm($this, 'shippingPaymentForm', $data);
        \Helper::pressNamedButton($this, 'changePaymentButton');
    }
}
