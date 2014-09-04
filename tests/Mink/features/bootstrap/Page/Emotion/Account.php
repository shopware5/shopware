<?php
namespace Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Behat\Mink\Exception\ExpectationException;

class Account extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/account';

    public $cssLocator = array(
        'identifiers' => array(
            'dashboard' => 'div#content > div > div.account',
            'login' => 'div#login',
            'register' => 'div#content > div > div.register'
        ),
        'payment' => 'div#selected_payment strong',
        'logout' => 'div.adminbox a.logout',
        'registrationForm' => 'div.register > form',
        'billingForm' => 'div.change_billing > form',
        'shippingForm' => 'div.change_shipping > form',
        'paymentForm' => 'div.change_payment > form'
    );

    /** @var array $namedSelectors */
    public $namedSelectors = array(
        'registerButton' => array('de' => 'Neuer Kunde',                'en' => 'New customer'),
        'sendButton'     => array('de' => 'Registrierung abschließen',  'en' => 'Complete registration')
    );

    /**
     * Logins a user
     * @param string $email
     * @param string $password
     */
    public function login($email, $password)
    {
        $this->fillField('email', $email);
        $this->fillField('password', $password);

        $this->pressButton('Anmelden');
    }

    /**
     * Check if the user was successfully logged in
     */
    public function verifyLogin($username)
    {
        $assert = new \Behat\Mink\WebAssert($this->getSession());
        $assert->pageTextContains(
            'Dies ist Ihr Konto Dashboard, wo Sie die Möglichkeit haben, Ihre letzten Kontoaktivitäten einzusehen'
        );
        $assert->pageTextContains('Willkommen, '.$username);
    }

    /**
     * Verify if we're on an expected page. Throw an exception if not.
     * @param string|null $action
     * @return string
     */
    public function verifyPage($action = null)
    {
        $locators = $this->cssLocator['identifiers'];
        $elements = \Helper::findElements($this, $locators, $locators, false, false);

        $elements = array_filter($elements);

        if (empty($elements)) {
            $message = array('You are not on Account page!', 'Current URL: ' . $this->getSession()->getCurrentUrl());
            \Helper::throwException($message);
        }

        if (!$action) {
            return true;
        }

        if (array_key_exists($action, $elements)) {
            return true;
        }

        return key($elements);
    }

    /**
     * Logout a customer (important by using the Sahi driver)
     * @return bool
     */
    public function logout()
    {
        $locators = array('logout');
        $elements = \Helper::findElements($this, $locators, $this->cssLocator, false, false);

        if ($elements['logout']) {
            $elements['logout']->click();

            return true;
        }

        return false;
    }

    /**
     * Changes the password of the user
     * @param string $currentPassword
     * @param string $password
     * @param string $passwordConfirmation
     */
    public function changePassword($currentPassword, $password, $passwordConfirmation)
    {
        $this->fillField('currentPassword', $currentPassword);
        $this->fillField('password', $password);
        $this->fillField('passwordConfirmation', $passwordConfirmation);

        $this->pressButton('Passwort ändern');
    }

    /**
     * Changes the email address of the user
     * @param string $password
     * @param string $email
     * @param string $emailConfirmation
     */
    public function changeEmail($password, $email, $emailConfirmation)
    {
        $this->fillField('emailPassword', $password);
        $this->fillField('email', $email);
        $this->fillField('emailConfirmation', $emailConfirmation);

        $this->pressButton('E-Mail ändern');
    }

    /**
     * Changes the billing address of the user
     * @param array $values
     */
    public function changeBilling($values)
    {
        $this->fillBilling($values);
        $this->pressButton('Ändern');
    }

    /**
     * Changes the shipping address of the user
     * @param array $values
     */
    public function changeShipping($values)
    {
        $this->fillShipping($values);
        $this->pressButton('Ändern');
    }

    public function checkPayment($payment)
    {
        $locators = array('payment');
        $elements = \Helper::findElements($this, $locators);

        if (strcmp($elements['payment']->getText(), $payment) !== 0) {
            $message = sprintf('The current payment method is %s! (should be %s)', $elements['payment']->getText(), $payment);
            throw new ExpectationException($message, $this->getSession());
        }
    }

    /**
     * Changes the payment method
     * @param integer $value
     * @param array   $data
     */
    public function changePayment($value, $data = array())
    {
        $field = $this->findField('register[payment]');

        if (null === $field) {
            $this->clickLink('Zahlungsart ändern');
            $this->selectFieldOption('register[payment]', $value);
        } else {
            $field->selectOption($value);
        }

        if ($value === 2) {
            foreach ($data as $field => $value) {
                $this->fillField($field, $value);
            }
        }

        $this->pressButton('Ändern');
    }

    public function checkOrder($orderNumber, $articles, $position = 1)
    {
        $this->open();

        $this->clickLink('Meine Bestellungen');

        $locator_prefix = sprintf(
            'div.orderoverview_active > div.table > div:nth-of-type(%d) > div.table',
            $position * 2 + 1
        );

        $locators = array();
        $check = array();
        $esd = array();

        //Check positions
        foreach ($articles as $key => $article) {
            $locators['name' . $key] = sprintf(
                '%s > div.table_row:nth-of-type(%d) .articleName',
                $locator_prefix,
                $key + 2
            );
            $locators['quantity' . $key] = sprintf(
                '%s > div.table_row:nth-of-type(%d) > div:nth-of-type(2)',
                $locator_prefix,
                $key + 2
            );
            $locators['price' . $key] = sprintf(
                '%s > div.table_row:nth-of-type(%d) > div:nth-of-type(3)',
                $locator_prefix,
                $key + 2
            );
            $locators['sum' . $key] = sprintf(
                '%s > div.table_row:nth-of-type(%d) > div:nth-of-type(4)',
                $locator_prefix,
                $key + 2
            );

            $check['name' . $key] = array('', $article['product']);
            $check['quantity' . $key] = array('', $article['quantity']);
            $check['price' . $key] = array('', $article['price']);
            $check['sum' . $key] = array('', $article['sum']);

            if (!empty($article['esd'])) {
                $esd[] = $article['product'];
            }
        }

        $locators['orderDate'] = $locator_prefix . ' > div.table_foot > div:nth-of-type(2) > p:nth-of-type(1)';
        $locators['orderNumber'] = $locator_prefix . ' > div.table_foot > div:nth-of-type(2) > p:nth-of-type(2)';

        $check['orderNumber'] = array('', $orderNumber);

        $elements = \Helper::findElements($this, null, $locators);

        foreach ($check as $key => &$checkStep) {
            $checkStep[0] = $elements[$key]->getText();

            if (strpos($key, 'name') === false) {
                $checkStep = \Helper::toFloat($checkStep);
            }
        }

        if (!empty($esd)) {
            $date = $elements['orderDate']->getText();

            $downloads = $this->getEsdArray($date);

            foreach ($downloads as $key => $download) {
                $check['esd' . $key] = array($download, $date .' '. $esd[$key]);
            }
        }

        $result = \Helper::checkArray($check);

        if ($result !== true) {
            $message = sprintf('There was a different value of the order! (%s: %s instead of %s)', $result, $check[$result][0], $check[$result][1]);
            throw new ExpectationException($message, $this->getSession());
        }
    }

    public function getEsdArray($date = null)
    {
        $this->open();

        $this->clickLink('Meine Sofortdownloads');

        $rows = $this->findAll('css', 'div.downloads div.table_row');

        $downloads = array();

        foreach ($rows as $row) {
            if (strpos($row->getText(), $date) !== false) {
                $downloads[] = $row->getText();
            }
        }

        return $downloads;
    }

    public function checkAddress($type, $address)
    {
        $this->open();

        $type = strtolower($type);
        $type = ucfirst($type);

        $this->getElement('Account'.$type)->checkAddress($address);
    }

    public function register($data)
    {
        if ($this->verifyPage('login') === true) {
            \Helper::pressNamedButton2($this, 'registerButton', null, 'de');
        }

        \Helper::fillForm($this, 'registrationForm', $data);
        \Helper::pressNamedButton2($this, 'sendButton', null, 'de');
    }
}
