<?php
namespace Shopware\Tests\Mink\Page\Responsive;

use Shopware\Tests\Mink\Helper;

class CheckoutConfirm extends \Shopware\Tests\Mink\Page\Emotion\CheckoutConfirm
{
    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'shippingPaymentForm' => 'form.payment',
            'proceedCheckoutForm' => 'form#confirm--form',
            'orderNumber' => 'div.finish--details > div.panel--body'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [
            'gtc' => ['de' => 'AGB und Widerrufsbelehrung', 'en' => 'Terms, conditions and cancellation policy'],
            'confirmButton' => ['de' => 'Zahlungspflichtig bestellen', 'en' => 'Send order'],
            'changePaymentButton' => ['de' => 'Weiter', 'en' => 'Next']
        ];
    }

    /**
     * Changes the payment method
     * @param array   $data
     */
    public function changePaymentMethod(array $data = [])
    {
        $data[0]['field'] = 'payment';
        $this->changeShippingMethod($data);
    }

    /**
     * Changes the shipping method
     * @param array $data
     */
    public function changeShippingMethod(array $data = [])
    {
        $element = $this->getElement('CheckoutPayment');
        Helper::clickNamedLink($element, 'changeButton');

        Helper::fillForm($this, 'shippingPaymentForm', $data);
        Helper::pressNamedButton($this, 'changePaymentButton');
    }
}
