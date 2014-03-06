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
     * @Given /^I log in as "([^"]*)" with password "([^"]*)"$/
     */
    public function iLogInAsWithPassword($email, $password)
    {
        $this->getPage('Account')->login($email, $password);
    }

    /**
     * @Given /^I log in successful as "([^"]*)" with password "([^"]*)"$/
     */
    public function iLogInSuccessfulAsWithPassword($email, $password)
    {
        $this->getPage('Account')->login($email, $password);
        $this->getPage('Account')->verifyLogin();
    }

    /**
     * @Then /^I change my email with password "([^"]*)" to "([^"]*)" with confirmation "([^"]*)"$/
     */
    public function iChangeMyEmailWithPasswordToWithConfirmation($password, $email, $emailConfirmation)
    {
        $this->getPage('Account')->changeEmail($password, $email, $emailConfirmation);
    }

    /**
     * @Then /^I change my password from "([^"]*)" to "([^"]*)" with confirmation "([^"]*)"$/
     */
    public function iChangeMyPasswordFromToWithConfirmation($currentPassword, $password, $passwordConfirmation)
    {
        $this->getPage('Account')->changePassword($currentPassword, $password, $passwordConfirmation);
    }

    /**
     * @Then /^I change my billing adress:$/
     */
    public function iChangeMyBillingAdress(TableNode $fieldValues)
    {
        $values = $fieldValues->getHash();

        $this->getPage('Account')->changeBilling($values);

    }

    /**
     * @Then /^I change my shipping adress:$/
     */
    public function iChangeMyShippingAdress(TableNode $fieldValues)
    {
        $values = $fieldValues->getHash();

        $this->getPage('Account')->changeShipping($values);
    }
}