<?php
namespace  Shopware\Tests\Mink\Page\Emotion;

use Shopware\Tests\Mink\Element\Emotion\CheckoutPayment;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Helper;

class CheckoutConfirm extends Page implements \Shopware\Tests\Mink\HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = '/checkout/confirm';

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'deliveryForm' => 'form.payment',
            'deliveryFormSubmit' => 'form.payment input[type="submit"]',
            'proceedCheckoutForm' => 'div.additional_footer > form',
            'orderNumber' => 'div#finished > div.orderdetails > p'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [
            'gtc'  => ['de' => 'AGB und Widerrufsbelehrung', 'en' => 'Terms, conditions and cancellation policy'],
            'confirmButton'  => ['de' => 'Zahlungspflichtig bestellen', 'en' => 'Send order']
        ];
    }

    /**
     * @param string $language
     */
    public function verifyPage($language = '')
    {
        $namedSelectors = $this->getNamedSelectors();

        if (!$language) {
            $language = Helper::getCurrentLanguage($this);
        }

        $assert = new \Behat\Mink\WebAssert($this->getSession());
        $assert->pageTextContains($namedSelectors['gtc'][$language]);
    }

    /**
     * Returns the order number from finish page
     * @return int
     */
    public function getOrderNumber()
    {
        $elements = Helper::findElements($this, ['orderNumber']);

        $orderDetails = $elements['orderNumber']->getText();

        preg_match("/\d+/", $orderDetails, $orderNumber);
        $orderNumber = intval($orderNumber[0]);

        return $orderNumber;
    }

    /**
     * Proceeds the checkout
     */
    public function proceedToCheckout()
    {
        $this->checkField('sAGB');
        Helper::pressNamedButton($this, 'confirmButton');
    }

    /**
     * Changes the payment method
     * @param array   $data
     */
    public function changePaymentMethod($data = [])
    {
        $element = $this->getElement('CheckoutPayment');
        $language = Helper::getCurrentLanguage($this);
        Helper::clickNamedLink($element, 'changeButton', $language);

        $account = $this->getPage('Account');
        Helper::fillForm($account, 'paymentForm', $data);
        Helper::pressNamedButton($account, 'changePaymentButton', $language);
    }

    /**
     * @param array $data
     */
    public function changeBillingAddress($data = [])
    {
        $element = $this->getElement('CheckoutBilling');
        $language = Helper::getCurrentLanguage($this);
        Helper::clickNamedLink($element, 'changeButton', $language);

        $account = $this->getPage('Account');
        Helper::fillForm($account, 'billingForm', $data);
        Helper::pressNamedButton($account, 'changeBillingButton', $language);
    }

    /**
     * @param array $data
     */
    public function changeShippingAddress($data = [])
    {
        $element = $this->getElement('CheckoutShipping');
        $language = Helper::getCurrentLanguage($this);
        Helper::clickNamedLink($element, 'changeButton', $language);

        $account = $this->getPage('Account');
        Helper::fillForm($account, 'shippingForm', $data);
        Helper::pressNamedButton($account, 'changeShippingButton', $language);
    }

    /**
     * @param array $data
     */
    public function changeShippingMethod($data = [])
    {
        Helper::fillForm($this, 'deliveryForm', $data);

        $elements = Helper::findElements($this, ['deliveryFormSubmit']);
        $elements['deliveryFormSubmit']->press();
    }

    /**
     * @param string $paymentMethod
     * @throws \Exception
     */
    public function checkPaymentMethod($paymentMethod)
    {
        /** @var CheckoutPayment $element */
        $element = $this->getElement('CheckoutPayment');

        $properties = [
            'paymentMethod' => $paymentMethod
        ];

        $result = Helper::assertElementProperties($element, $properties);

        if ($result === true) {
            return;
        }

        $message = sprintf(
            'The current payment method is "%s" (should be "%s")',
            $result['value'],
            $result['value2']
        );

        Helper::throwException($message);
    }
}
