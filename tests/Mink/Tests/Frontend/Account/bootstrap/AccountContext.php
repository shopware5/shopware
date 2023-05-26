<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Mink\Tests\Frontend\Account\bootstrap;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\WebAssert;
use Doctrine\DBAL\Connection;
use Shopware\Tests\Mink\Page\Frontend\Account\Account;
use Shopware\Tests\Mink\Page\Frontend\Address\Address;
use Shopware\Tests\Mink\Page\Frontend\Address\AddressDelete;
use Shopware\Tests\Mink\Page\Frontend\Address\AddressEdit;
use Shopware\Tests\Mink\Page\Frontend\Address\Elements\AddressBox;
use Shopware\Tests\Mink\Page\Frontend\Address\Elements\AddressManagementAddressBox;
use Shopware\Tests\Mink\Page\Frontend\Checkout\CheckoutCart;
use Shopware\Tests\Mink\Page\Frontend\Checkout\CheckoutConfirm;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;
use Shopware\Tests\Mink\Tests\General\Helpers\SubContext;

class AccountContext extends SubContext
{
    /**
     * @Given /^I log in with email "(?P<email>[^"]*)" and password "(?P<password>[^"]*)"$/
     */
    public function iLogInAsWithPassword(string $email, string $password): void
    {
        $this->getPage(Account::class)->login($email, $password);
    }

    /**
     * @Given /^I log in successful as "(?P<username>[^"]*)" with email "(?P<email>[^"]*)" and password "(?P<password>[^"]*)"$/
     */
    public function iLogInSuccessfulAsWithPassword(string $username, string $email, string $password): void
    {
        Shopware()->Container()->get(Connection::class)->executeQuery('DELETE FROM s_order_basket');

        $this->getPage(Account::class)->login($email, $password);
        $this->getPage(Account::class)->verifyLogin($username);
    }

    /**
     * @When /^I log me out$/
     */
    public function iLogMeOut(): void
    {
        $this->getPage(Account::class)->logout();
    }

    /**
     * @When /^I click on login again$/
     */
    public function iClickOnLoginAgain(): void
    {
        $this->getPage(Account::class)->clickLoginAgain();
    }

    /**
     * @Then /^I change my email with password "(?P<password>[^"]*)" to "(?P<new>[^"]*)"$/
     * @Then /^I change my email with password "(?P<password>[^"]*)" to "(?P<new>[^"]*)" with confirmation "(?P<confirmation>[^"]*)"$/
     */
    public function iChangeMyEmailWithPasswordToWithConfirmation(string $password, string $email, ?string $emailConfirmation = null): void
    {
        $this->getPage(Account::class)->changeEmail($password, $email, $emailConfirmation);
    }

    /**
     * @Then /^I change my password from "(?P<old>[^"]*)" to "(?P<new>[^"]*)"$/
     * @Then /^I change my password from "(?P<old>[^"]*)" to "(?P<new>[^"]*)" with confirmation "(?P<confirmation>[^"]*)"$/
     */
    public function iChangeMyPasswordFromToWithConfirmation(string $currentPassword, string $password, ?string $passwordConfirmation = null): void
    {
        $this->getPage(Account::class)->changePassword($currentPassword, $password, $passwordConfirmation);
    }

    /**
     * @Given /^I change my billing address:$/
     */
    public function iChangeMyBillingAddress(TableNode $table): void
    {
        $pageInfo = Helper::getPageInfo($this->getSession(), ['controller']);
        if (!\is_array($pageInfo)) {
            Helper::throwException('Could not get page info');
        }
        $pageName = ucfirst($pageInfo['controller']);

        if ($pageName === 'Checkout') {
            $pageName = CheckoutConfirm::class;
        } elseif ($pageName === 'Account') {
            $pageName = Account::class;
        } elseif ($pageName === 'Address') {
            $pageName = Address::class;
        } else {
            Helper::throwException('Wrong page for changing the billing address');
        }

        $page = $this->getPage($pageName);
        $data = $table->getHash();

        $page->changeBillingAddress($data);
    }

    /**
     * @Given /^I change my shipping address:$/
     */
    public function iChangeMyShippingAddress(TableNode $table): void
    {
        $pageInfo = Helper::getPageInfo($this->getSession(), ['controller']);
        if (!\is_array($pageInfo)) {
            Helper::throwException('Could not get page info');
        }
        $pageName = ucfirst($pageInfo['controller']);

        if ($pageName === 'Checkout') {
            $pageName = CheckoutConfirm::class;
        } elseif ($pageName === 'Account') {
            $pageName = Account::class;
        } elseif ($pageName === 'Address') {
            $pageName = Address::class;
        } else {
            Helper::throwException('Wrong page for changing the shipping address');
        }

        $page = $this->getPage($pageName);
        $data = $table->getHash();

        $page->changeShippingAddress($data);
    }

    /**
     * @Given /^the "([^"]*)" address should be "([^"]*)"$/
     */
    public function theAddressShouldBe(string $type, string $address): void
    {
        $this->getPage(Account::class)->checkAddress($type, $address);
    }

    /**
     * @Given /^I register me:$/
     */
    public function iRegisterMe(TableNode $table): void
    {
        $this->getPage(Account::class)->register($table->getHash());
    }

