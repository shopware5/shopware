<?php

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Behat\Behat\Context\Step;

class Account extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/account';

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
    public function verifyLogin()
    {
        $assert = new \Behat\Mink\WebAssert($this->getSession());
        $assert->pageTextContains(
                'Dies ist Ihr Konto Dashboard, wo Sie die Möglichkeit haben, Ihre letzten Kontoaktivitäten einzusehen'
        );
    }

    /**
     * Verify if we're on an expected page. Throw an exception if not.
     */
    protected function verifyPage()
    {
        $assert = new \Behat\Mink\WebAssert($this->getSession());
        $assert->pageTextContains('Sie besitzen bereits ein Kundenkonto');
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

    /**
     * Register a new user
     * @param array $values
     */
    public function register($values)
    {
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
}
