<?php
namespace Page\Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class CheckoutConfirm extends Page implements \HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = '/checkout/confirm';

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'pageIdentifier'  => 'div#confirm',
            'deliveryForm' => 'form.payment',
            'deliveryFormSubmit' => 'form.payment input[type="submit"]',
            'proceedCheckoutForm' => 'div.additional_footer > form',
            'orderNumber' => 'div#finished > div.orderdetails > p'
        );
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return array(
            'confirmButton'  => array('de' => 'Zahlungspflichtig bestellen', 'en' => 'Send order')
        );
    }

    public function verifyPage()
    {
        $locators = array('pageIdentifier');
        $elements = \Helper::findElements($this, $locators, false);

        if (!empty($elements['pageIdentifier'])) {
            return;
        }

        $message = array('You are not on CheckoutConfirm page!', 'Current URL: '. $this->getSession()->getCurrentUrl());
        \Helper::throwException($message);
    }

    public function getOrderNumber()
    {
        $locators = array('orderNumber');
        $elements = \Helper::findElements($this, $locators);

        $orderDetails = $elements['orderNumber']->getText();

        preg_match("/\d+/",$orderDetails,$orderNumber);
        $orderNumber = intval($orderNumber[0]);

        return $orderNumber;
    }

    /**
     * Proceeds the checkout
     */
    public function proceedToCheckout()
    {
        $this->checkField('sAGB');
        \Helper::pressNamedButton($this, 'confirmButton');
    }

    /**
     * Changes the payment method
     * @param array   $data
     */
    public function changePaymentMethod($data = array())
    {
        $element = $this->getElement('CheckoutPayment');
        $language = \Helper::getCurrentLanguage($this);
        \Helper::clickNamedLink($element, 'changeButton', $language);

        $account = $this->getPage('Account');
        \Helper::fillForm($account, 'paymentForm', $data);
        \Helper::pressNamedButton($account, 'changePaymentButton', $language);
    }

    /**
     * @param array $data
     */
    public function changeShippingAddress($data = array())
    {
        $element = $this->getElement('CheckoutShipping');
        $language = \Helper::getCurrentLanguage($this);
        \Helper::clickNamedLink($element, 'changeButton', $language);

        $account = $this->getPage('Account');
        \Helper::fillForm($account, 'shippingForm', $data);
        \Helper::pressNamedButton($account, 'changeShippingButton', $language);
    }

    public function changeShippingMethod($data = array())
    {
        \Helper::fillForm($this, 'deliveryForm', $data);

        $locators = array('deliveryFormSubmit');
        $elements = \Helper::findElements($this, $locators);
        $elements['deliveryFormSubmit']->press();
    }
}
