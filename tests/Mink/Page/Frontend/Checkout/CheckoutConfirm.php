<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Mink\Page\Frontend\Checkout;

use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\ResponseTextException;
use Behat\Mink\WebAssert;
use Exception;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Page\Frontend\Account\Account;
use Shopware\Tests\Mink\Page\Frontend\Checkout\Elements\CheckoutBilling;
use Shopware\Tests\Mink\Page\Frontend\Checkout\Elements\CheckoutPayment;
use Shopware\Tests\Mink\Page\Frontend\Checkout\Elements\CheckoutShipping;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;
use Shopware\Tests\Mink\Tests\General\Helpers\HelperSelectorInterface;

class CheckoutConfirm extends Page implements HelperSelectorInterface
{
    /**
     * @var string
     */
    protected $path = '/checkout/confirm';

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors(): array
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
    public function getNamedSelectors(): array
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
    public function verifyPage(): void
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
     */
    public function getOrderNumber(): string
    {
        $elements = Helper::findElements($this, ['orderNumber']);

        $orderDetails = $elements['orderNumber']->getText();

        preg_match("/\d+/", $orderDetails, $orderNumber);

        return (string) ($orderNumber[0] ?? '');
    }

    /**
     * Proceeds the checkout
     */
    public function proceedToCheckout(): void
    {
        $this->checkField('sAGB');
        Helper::pressNamedButton($this, 'confirmButton');
    }

    /**
     * Changes the payment method
     */
    public function changePaymentMethod(array $data = []): void
    {
        $data[0]['field'] = 'payment';
        $this->changeShippingMethod($data);
    }

    /**
     * Changes the billing address
     */
    public function changeBillingAddress(array $data = []): void
    {
        $element = $this->getElement(CheckoutBilling::class);
        Helper::clickNamedLink($element, 'changeButton');

        $account = $this->getPage(Account::class);
        Helper::fillForm($account, 'billingForm', $data);
        Helper::pressNamedButton($account, 'changeBillingButton');
    }

    /**
     * Changes the shipping address
     */
    public function changeShippingAddress(array $data = []): void
    {
        $element = $this->getElement(CheckoutShipping::class);
        $url = $element->find('css', 'a[title="Adresse ändern"]')->getAttribute('href');

        $this->getSession()->visit($url);

        $account = $this->getPage(Account::class);
        Helper::fillForm($account, 'shippingForm', $data);
        Helper::pressNamedButton($account, 'changeShippingButton');
    }

    /**
     * Changes the shipping method
     */
    public function changeShippingMethod(array $data = []): void
    {
        $element = $this->getElement(CheckoutPayment::class);
        Helper::clickNamedLink($element, 'changeButton');

        Helper::fillForm($this, 'shippingPaymentForm', $data, true);

        Helper::waitForOverlay($this);

        Helper::pressNamedButton($this, 'changePaymentButton');
    }

    /**
     * Checks the name of the current payment method
     *
     * @throws Exception
     */
    public function checkPaymentMethod(string $paymentMethod): void
    {
        $element = $this->getElement(CheckoutPayment::class);

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
     */
    public function createArbitraryAddress(array $values): void
    {
        Helper::fillForm($this, 'addressForm', $values);
        $button = $this->find('css', '.address--form-actions > button');
        $button->press();
    }

    /**
     * Changes the values in a modal address form and saves the form
     */
    public function changeModalAddress(array $values): void
    {
        Helper::fillForm($this, 'addressForm', $values);
        $button = $this->find('named', ['button', 'Adresse speichern']);
        $button->press();
    }

    public function checkoutUsingGet(string $path): void
    {
        $this->getDriver()->visit($path);
    }
}
