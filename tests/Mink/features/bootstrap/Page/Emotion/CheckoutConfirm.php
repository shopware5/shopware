<?php
namespace  Shopware\Tests\Mink\Page\Emotion;

use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\ResponseTextException;
use Behat\Mink\WebAssert;
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
     * Verify if we're on an expected page. Throw an exception if not.
     */
    public function verifyPage()
    {
        if($this->getDriver() instanceof Selenium2Driver) {
            $this->getSession()->wait(5000, '$("#sAGB").length > 0');
        }

        $namedSelectors = $this->getNamedSelectors();
        $language = Helper::getCurrentLanguage();

        try {
            $assert = new WebAssert($this->getSession());
            $assert->pageTextContains($namedSelectors['gtc'][$language]);
        } catch (ResponseTextException $e) {
            $message = ['You are not on the checkout confirmation page!', 'Current URL: ' . $this->getSession()->getCurrentUrl()];
            Helper::throwException($message);
        }
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
     * @param array $data
     */
    public function changePaymentMethod(array $data = [])
    {
        $element = $this->getElement('CheckoutPayment');
        Helper::clickNamedLink($element, 'changeButton');

        $account = $this->getPage('Account');
        Helper::fillForm($account, 'paymentForm', $data);
        Helper::pressNamedButton($account, 'changePaymentButton');
    }

    /**
     * Changes the billing address
     * @param array $data
     */
    public function changeBillingAddress(array $data = [])
    {
        $element = $this->getElement('CheckoutBilling');
        Helper::clickNamedLink($element, 'changeButton');

        $account = $this->getPage('Account');
        Helper::fillForm($account, 'billingForm', $data);
        Helper::pressNamedButton($account, 'changeBillingButton');
    }

    /**
     * Changes the shipping address
     * @param array $data
     */
    public function changeShippingAddress(array $data = [])
    {
        $element = $this->getElement('CheckoutShipping');
        Helper::clickNamedLink($element, 'changeButton');

        $account = $this->getPage('Account');
        Helper::fillForm($account, 'shippingForm', $data);
        Helper::pressNamedButton($account, 'changeShippingButton');
    }

    /**
     * Changes the shipping method
     * @param array $data
     */
    public function changeShippingMethod(array $data = [])
    {
        Helper::fillForm($this, 'deliveryForm', $data);

        $elements = Helper::findElements($this, ['deliveryFormSubmit']);
        $elements['deliveryFormSubmit']->press();
    }

    /**
     * Checks the name of the current payment method
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
