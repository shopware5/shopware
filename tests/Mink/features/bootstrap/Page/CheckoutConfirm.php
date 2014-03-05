<?php

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Behat\Behat\Context\Step;


class CheckoutConfirm extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/checkout/confirm';

    public function login($email, $password)
    {
        $this->fillField('email', $email);
        $this->fillField('password', $password);

        $this->pressButton('Anmelden');
    }

    public function changeBilling($values)
    {
        $this->open();

        $button = $this->find('css', 'div.invoice-address a.button-middle:nth-of-type(1)');
        $button->click();

        $this->getPage('Account')->changeBilling($values);
    }

    public function changeShipping($values)
    {
        $this->open();

        $button = $this->find('css', 'div.shipping-address a.button-middle:nth-of-type(1)');
        $button->click();

        $this->getPage('Account')->changeShipping($values);
    }

    public function changePayment($value, $data=array())
    {
        $this->open();

        $button = $this->find('css', 'div.payment-display a.button-middle');
        $button->click();

        $this->selectFieldOption('register[payment]', $value);

        if ($value === 2) {
            foreach ($data as $field => $value) {
                $this->fillField($field, $value);
            }
        }

        $this->pressButton('Ändern');
    }

    public function changeDelivery($value)
    {
        $this->open();

        $this->selectFieldOption('sDispatch', $value);

        $button = $this->find('css', 'div.dispatch-methods input.button-middle');
        $button->press();
    }


}