    /**
     * @When /^I change the payment method to (?P<paymentId>\d+)$/
     * @When /^I change the payment method to (?P<paymentId>\d+):$/
     */
    public function iChangeThePaymentMethodTo(int $paymentId, ?TableNode $table = null): void
    {
        $pageInfo = Helper::getPageInfo($this->getSession(), ['controller', 'action']);
        if (!\is_array($pageInfo)) {
            Helper::throwException('Could not get page info');
        }
        $pageName = ucfirst($pageInfo['controller']);

        if ($pageName === 'Checkout') {
            $pageName = ($pageInfo['action'] === 'shippingpayment') ? CheckoutCart::class : CheckoutConfirm::class;
        } elseif ($pageName === 'Account') {
            $pageName = Account::class;
        } else {
            Helper::throwException('Wrong page to change payment method');
        }

        $page = $this->getPage($pageName);
        $data = [
            [
                'field' => 'register[payment]',
                'value' => $paymentId,
            ],
        ];

        if ($table) {
            $data = array_merge($data, $table->getHash());
        }

        $page->changePaymentMethod($data);
    }

    /**
     * @Then /^the current payment method should be "([^"]*)"$/
     */
    public function theCurrentPaymentMethodShouldBe(string $paymentMethod): void
    {
        $pageInfo = Helper::getPageInfo($this->getSession(), ['controller']);
        if (!\is_array($pageInfo)) {
            Helper::throwException('Could not get page info');
        }
        $pageName = (ucfirst($pageInfo['controller']) === 'Checkout') ? CheckoutConfirm::class : Account::class;

        $this->getPage($pageName)->checkPaymentMethod($paymentMethod);
    }

    /**
     * @When /^I choose the address "([^"]*)"$/
     */
    public function iChooseTheAddress(string $name): void
    {
        $page = $this->getPage(Account::class);

        $addresses = $this->getMultipleElement($page, AddressBox::class);

        $page->chooseAddress($addresses, $name);
    }

    /**
     * @Given /^I change my profile with "([^"]*)" "([^"]*)" "([^"]*)"$/
     */
    public function iChangeMyProfileWith(string $salutation, string $firstname, string $lastname): void
    {
        $this->getPage(Account::class)->changeProfile($salutation, $firstname, $lastname);
    }

    /**
     * @Given /^I should be welcomed with "([^"]*)"$/
     */
    public function iShouldBeWelcomedWith(string $welcome): void
    {
        $welcome = preg_replace("/\s\s+/", ' ', $welcome);
        if (!\is_string($welcome)) {
            Helper::throwException('Invalid welcome text');
        }

        $assert = new WebAssert($this->getSession());
        $assert->pageTextContains($welcome);
    }

    /**
     * @Given /^there should be an address "([^"]*)"$/
     */
    public function thereShouldBeAnAddress(string $address): void
    {
        $page = $this->getPage(Address::class);

        $address = str_replace('<ignore>', '', $address);
        $testAddress = array_values(array_filter(explode(', ', $address)));

        $addressManagementAddressBoxes = $this->getMultipleElement($page, AddressManagementAddressBox::class);

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
    public function iCreateANewAddress(TableNode $table): void
    {
        $page = $this->getPage(Address::class);
        $data = $table->getHash();

        $page->createArbitraryAddress($data);
    }

    /**
     * @Given /^I click "([^"]*)" on address "([^"]*)"$/
     */
    public function iClickOnAddress(string $locator, string $address): void
    {
        $page = $this->getPage(Address::class);

        $testAddress = array_values(array_filter(explode(', ', $address)));

        $addressManagementAddressBoxes = $this->getMultipleElement($page, AddressManagementAddressBox::class);

        foreach ($addressManagementAddressBoxes as $box) {
            if ($box->containsAdress($testAddress) === false) {
                continue;
            }

            if (str_ends_with($locator, 'Button')) {
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
    public function iShouldSeeOnlyWithTitle(string $address, string $addressTitle): void
    {
        $page = $this->getPage(Address::class);

        $testAddress = array_values(array_filter(explode(', ', $address)));

        $addressManagementAddressBoxes = $this->getMultipleElement($page, AddressManagementAddressBox::class);

        $addressCount = 0;

        foreach ($addressManagementAddressBoxes as $box) {
            if ($box->containsAdress($testAddress) && $box->hasTitle($addressTitle)) {
                ++$addressCount;
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
    public function iChangeTheCurrentAddressTo(TableNode $table): void
    {
        $page = $this->getPage(AddressEdit::class);

        $data = $table->getHash();

        Helper::fillForm($page, 'addressForm', $data);
        Helper::pressNamedButton($page, 'saveAddressButton');
    }

    /**
     * @Given /^I delete the address "([^"]*)"$/
     */
    public function iDeleteTheAddress(string $address): void
    {
        $page = $this->getPage(Address::class);

        $testAddress = array_values(array_filter(explode(', ', $address)));

        $addressManagementAddressBoxes = $this->getMultipleElement($page, AddressManagementAddressBox::class);

        foreach ($addressManagementAddressBoxes as $box) {
            if ($box->containsAdress($testAddress) === false) {
                continue;
            }

            Helper::clickNamedLink($box, 'deleteLink');

            $page = $this->getPage(AddressDelete::class);

            Helper::pressNamedButton($page, 'confirmDeleteButton');
            break;
        }
    }

    /**
     * @Then /^there must not be an address "([^"]*)"$/
     */
    public function thereMustNotBeAnAddress(string $address): void
    {
        $page = $this->getPage(Address::class);

        $testAddress = array_values(array_filter(explode(', ', $address)));

        $addressManagementAddressBoxes = $this->getMultipleElement($page, AddressManagementAddressBox::class);

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
    public function iMustNotSeeInBoxWithTitle(string $elementName, string $title): void
    {
        $page = $this->getPage(Address::class);

        $addressManagementAddressBoxes = $this->getMultipleElement($page, AddressManagementAddressBox::class);

        foreach ($addressManagementAddressBoxes as $box) {
            if ($box->hasTitle($title) && ($box->hasLink($elementName) || $box->hasButton($elementName))) {
                $message = sprintf('Wrong number of boxes with title "%s" and delete button found! Expected 0, found at least 1.', $title);
                Helper::throwException($message);
            }
        }
    }
}
