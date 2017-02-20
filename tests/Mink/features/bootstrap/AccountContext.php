<?php

namespace Shopware\Tests\Mink;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\WebAssert;
use Shopware\Tests\Mink\Element\AddressManagementAddressBox;

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

        /** @var \Shopware\Tests\Mink\Page\Account|\Shopware\Tests\Mink\Page\CheckoutConfirm $page */
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

        /** @var \Shopware\Tests\Mink\Page\Account|\Shopware\Tests\Mink\Page\CheckoutConfirm $page */
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

        /** @var \Shopware\Tests\Mink\Page\Account|\Shopware\Tests\Mink\Page\CheckoutConfirm $page */
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
        /** @var \Shopware\Tests\Mink\Page\Account $page */
        $page = $this->getPage("Account");

        $addresses = $this->getMultipleElement($page, 'AddressBox');

        $page->chooseAddress($addresses, $name);
    }

    /**
     * @Given /^I change my profile with "([^"]*)" "([^"]*)" "([^"]*)"$/
     */
    public function iChangeMyProfileWith($salutation, $firstname, $lastname)
    {
        $this->getPage('Account')->changeProfile($salutation, $firstname, $lastname);
    }

    /**
     * @Given /^I should be welcomed with "([^"]*)"$/
     */
    public function iShouldBeWelcomedWith($welcome)
    {
        $welcome = preg_replace("/\s\s+/", " ", $welcome);

        $assert = new WebAssert($this->getSession());
        $assert->pageTextContains($welcome);
    }

    private function endsWith($haystack, $needle)
    {
        return preg_match("/" . $needle . "$/", $haystack);
    }

    /**
     * @Given /^there should be an address "([^"]*)"$/
     */
    public function thereShouldBeAnAddress($address)
    {
        /** @var \Shopware\Tests\Mink\Page\Account|\Shopware\Tests\Mink\Page\Address $page */
        $page = $this->getPage('Address');

        $testAddress = array_values(array_filter(explode(', ', $address)));

        /** @var array $addressManagementAddressBoxes */
        $addressManagementAddressBoxes = $this->getMultipleElement($page, 'AddressManagementAddressBox');

        /** @var AddressManagementAddressBox $box */
        foreach ($addressManagementAddressBoxes as $box) {
            if ($box->containsAdress($testAddress)) {
                return;
            }
        }

        $message = sprintf('Newly created address not found! (%s)', $address);
        Helper::throwException($message);
    }

    /**
     * @Given /^I create a new address:$/
     */
    public function iCreateANewAddress(TableNode $table)
    {
        /** @var \Shopware\Tests\Mink\Page\Address $page */
        $page = $this->getPage('Address');
        $data = $table->getHash();

        $page->createArbitraryAddress($data);
    }

    /**
     * @Given /^I click "([^"]*)" on address "([^"]*)"$/
     */
    public function iClickOnAddress($locator, $address)
    {
        /** @var \Shopware\Tests\Mink\Page\Account|\Shopware\Tests\Mink\Page\Address $page */
        $page = $this->getPage('Address');

        $testAddress = array_values(array_filter(explode(', ', $address)));

        /** @var array $addressManagementAddressBoxes */
        $addressManagementAddressBoxes = $this->getMultipleElement($page, 'AddressManagementAddressBox');

        /** @var AddressManagementAddressBox $box */
        foreach ($addressManagementAddressBoxes as $box) {
            if ($box->containsAdress($testAddress) === false) {
                continue;
            }

            if ($this->endsWith($locator, 'Button')) {
                Helper::pressNamedButton($box, $locator);
                return;
            }

            Helper::clickNamedLink($box, $locator);
            return;
        }

        $message = sprintf('Given address not found! (%s)', $address);
        Helper::throwException($message);
    }

    /**
     * @Then /^I should see only "([^"]*)" with title "([^"]*)"$/
     */
    public function iShouldSeeOnlyWithTitle($address, $addressTitle)
    {
        /** @var \Shopware\Tests\Mink\Page\Account|\Shopware\Tests\Mink\Page\Address $page */
        $page = $this->getPage('Address');

        $testAddress = array_values(array_filter(explode(', ', $address)));

        /** @var array $addressManagementAddressBoxes */
        $addressManagementAddressBoxes = $this->getMultipleElement($page, 'AddressManagementAddressBox');

        $addressCount = 0;

        /** @var AddressManagementAddressBox $box */
        foreach ($addressManagementAddressBoxes as $box) {
            if ($box->containsAdress($testAddress) && $box->hasTitle($addressTitle)) {
                $addressCount ++;
            }
        }

        if ($addressCount !== 1) {
            $message = sprintf('Wrong number of given addresses titled "%s" found! Expected 1, found %d.', $addressTitle, $addressCount);
            Helper::throwException($message);
        }
    }

    /**
     * @Given /^I change the current address to:$/
     */
    public function iChangeTheCurrentAddressTo(TableNode $table)
    {
        /** @var \Shopware\Tests\Mink\Page\Account|\Shopware\Tests\Mink\Page\AddressEdit $page */
        $page = $this->getPage('AddressEdit');

        $data = $table->getHash();

        Helper::fillForm($page, 'addressForm', $data);
        Helper::pressNamedButton($page, 'saveAddressButton');
    }

    /**
     * @Given /^I delete the address "([^"]*)"$/
     */
    public function iDeleteTheAddress($address)
    {
        /** @var \Shopware\Tests\Mink\Page\Account|\Shopware\Tests\Mink\Page\Address $page */
        $page = $this->getPage('Address');

        $testAddress = array_values(array_filter(explode(', ', $address)));

        /** @var array $addressManagementAddressBoxes */
        $addressManagementAddressBoxes = $this->getMultipleElement($page, 'AddressManagementAddressBox');

        /** @var AddressManagementAddressBox $box */
        foreach ($addressManagementAddressBoxes as $box) {
            if ($box->containsAdress($testAddress) === false) {
                continue;
            }

            Helper::clickNamedLink($box, 'deleteLink');

            /** @var \Shopware\Tests\Mink\Page\AddressDelete $page */
            $page = $this->getPage('AddressDelete');

            Helper::pressNamedButton($page, 'confirmDeleteButton');
            break;
        }
    }

    /**
     * @Then /^there must not be an address "([^"]*)"$/
     */
    public function thereMustNotBeAnAddress($address)
    {
        /** @var \Shopware\Tests\Mink\Page\Account|\Shopware\Tests\Mink\Page\Address $page */
        $page = $this->getPage('Address');

        $testAddress = array_values(array_filter(explode(', ', $address)));

        /** @var array $addressManagementAddressBoxes */
        $addressManagementAddressBoxes = $this->getMultipleElement($page, 'AddressManagementAddressBox');

        /** @var AddressManagementAddressBox $box */
        foreach ($addressManagementAddressBoxes as $box) {
            if ($box->containsAdress($testAddress)) {
                $message = 'Wrong number of given addresses found! Expected 0, found at least 1.';
                Helper::throwException($message);
            }
        }
    }

    /**
     * @Then /^I must not see "([^"]*)" in box with "([^"]*)" title$/
     */
    public function iMustNotSeeInBoxWithTitle($elementName, $title)
    {
        /** @var \Shopware\Tests\Mink\Page\Account|\Shopware\Tests\Mink\Page\Address $page */
        $page = $this->getPage('Address');

        /** @var array $addressManagementAddressBoxes */
        $addressManagementAddressBoxes = $this->getMultipleElement($page, 'AddressManagementAddressBox');

        /** @var AddressManagementAddressBox $box */
        foreach ($addressManagementAddressBoxes as $box) {
            if ($box->hasTitle($title) && ($box->hasLink($elementName) || $box->hasButton($elementName))) {
                $message = sprintf('Wrong number of boxes with title "%s" and delete button found! Expected 0, found at least 1.', $title);
                Helper::throwException($message);
            }
        }
    }
}
