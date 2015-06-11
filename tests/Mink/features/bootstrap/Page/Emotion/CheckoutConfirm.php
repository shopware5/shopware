<?php
namespace Page\Emotion;

use Element\Emotion\CheckoutPayment;
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
            'gtc'  => array('de' => 'AGB und Widerrufsbelehrung', 'en' => 'Terms, conditions and cancellation policy'),
            'confirmButton'  => array('de' => 'Zahlungspflichtig bestellen', 'en' => 'Send order')
        );
    }

    public function verifyPage($language = '')
    {
        $namedSelectors = $this->getNamedSelectors();

        if(!$language) {
            $language = \Helper::getCurrentLanguage($this);
        }

        $assert = new \Behat\Mink\WebAssert($this->getSession());
        $assert->pageTextContains($namedSelectors['gtc'][$language]);
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
    public function changeBillingAddress($data = array())
    {
        $element = $this->getElement('CheckoutBilling');
        $language = \Helper::getCurrentLanguage($this);
        \Helper::clickNamedLink($element, 'changeButton', $language);

        $account = $this->getPage('Account');
        \Helper::fillForm($account, 'billingForm', $data);
        \Helper::pressNamedButton($account, 'changeBillingButton', $language);
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

    /**
     * @param array $data
     */
    public function changeShippingMethod($data = array())
    {
        \Helper::fillForm($this, 'deliveryForm', $data);

        $locators = array('deliveryFormSubmit');
        $elements = \Helper::findElements($this, $locators);
        $elements['deliveryFormSubmit']->press();
    }

    /**
     * @param string $paymentMethod
     * @throws \Behat\Behat\Exception\PendingException
     * @throws \Exception
     */
    public function checkPaymentMethod($paymentMethod)
    {
        /** @var CheckoutPayment $element */
        $element = $this->getElement('CheckoutPayment');

        $properties = array(
            'paymentMethod' => $paymentMethod
        );

        $result = \Helper::assertElementProperties($element, $properties);

        if($result === true) {
            return;
        }

        $message = sprintf(
            'The current payment method is "%s" (should be "%s")',
            $result['value'],
            $result['value2']
        );

        \Helper::throwException($message);
    }
}
