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

namespace Shopware\Tests\Mink\Page;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\WebAssert;
use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Element\AccountOrder;
use Shopware\Tests\Mink\Element\AccountPayment;
use Shopware\Tests\Mink\Element\AddressBox;
use Shopware\Tests\Mink\Helper;
use Shopware\Tests\Mink\HelperSelectorInterface;

class Account extends Page implements HelperSelectorInterface
{
    /**
     * @var string
     */
    protected $path = '/account';

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
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
    public function getNamedSelectors()
    {
        return [
            'loginButton' => ['de' => 'Anmelden',                 'en' => 'Login'],
            'forgotPasswordLink' => ['de' => 'Passwort vergessen?',      'en' => 'Forgot your password?'],
            'sendButton' => ['de' => 'Weiter',                   'en' => 'Continue'],

            'myAccountLink' => ['de' => 'Übersicht',                'en' => 'Overview'],
            'profileLink' => ['de' => 'Persönliche Daten',        'en' => 'Profile'],
            'addressesLink' => ['de' => 'Adressen',                 'en' => 'Addresses'],
            'myOrdersLink' => ['de' => 'Bestellungen',             'en' => 'orders'],
            'myEsdDownloadsLink' => ['de' => 'Sofortdownloads',          'en' => 'Instant downloads'],
            'changePaymentLink' => ['de' => 'Zahlungsarten',            'en' => 'Payment methods'],
            'noteLink' => ['de' => 'Merkzettel',               'en' => 'Wish list'],
            'logoutLink' => ['de' => 'Abmelden',                 'en' => 'Logout'],

            'changePaymentButton' => ['de' => 'Ändern',                   'en' => 'Change'],
            'changeBillingButton' => ['de' => 'Adresse speichern',        'en' => 'Change address'],
            'changeShippingButton' => ['de' => 'Adresse speichern',        'en' => 'Change address'],
            'saveAddressButton' => ['de' => 'Adresse speichern',        'en' => 'Save address'],
            'loginAgain' => ['de' => 'Erneut Anmelden', 'en' => 'Login again'],
        ];
    }

    /**
     * Verify if we're on an expected page. Throw an exception if not.
     *
     * @param string $action
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function verifyPage($action = '')
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
     *
     * @param string $email
     * @param string $password
     */
    public function login($email, $password)
    {
        $this->fillField('email', $email);
        $this->fillField('password', $password);

        Helper::pressNamedButton($this, 'loginButton');
    }

    public function clickLoginAgain()
    {
        Helper::clickNamedLink($this, 'loginAgain');
    }

    /**
     * Check if the user was successfully logged in
     *
     * @param string $username
     *
     * @throws \Behat\Mink\Exception\ResponseTextException
     */
    public function verifyLogin($username)
    {
        $assert = new WebAssert($this->getSession());
        $assert->pageTextContains(
            'Dies ist Ihr Konto Dashboard, wo Sie die Möglichkeit haben, Ihre letzten Kontoaktivitäten einzusehen'
        );
        $assert->pageTextContains('Willkommen, ' . $username);
    }

    /**
     * Logout a customer (important when using the Selenium driver)
     *
     * @return bool
     */
    public function logout()
    {
        Helper::clickNamedLink($this, 'logoutLink');
    }

    /**
     * Changes the password of the user
     *
     * @param string $currentPassword
     * @param string $password
     * @param string $passwordConfirmation
     */
    public function changePassword($currentPassword, $password, $passwordConfirmation = null)
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
                'value' => ($passwordConfirmation !== null) ? $passwordConfirmation : $password,
            ],
        ];

        Helper::fillForm($this, 'passwordForm', $data);
        $this->find('css', $this->getCssSelectors()['changePasswordButton'])->press();
    }

    /**
     * Changes the email address of the user
     *
     * @param string $password
     * @param string $email
     * @param string $emailConfirmation
     */
    public function changeEmail($password, $email, $emailConfirmation = null)
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
                'value' => ($emailConfirmation !== null) ? $emailConfirmation : $email,
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
        $element = $this->getElement('AccountPayment');
        Helper::clickNamedLink($element, 'changeButton');

        Helper::fillForm($this, 'paymentForm', $data);
        Helper::pressNamedButton($this, 'changePaymentButton');
    }

    /**
     * Checks the name of the payment method
     *
     * @param string $paymentMethod
     *
     * @throws \Exception
     */
    public function checkPaymentMethod($paymentMethod)
    {
        /** @var AccountPayment $element */
        $element = $this->getElement('AccountPayment');

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
     * @param string $orderNumber
     */
    public function checkOrder(AccountOrder $order, $orderNumber, array $articles)
    {
        $date = $order->getDateProperty();
        $this->checkOrderNumber($order, $orderNumber);
        $this->checkOrderPositions($order, $articles);
        $this->checkEsdArticles($date, $articles);
    }

    /**
     * Checks the billing or shipping address
     *
     * @param string $type
     * @param string $address
     */
    public function checkAddress($type, $address)
    {
        $this->open();

        $testAddress = explode(', ', $address);
        $testAddress = array_filter($testAddress);
        $testAddress = array_values($testAddress);

        $type = strtolower($type);
        $type = ucfirst($type);

        $addressBox = $this->getElement('Account' . $type);
        $addressData = Helper::getElementProperty($addressBox, 'address');

        $givenAddress = [];

        /** @var Element $data */
        foreach ($addressData as $data) {
            $part = $data->getHtml();
            $parts = explode('<br />', $part);
            foreach ($parts as &$part) {
                $part = strip_tags($part);
                $part = str_replace([chr(0x0009), '  '], ' ', $part);
                $part = str_replace([chr(0x0009), '  '], ' ', $part);
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

    /**
     * @param string $name
     */
    public function chooseAddress(AddressBox $addresses, $name)
    {
        $this->searchAddress($addresses, $name);
    }

    /**
     * @param string $salutation
     * @param string $firstname
     * @param string $lastname
     */
    public function changeProfile($salutation, $firstname, $lastname)
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
     * @param string $name
     *
     * @throws \Exception
     */
    protected function searchAddress(AddressBox $addresses, $name)
    {
        /** @var AddressBox $address */
        foreach ($addresses as $address) {
            if (strpos($address->getProperty('title'), $name) === false) {
                continue;
            }

            Helper::pressNamedButton($address, 'chooseButton');

            return;
        }

        $messages = ['The address "' . $name . '" is not available. Available are:'];

        /** @var AddressBox $address */
        foreach ($addresses as $address) {
            $messages[] = $address->getProperty('title');
        }

        Helper::throwException($messages);
    }

    /**
     * Helper method checks the order number
     *
     * @param string $orderNumber
     */
    private function checkOrderNumber(AccountOrder $order, $orderNumber)
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
     * @throws \Exception
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
     * @param string $date
     *
     * @throws \Exception
     */
    private function checkEsdArticles($date, array $articles)
    {
        $esd = [];

        foreach ($articles as $key => $article) {
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

        /** @var NodeElement $esdDownload */
        foreach ($elements['esdDownloads'] as $esdDownload) {
            if (strpos($esdDownload->getText(), $date) !== false) {
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
