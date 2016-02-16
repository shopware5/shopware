<?php

namespace Shopware\Tests\Mink;

use Behat\Gherkin\Node\TableNode;

class AccountContext extends SubContext
{
    /**
     * @Given /^I log in with email "(?P<email>[^"]*)" and password "(?P<password>[^"]*)"$/
     */
    public function iLogInAsWithPassword($email, $password)
    {
        $this->getPage('Account')->login($email, $password);
    }

    /**
     * @Given /^I log in successful as "(?P<username>[^"]*)" with email "(?P<email>[^"]*)" and password "(?P<password>[^"]*)"$/
     */
    public function iLogInSuccessfulAsWithPassword($username, $email, $password)
    {
        $this->getPage('Account')->login($email, $password);
        $this->getPage('Account')->verifyLogin($username);
    }

    /**
     * @When /^I log me out$/
     */
    public function iLogMeOut()
    {
        $this->getPage('Account')->logout();
    }

    /**
     * @Then /^I change my email with password "(?P<password>[^"]*)" to "(?P<new>[^"]*)"$/
     * @Then /^I change my email with password "(?P<password>[^"]*)" to "(?P<new>[^"]*)" with confirmation "(?P<confirmation>[^"]*)"$/
     */
    public function iChangeMyEmailWithPasswordToWithConfirmation($password, $email, $emailConfirmation = null)
    {
        $this->getPage('Account')->changeEmail($password, $email, $emailConfirmation);
    }

    /**
     * @Then /^I change my password from "(?P<old>[^"]*)" to "(?P<new>[^"]*)"$/
     * @Then /^I change my password from "(?P<old>[^"]*)" to "(?P<new>[^"]*)" with confirmation "(?P<confirmation>[^"]*)"$/
     */
    public function iChangeMyPasswordFromToWithConfirmation($currentPassword, $password, $passwordConfirmation = null)
    {
        $this->getPage('Account')->changePassword($currentPassword, $password, $passwordConfirmation);
    }

    /**
     * @Given /^I change my billing address:$/
     */
    public function iChangeMyBillingAddress(TableNode $table)
    {
        $pageInfo = Helper::getPageInfo($this->getSession(), ['controller']);
        $pageName = ucfirst($pageInfo['controller']);

        if ($pageName === 'Checkout') {
            $pageName = 'CheckoutConfirm';
        }

        /** @var \Shopware\Tests\Mink\Page\Emotion\Account|\Shopware\Tests\Mink\Page\Emotion\CheckoutConfirm $page */
        $page = $this->getPage($pageName);
        $data = $table->getHash();

        $page->changeBillingAddress($data);
    }

    /**
     * @Given /^I change my shipping address:$/
     */
    public function iChangeMyShippingAddress(TableNode $table)
    {
        $pageInfo = Helper::getPageInfo($this->getSession(), ['controller']);
        $pageName = ucfirst($pageInfo['controller']);

        if ($pageName === 'Checkout') {
            $pageName = 'CheckoutConfirm';
        }

        /** @var \Shopware\Tests\Mink\Page\Emotion\Account|\Shopware\Tests\Mink\Page\Emotion\CheckoutConfirm $page */
        $page = $this->getPage($pageName);
        $data = $table->getHash();

        $page->changeShippingAddress($data);
    }

    /**
     * @Given /^the "([^"]*)" address should be "([^"]*)"$/
     */
    public function theAddressShouldBe($type, $address)
    {
        $this->getPage('Account')->checkAddress($type, $address);
    }

    /**
     * @Given /^I register me:$/
     */
    public function iRegisterMe(\Behat\Gherkin\Node\TableNode $table)
    {
        $this->getPage('Account')->register($table->getHash());
    }

    /**
     * @When /^I change the payment method to (?P<paymentId>\d+)$/
     * @When /^I change the payment method to (?P<paymentId>\d+):$/
     */
    public function iChangeThePaymentMethodTo($payment, TableNode $table = null)
    {
        $pageInfo = Helper::getPageInfo($this->getSession(), ['controller', 'action']);
        $pageName = ucfirst($pageInfo['controller']);

        if ($pageName === 'Checkout') {
            $pageName = ($pageInfo['action'] === 'shippingpayment') ? 'CheckoutCart' : 'CheckoutConfirm';
        }

        /** @var \Shopware\Tests\Mink\Page\Emotion\Account|\Shopware\Tests\Mink\Page\Emotion\CheckoutConfirm $page */
        $page = $this->getPage($pageName);
        $data = [
            [
                'field' => 'register[payment]',
                'value' => $payment
            ]
        ];

        if ($table) {
            $data = array_merge($data, $table->getHash());
        }

        $page->changePaymentMethod($data);
    }

    /**
     * @Then /^the current payment method should be "([^"]*)"$/
     */
    public function theCurrentPaymentMethodShouldBe($paymentMethod)
    {
        $pageInfo = Helper::getPageInfo($this->getSession(), ['controller']);
        $pageName = (ucfirst($pageInfo['controller']) === 'Checkout') ? 'CheckoutConfirm' : 'Account';

        $this->getPage($pageName)->checkPaymentMethod($paymentMethod);
    }

    /**
     * @When /^I choose the address "([^"]*)"$/
     */
    public function iChooseTheAddress($name)
    {
        /** @var \Shopware\Tests\Mink\Page\Emotion\Account $page */
        $page = $this->getPage("Account");

        $addresses = $this->getMultipleElement($page, 'AddressBox');

        $page->chooseAddress($addresses, $name);
    }
}
