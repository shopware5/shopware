<?php
namespace Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

class CheckoutConfirm extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/checkout/confirm';

    public $cssLocator = array(
        'pageIdentifier'  => 'div#confirm',
        'deliveryForm' => 'form.payment',
        'deliveryFormSubmit' => 'form.payment input[type="submit"]',
        'proceedCheckoutForm' => 'div.additional_footer > form',
        'orderNumber' => 'div#finished > div.orderdetails > p'
    );

    /** @var array $namedSelectors */
    public $namedSelectors = array(
        'confirmButton'  => array('de' => 'Zahlungspflichtig bestellen',            'en' => 'Send order')
    );

    public function verifyPage()
    {
        $locators = array('pageIdentifier');
        $elements = \Helper::findElements($this, $locators, $this->cssLocator, false, false);

        if (!empty($elements['pageIdentifier'])) {
            return;
        }

        $message = array('You are not on CheckoutConfirm page!', 'Current URL: '.$this->getSession()->getCurrentUrl());
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
    public function changePayment($data = array())
    {
        $element = $this->getElement('CheckoutPayment');
        $language = \Helper::getCurrentLanguage($this);
        \Helper::clickNamedLink($element, 'changeButton', null, $language);

        $account = $this->getPage('Account');
        \Helper::fillForm($account, 'paymentForm', $data);
        \Helper::pressNamedButton($account, 'changePaymentButton');
    }

    /**
     * @param array $data
     */
    public function changeShipping($data = array())
    {
        \Helper::fillForm($this, 'deliveryForm', $data);

        $locators = array('deliveryFormSubmit');
        $elements = \Helper::findElements($this, $locators);
        $elements['deliveryFormSubmit']->press();
    }
}
