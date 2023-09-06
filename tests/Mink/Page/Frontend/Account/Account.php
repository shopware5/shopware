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

namespace Shopware\Tests\Mink\Page\Frontend\Account;

use Behat\Mink\Exception\ResponseTextException;
use Behat\Mink\WebAssert;
use Exception;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Page\Frontend\Account\Elements\AccountBilling;
use Shopware\Tests\Mink\Page\Frontend\Account\Elements\AccountOrder;
use Shopware\Tests\Mink\Page\Frontend\Account\Elements\AccountPayment;
use Shopware\Tests\Mink\Page\Frontend\Account\Elements\AccountShipping;
use Shopware\Tests\Mink\Page\Frontend\Address\Elements\AddressBox;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;
use Shopware\Tests\Mink\Tests\General\Helpers\HelperSelectorInterface;

class Account extends Page implements HelperSelectorInterface
{
    /**
     * @var string
     */
    protected $path = '/account';

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors(): array
    {
        return [
            'payment' => 'div.account--payment.account--box strong',
            'logout' => 'div.account--menu-container a.link--logout',
            'registrationForm' => 'form.register--form',
            'billingForm' => 'div.account--address-form form',
            'shippingForm' => 'div.account--address-form form',
            'paymentForm' => 'div.account--payment-form > form',
            'passwordForm' => 'div.profile-password--container > form',
            'emailForm' => 'div.profile-email--container > form',
            'profileForm' => 'div.account--profile > form',
            'changePasswordButton' => 'div.profile-password--container button',
            'changeEmailButton' => 'div.profile-email--container button',
            'changeProfileButton' => 'div.account--profile > form button',
            'esdDownloads' => '.downloads--table-header ~ .panel--tr',
            'esdDownloadName' => '.download--name',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getNamedSelectors(): array
    {
        return [
            'loginButton' => ['de' => 'Anmelden', 'en' => 'Login'],
            'forgotPasswordLink' => ['de' => 'Passwort vergessen?', 'en' => 'Forgot your password?'],
            'sendButton' => ['de' => 'Weiter', 'en' => 'Continue'],

            'myAccountLink' => ['de' => 'Übersicht', 'en' => 'Overview'],
            'profileLink' => ['de' => 'Persönliche Daten', 'en' => 'Profile'],
            'addressesLink' => ['de' => 'Adressen', 'en' => 'Addresses'],
            'myOrdersLink' => ['de' => 'Bestellungen', 'en' => 'orders'],
            'myEsdDownloadsLink' => ['de' => 'Sofortdownloads', 'en' => 'Instant downloads'],
            'changePaymentLink' => ['de' => 'Zahlungsarten', 'en' => 'Payment methods'],
            'noteLink' => ['de' => 'Merkzettel', 'en' => 'Wish list'],
            'logoutLink' => ['de' => 'Abmelden', 'en' => 'Logout'],

            'changePaymentButton' => ['de' => 'Ändern', 'en' => 'Change'],
            'changeBillingButton' => ['de' => 'Adresse speichern', 'en' => 'Change address'],
            'changeShippingButton' => ['de' => 'Adresse speichern', 'en' => 'Change address'],
            'saveAddressButton' => ['de' => 'Adresse speichern', 'en' => 'Save address'],
            'loginAgain' => ['de' => 'Erneut Anmelden', 'en' => 'Login again'],
        ];
    }

    /**
     * Verify if we're on an expected page. Throw an exception if not.
     *
     * @throws Exception
     */
    public function verifyPage(string $action = ''): bool
    {
        if ($action === 'Dashboard' || empty($action)) {
            if ($this->verifyPageDashboard()) {
                return true;
            }
        }

        if ($action === 'Login' || empty($action)) {
            if ($this->verifyPageLogin()) {
                return true;
            }
        }

        if ($action === 'Register' || empty($action)) {
            if ($this->verifyPageRegister()) {
                return true;
            }
        }

        if ($action) {
            return false;
        }

        $message = ['You are not on Account page! Action:' . $action, 'Current URL: ' . $this->getSession()->getCurrentUrl()];
        Helper::throwException($message);
    }

    /**
     * Logins a user
     */
    public function login(string $email, string $password): void
    {
        $this->fillField('email', $email);
        $this->fillField('password', $password);

        Helper::pressNamedButton($this, 'loginButton');
    }

    public function clickLoginAgain(): void
    {
        Helper::clickNamedLink($this, 'loginAgain');
    }

    /**
     * Check if the user was successfully logged in
     *
     * @throws ResponseTextException
     */
    public function verifyLogin(string $username): void
    {
        $assert = new WebAssert($this->getSession());
        $assert->pageTextContains(
            'Dies ist Ihr Konto Dashboard, wo Sie die Möglichkeit haben, Ihre letzten Kontoaktivitäten einzusehen'
        );
        $assert->pageTextContains('Willkommen, ' . $username);
    }

    /**
     * Logout a customer (important when using the Selenium driver)
     */
    public function logout(): void
    {
        Helper::clickNamedLink($this, 'logoutLink');
    }

    /**
     * Changes the password of the user
     */
    public function changePassword(string $currentPassword, string $password, ?string $passwordConfirmation = null): void
    {
        $data = [
            [
                'field' => 'password[currentPassword]',
                'value' => $currentPassword,
            ],
            [
                'field' => 'password[password]',
                'value' => $password,
            ],
            [
                'field' => 'password[passwordConfirmation]',
                'value' => $passwordConfirmation ?? $password,
            ],
        ];

        Helper::fillForm($this, 'passwordForm', $data);
        $this->find('css', $this->getCssSelectors()['changePasswordButton'])->press();
    }

    /**
     * Changes the email address of the user
     */
    public function changeEmail(string $password, string $email, ?string $emailConfirmation = null): void
    {
        $data = [
            [
                'field' => 'email[currentPassword]',
                'value' => $password,
            ],
            [
                'field' => 'email[email]',
                'value' => $email,
            ],
            [
                'field' => 'email[emailConfirmation]',
                'value' => $emailConfirmation ?? $email,
            ],
        ];

        Helper::fillForm($this, 'emailForm', $data);
        $this->find('css', $this->getCssSelectors()['changeEmailButton'])->press();
    }

    /**
     * Changes the billing address of the user
     *
     * @param array $values
     */
    public function changeBillingAddress($values)
    {
        Helper::fillForm($this, 'addressForm', $values);
        Helper::pressNamedButton($this, 'saveAddressButton');
    }

    /**
     * Changes the shipping address of the user
     *
     * @param array $values
     */
    public function changeShippingAddress($values)
    {
        Helper::fillForm($this, 'addressForm', $values);
        Helper::pressNamedButton($this, 'saveAddressButton');
    }

    /**
     * Creates a new address used neither as billing nor as shipping address
     *
     * @param array $values
     */
    public function createArbitraryAddress($values)
    {
        Helper::fillForm($this, 'addressForm', $values);
        Helper::pressNamedButton($this, 'saveAddressButton');
    }

    /**
     * Changes the payment method
     *
     * @param array $data
     */
    public function changePaymentMethod($data = [])
    {
        $element = $this->getElement(AccountPayment::class);
        Helper::clickNamedLink($element, 'changeButton');

        Helper::fillForm($this, 'paymentForm', $data);
        Helper::pressNamedButton($this, 'changePaymentButton');
    }

    /**
     * Checks the name of the payment method
     *
     * @param string $paymentMethod
     *
     * @throws Exception
     */
    public function checkPaymentMethod($paymentMethod)
    {
        $element = $this->getElement(AccountPayment::class);

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

    public function checkOrder(AccountOrder $order, string $orderNumber, array $articles): void
    {
        $this->getSession()->executeScript('$(\'[data-collapse-panel]\').click()');
        sleep(1);
        $date = $order->getDateProperty();
        $this->checkOrderNumber($order, $orderNumber);
        $this->checkOrderPositions($order, $articles);
        $this->checkEsdArticles($date, $articles);
    }

    /**
     * Checks the billing or shipping address
     */
    public function checkAddress(string $type, string $address): void
    {
        $this->open();

        $address = str_replace('<ignore>', '', $address);
        $testAddress = explode(', ', $address);
        $testAddress = array_filter($testAddress);
        $testAddress = array_values($testAddress);

        $type = strtolower($type);
        $type = ucfirst($type);

        /** @var class-string<AccountBilling|AccountShipping> $elementClass */
        $elementClass = 'Account' . $type;
        $addressBox = $this->getElement($elementClass);
        $addressData = Helper::getElementProperty($addressBox, 'address');

        $givenAddress = [];

        foreach ($addressData as $data) {
            $part = $data->getHtml();
            $parts = explode('<br />', $part);
            foreach ($parts as &$part) {
                $part = strip_tags($part);
                $part = str_replace([\chr(0x0009), '  '], ' ', $part);
                $part = str_replace([\chr(0x0009), '  '], ' ', $part);
                $part = trim($part);
            }
            unset($part);

            $givenAddress = array_merge($givenAddress, $parts);
        }

        $result = Helper::compareArrays($givenAddress, $testAddress);

        if ($result === true) {
            return;
        }

        $message = sprintf(
            'The addresses are different! ("%s" not was found in "%s")',
            $result['value2'],
            $result['value']
        );
        Helper::throwException($message);
    }

    /**
     * Fills the fields of the registration form and submits it
     */
    public function register(array $data)
    {
        $this->verifyPage();

        Helper::fillForm($this, 'registrationForm', $data);
        Helper::pressNamedButton($this, 'sendButton');
    }

    public function chooseAddress(AddressBox $addresses, string $name): void
    {
        $this->searchAddress($addresses, $name);
    }

    public function changeProfile(string $salutation, string $firstname, string $lastname): void
    {
        $data = [
            [
                'field' => 'profile[salutation]',
                'value' => $salutation,
            ],
            [
                'field' => 'profile[firstname]',
                'value' => $firstname,
            ],
            [
                'field' => 'profile[lastname]',
                'value' => $lastname,
            ],
        ];

        Helper::fillForm($this, 'profileForm', $data);
        $this->find('css', $this->getCssSelectors()['changeProfileButton'])->press();
    }

    /**
     * Helper function to check weather we are on the account dashboard
     *
     * @return bool
     */
    protected function verifyPageDashboard()
    {
        return (Helper::hasNamedLinks($this, [
                'myAccountLink',
                'profileLink',
                'addressesLink',
                'myOrdersLink',
                'myEsdDownloadsLink',
                'changePaymentLink',
                'noteLink',
                'logoutLink',
            ]) === true) ?: false;
    }

    /**
     * Helper function to check weather we are on the login page
     *
     * @return bool
     */
    protected function verifyPageLogin()
    {
        return $this->hasField('email')
            && $this->hasField('password')
            && Helper::hasNamedLink($this, 'forgotPasswordLink')
            && Helper::hasNamedButton($this, 'loginButton')
            && $this->verifyPageRegister()
        ;
    }

    /**
     * Helper function to check weather we are on the register page
     *
     * @return bool
     */
    protected function verifyPageRegister()
    {
        return $this->hasSelect('register[personal][customer_type]')
            && $this->hasSelect('register[personal][salutation]')
            && $this->hasField('register[personal][firstname]')
            && $this->hasField('register[personal][lastname]')
            && $this->hasField('register[personal][email]')
            && $this->hasField('register[personal][password]')

            && $this->hasField('register[billing][company]')
            && $this->hasField('register[billing][department]')
            && $this->hasField('register[billing][vatId]')

            && $this->hasField('register[billing][street]')
            && $this->hasField('register[billing][zipcode]')
            && $this->hasField('register[billing][city]')
            && $this->hasSelect('register[billing][country]')
            && $this->hasField('register[billing][shippingAddress]')

            && $this->hasSelect('register[shipping][salutation]')
            && $this->hasField('register[shipping][company]')
            && $this->hasField('register[shipping][department]')
            && $this->hasField('register[shipping][firstname]')
            && $this->hasField('register[shipping][lastname]')
            && $this->hasField('register[shipping][street]')
            && $this->hasField('register[shipping][zipcode]')
            && $this->hasField('register[shipping][city]')
            && $this->hasSelect('register[shipping][country]')

            && Helper::hasNamedButton($this, 'sendButton')
        ;
    }

    /**
     * @throws Exception
     */
    protected function searchAddress(AddressBox $addresses, string $name): void
    {
        foreach ($addresses as $address) {
            if (!str_contains($address->getProperty('title'), $name)) {
                continue;
            }

            Helper::pressNamedButton($address, 'chooseButton');

            return;
        }

        $messages = ['The address "' . $name . '" is not available. Available are:'];

        foreach ($addresses as $address) {
            $messages[] = $address->getProperty('title');
        }

        Helper::throwException($messages);
    }

    /**
     * Helper method checks the order number
     */
    private function checkOrderNumber(AccountOrder $order, string $orderNumber): void
    {
        $properties = [
            'number' => $orderNumber,
        ];

        $result = Helper::assertElementProperties($order, $properties);

        if ($result === true) {
            return;
        }

        $message = sprintf(
            'The order number is "%s" (should be "%s")',
            $result['value'],
            $result['value2']
        );

        Helper::throwException($message);
    }

    /**
     * Helper method checks the order positions
     *
     * @throws Exception
     */
    private function checkOrderPositions(AccountOrder $order, array $articles)
    {
        $positions = $order->getPositions(['product', 'quantity', 'price', 'sum']);

        $data = [];

        foreach ($articles as $key => $article) {
            $data[$key] = Helper::floatArray([
                'quantity' => $article['quantity'],
                'price' => $article['price'],
                'sum' => $article['sum'],
            ]);

            $data[$key]['product'] = $article['product'];
        }

        $result = Helper::compareArrays($positions, $data);

        if ($result === true) {
            return;
        }

        $message = sprintf(
            'The %s of a position is different! (is "%s", should be "%s")',
            $result['key'],
            $result['value'],
            $result['value2']
        );
        Helper::throwException($message);
    }

    /**
     * Helper method checks the ESD articles
     *
     * @throws Exception
     */
    private function checkEsdArticles(string $date, array $articles)
    {
        $esd = [];

        foreach ($articles as $article) {
            if (empty($article['esd'])) {
                continue;
            }

            $esd[] = $article['product'];
        }

        if (empty($esd)) {
            return;
        }

        Helper::clickNamedLink($this, 'myEsdDownloadsLink');

        $elements = Helper::findAllOfElements($this, ['esdDownloads']);
        $locator = Helper::getRequiredSelector($this, 'esdDownloadName');
        $downloads = [];

        foreach ($elements['esdDownloads'] as $esdDownload) {
            if (str_contains($esdDownload->getText(), $date)) {
                $downloads[] = $this->find('css', $locator)->getText();
            }
        }

        foreach ($esd as $givenEsd) {
            foreach ($downloads as $download) {
                if ($givenEsd === $download) {
                    break;
                }

                if ($download === end($downloads)) {
                    $message = sprintf('ESD-Article "%s" not found in account!', $givenEsd);
                    Helper::throwException($message);
                }
            }
        }
    }
}
