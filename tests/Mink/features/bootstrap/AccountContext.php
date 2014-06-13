<?php

use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\TableNode;

require_once 'SubContext.php';

class AccountContext extends SubContext
{
    /**
     * @Given /^I am on my account page$/
     */
    public function iAmOnMyAccountPage()
    {
        $this->getPage('Account')->open();
    }

    /**
     * @Given /^I register me$/
     */
    public function iRegisterMe(TableNode $fieldValues)
    {
        $values = $fieldValues->getHash();

        $this->getPage('Account')->register($values);
    }

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
     * @Then /^I change my email with password "(?P<password>[^"]*)" to "(?P<new>[^"]*)" with confirmation "(?P<confirmation>[^"]*)"$/
     */
    public function iChangeMyEmailWithPasswordToWithConfirmation($password, $email, $emailConfirmation)
    {
        $this->getPage('Account')->changeEmail($password, $email, $emailConfirmation);
    }

    /**
     * @Then /^I change my password from "(?P<old>[^"]*)" to "(?P<new>[^"]*)" with confirmation "(?P<confirmation>[^"]*)"$/
     */
    public function iChangeMyPasswordFromToWithConfirmation($currentPassword, $password, $passwordConfirmation)
    {
        $this->getPage('Account')->changePassword($currentPassword, $password, $passwordConfirmation);
    }

    /**
     * @When /^my current payment method is "(?P<payment>[^"]*)"$/
     * @Given /^my current payment method should be "(?P<payment>[^"]*)"$/
     */
    public function myCurrentPaymentMethodShouldBe($payment)
    {
        $this->getPage('Account')->checkPayment($payment);
    }

    /**
     * @When /^I change my payment method to (?P<method>\d+)$/
     */
    public function iChangeMyPaymentMethodTo($value)
    {
        $this->getPage('Account')->changePayment($value);
    }

    /**
     * @When /^I change my payment method to debit 2 using account of "(?P<name>[^"]*)" \(no\. "(?P<account>\d+)"\) of bank "(?P<bank>[^"]*)" \(code "(?P<code>\d+)"\)$/
     */
    public function iChangeMyPaymentMethodToDebitUsingAccountOfNoOfBankCode($name, $kto, $bank, $blz)
    {
        $data = array('kontonr' => $kto,
            'blz' => $blz,
            'bank' => $bank,
            'bank2' => $name);

        $this->getPage('Account')->changePayment(2, $data);
    }

    /**
     * @Then /^I change my billing address:$/
     */
    public function iChangeMyBillingAddress(TableNode $fieldValues)
    {
        $values = $fieldValues->getHash();

        $this->getPage('Account')->changeBilling($values);
    }

    /**
     * @Then /^I change my shipping address:$/
     */
    public function iChangeMyShippingAddress(TableNode $fieldValues)
    {
        $values = $fieldValues->getHash();

        $this->getPage('Account')->changeShipping($values);
    }

    /**
     * @When /^I want to choose an other "([^"]*)" address$/
     */
    public function iWantToChooseAnOtherAddress($type)
    {
        $this->getPage('Account')->chooseAddress($type);
    }

    /**
     * @Given /^the "([^"]*)" address should be "([^"]*)"$/
     */
    public function theAddressShouldBe($type, $address)
    {
        $this->getPage('Account')->checkAddress($type, $address);
    }
}