<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace  Shopware\Tests\Mink\Page;

use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\ResponseTextException;
use Behat\Mink\WebAssert;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Element\CheckoutPayment;
use Shopware\Tests\Mink\Helper;

class CheckoutConfirm extends Page implements \Shopware\Tests\Mink\HelperSelectorInterface
{
    /**
     * @var string
     */
    protected $path = '/checkout/confirm';

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [
            'shippingPaymentForm' => 'form.payment',
            'proceedCheckoutForm' => 'form#confirm--form',
            'orderNumber' => 'div.finish--details > div.panel--body',
            'addressForm' => 'form[name="frmAddresses"]',
            'company' => '.address--company',
            'address' => '.address--address',
            'salutation' => '.address--salutation',
            'customerTitle' => '.address--title',
            'firstname' => '.address--firstname',
            'lastname' => '.address--lastname',
            'street' => '.address--street',
            'addLineOne' => '.address--additional-one',
            'addLineTwo' => '.address--additional-two',
            'zipcode' => '.address--zipcode',
            'city' => '.address--city',
            'stateName' => '.address--statename',
            'countryName' => '.address--countryname',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getNamedSelectors()
    {
        return [
            'gtc' => ['de' => 'AGB und Widerrufsbelehrung', 'en' => 'Terms, conditions and cancellation policy'],
            'confirmButton' => ['de' => 'Zahlungspflichtig bestellen', 'en' => 'Send order'],
            'changePaymentButton' => ['de' => 'Weiter', 'en' => 'Next'],
            'saveAsNewAddressButton' => ['de' => 'Als neue Adresse speichern'],
        ];
    }

    /**
     * Verify if we're on an expected page. Throw an exception if not.
     */
    public function verifyPage()
    {
        if ($this->getDriver() instanceof Selenium2Driver) {
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
     *
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
     *
     * @param array $data
     */
    public function changePaymentMethod(array $data = [])
    {
        $data[0]['field'] = 'payment';
        $this->changeShippingMethod($data);
    }

    /**
     * Changes the billing address
     *
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
     *
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
     *
     * @param array $data
     */
    public function changeShippingMethod(array $data = [])
    {
        $element = $this->getElement('CheckoutPayment');
        Helper::clickNamedLink($element, 'changeButton');

        Helper::fillForm($this, 'shippingPaymentForm', $data);
        Helper::pressNamedButton($this, 'changePaymentButton');
    }

    /**
     * Checks the name of the current payment method
     *
     * @param string $paymentMethod
     *
     * @throws \Exception
     */
    public function checkPaymentMethod($paymentMethod)
    {
        /** @var CheckoutPayment $element */
        $element = $this->getElement('CheckoutPayment');

        $properties = [
            'paymentMethod' => $paymentMethod,
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

    /**
     * Creates a new address and saves it
     *
     * @param $values
     */
    public function createArbitraryAddress($values)
    {
        Helper::fillForm($this, 'addressForm', $values);
        $button = $this->find('css', '.address--form-actions > button');
        $button->press();
    }

    /**
     * Changes the values in a modal address form and saves the form
     *
     * @param $values
     */
    public function changeModalAddress($values)
    {
        Helper::fillForm($this, 'addressForm', $values);
        $button = $this->find('named', ['button', 'Adresse speichern']);
        $button->press();
    }
}
