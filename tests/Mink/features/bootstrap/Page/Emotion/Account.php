<?php
namespace Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Behat\Behat\Context\Step;
use Behat\Mink\Exception\ExpectationException;

class Account extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/account';

    public $cssLocator = array(
        'payment' => 'div#selected_payment strong',
        'logout' => 'div.adminbox a.logout'
    );

    /**
     * Logins a user
     * @param string $email
     * @param string $password
     */
    public function login($email, $password)
    {
        $this->open();

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

//    /**
//     * Verify if we're on an expected page. Throw an exception if not.
//     */
//    protected function verifyPage()
//    {
//        if ($this->logout())
//        {
//            $this->open();
//            return;
//        }
//
//        $assert = new \Behat\Mink\WebAssert($this->getSession());
//        $assert->pageTextContains('Sie besitzen bereits ein Kundenkonto');
//    }

    /**
     * Logout a customer (important by using the Sahi driver)
     * @return bool
     */
    public function logout()
    {
        $this->open();

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

    protected function getRegistrationForm()
    {
//        $this->pressButton('Neuer Kunde');
    }

    /**
     * Register a new user
     * @param array $values
     */
    public function register($values)
    {
        $this->getRegistrationForm();

        $billingValues = array();
        $shippingValues = array();

        foreach ($values as $row) {
            if (!empty($row['billing'])) {
                $billingValues[] = array(
                    'field' => $row['field'],
                    'value' => $row['billing']
                );
            }

            if (!empty($row['shipping'])) {
                $shippingValues[] = array(
                    'field' => $row['field'],
                    'value' => $row['shipping']
                );
            }
        }

        $this->fillBilling($billingValues);

        if (!empty($shippingValues)) {
            $this->checkField('register_billing_shippingAddress');
            $this->fillShipping($shippingValues);
        }

        $this->pressButton('Registrierung abschließen');
    }

    /**
     * Helper function to fill the billing address form
     * @param array $values
     */
    private function fillBilling($values)
    {
        $personal_fields = array(
            'customer_type',
            'salutation',
            'firstname',
            'lastname',
            'email',
            'password',
            'passwordConfirmation',
            'phone',
            'birthday',
            'birthmonth',
            'birthyear'
        );

        foreach ($values as $row) {
            if (in_array($row['field'], $personal_fields)) {
                $prefix = 'personal';
            } else {
                $prefix = 'billing';
            }

            switch ($row['field']) {
                case 'customer_type':
                case 'salutation':
                case 'birthday':
                case 'birthmonth':
                case 'birthyear':
                case 'country':
                    $this->selectFieldOption('register[' . $prefix . '][' . $row['field'] . ']', $row['value']);
                    break;

                default:
                    $this->fillField('register[' . $prefix . '][' . $row['field'] . ']', $row['value']);
                    break;
            }
        }
    }

    /**
     * Helper function to fill the shipping address form
     * @param array $values
     */
    private function fillShipping($values)
    {
        foreach ($values as $row) {
            switch ($row['field']) {
                case 'salutation':
                case 'country':
                    $this->selectFieldOption('register[shipping][' . $row['field'] . ']', $row['value']);
                    break;

                default:
                    $this->fillField('register[shipping][' . $row['field'] . ']', $row['value']);
                    break;
            }
        }
    }

    public function checkPayment($payment)
    {
        $locators = array('payment');
        $elements = \Helper::findElements($this, $locators);

        if(strcmp($elements['payment']->getText(), $payment) !== 0)
        {
            $message = sprintf('The current payment method is %s! (should be %s)', $elements['payment']->getText(), $payment);
            throw new ExpectationException($message, $this->getSession());
        }
    }

    /**
     * Changes the payment method
     * @param integer $value
     * @param array $data
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

    public function chooseAddress($type)
    {
        $this->open();

        $type = strtolower($type);
        $type = ucfirst($type);

        $this->getElement('Account'.$type)->clickButton('chooseOtherButton');
    }

    public function checkAddress($type, $address)
    {
        $this->open();

        $type = strtolower($type);
        $type = ucfirst($type);

        $this->getElement('Account'.$type)->checkAddress($address);
    }
}
