<?php

use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Behat\Behat\Context\Step;

class Account extends Page
{
    /**
     * @var string $path
     */
    protected $path = '/account';

    public function login($email, $password)
    {
        $this->open();

        $this->fillField('email', $email);
        $this->fillField('password', $password);

        $this->pressButton('Anmelden');
    }

    public function verifyLogin()
    {
        $assert = new \Behat\Mink\WebAssert($this->getSession());
        $assert->pageTextContains('Dies ist Ihr Konto Dashboard, wo Sie die Möglichkeit haben, Ihre letzten Kontoaktivitäten einzusehen');
    }

    /**
     * Verify if we're on an expected page. Throw an exception if not.
     */
    protected function verifyPage()
    {
        $assert = new \Behat\Mink\WebAssert($this->getSession());
        $assert->pageTextContains('Sie besitzen bereits ein Kundenkonto');
    }
}
