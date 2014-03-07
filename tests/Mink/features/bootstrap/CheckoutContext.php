<?php

use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\TableNode;
require_once 'SubContext.php';

class CheckoutContext extends SubContext
{
    /**
     * @Then /^The sum should be "([^"]*)"$/
     */
    public function theSumShouldBe($sum)
    {
        $this->getPage('CheckoutCart')->checkSum($sum);
    }

    /**
     * @Given /^The shipping costs should be "([^"]*)"$/
     */
    public function theShippingCostsShouldBe($costs)
    {
        $this->getPage('CheckoutCart')->checkShippingCosts($costs);
    }

    /**
     * @Then /^The total sum should be "([^"]*)"$/
     */
    public function theTotalSumShouldBe($sum)
    {
        $this->getPage('CheckoutCart')->checkTotalSum($sum);
    }

    /**
     * @Given /^The sum without VAT should be "([^"]*)"$/
     */
    public function theSumWithoutVatShouldBe($sum)
    {
        $this->getPage('CheckoutCart')->checkSumWithoutVat($sum);
    }

    /**
     * @Given /^The VAT should be "([^"]*)"$/
     */
    public function theVatShouldBe($vat)
    {
        $this->getPage('CheckoutCart')->checkVat($vat);
    }

    /**
     * @When /^I add the voucher "(?P<code>[^"]*)" to my basket$/
     */
    public function iAddTheVoucherToMyBasket($voucher)
    {
        $this->getPage('CheckoutCart')->addVoucher($voucher);
    }

    /**
     * @When /^I remove the voucher$/
     */
    public function iRemoveTheVoucher()
    {
        $this->getPage('CheckoutCart')->removeVoucher();
    }

    /**
     * @When /^I add the article "(?P<articleNr>[^"]*)" to my basket$/
     */
    public function iAddTheArticleToMyBasket($article)
    {
        $this->getPage('CheckoutCart')->addArticle($article);
    }

    /**
     * @When /^I remove the article on position (?P<num>\d+)$/
     */
    public function iRemoveTheArticleOnPosition($position)
    {
        $this->getPage('CheckoutCart')->removeArticle($position);
    }

    /**
     * @Given /^I log in as "(?P<email>[^"]*)" with password "(?P<password>[^"]*)" on checkout$/
     */
    public function iLogInAsWithPasswordOnCheckout($email, $password)
    {
        $this->getPage('CheckoutConfirm')->login($email, $password);
    }

    /**
     * @Then /^I change my billing address on confirm page:$/
     */
    public function iChangeMyBillingAddressOnConfirmPage(TableNode $fieldValues)
    {
        $values = $fieldValues->getHash();

        $this->getPage('CheckoutConfirm')->changeBilling($values);
    }

    /**
     * @Then /^I change my shipping address on confirm page:$/
     */
    public function iChangeMyShippingAddressOnConfirmPage(TableNode $fieldValues)
    {
        $values = $fieldValues->getHash();

        $this->getPage('CheckoutConfirm')->changeShipping($values);
    }

    /**
     * @When /^I change my payment method to "([^"]*)"$/
     */
    public function iChangeMyPaymentMethodTo($value)
    {
        $this->getPage('CheckoutConfirm')->changePayment($value);
    }

    /**
     * @When /^I change my payment method to debit using account of "(?P<name>[^"]*)" \(no\. "(?P<account>\d+)"\) of bank "(?P<bank>[^"]*)" \(code "(?P<code>\d+)"\)$/
     */
    public function iChangeMyPaymentMethodToDebitUsingAccountOfNoOfBankCode($name, $kto, $bank, $blz)
    {
        $data = array('kontonr' => $kto,
                      'blz' => $blz,
                      'bank' => $bank,
                      'bank2' => $name);

        $this->getPage('CheckoutConfirm')->changePayment(2, $data);
    }

    /**
     * @When /^I change my delivery to "([^"]*)"$/
     */
    public function iChangeMyDeliveryTo($value)
    {
        $this->getPage('CheckoutConfirm')->changeDelivery($value);
    }

    /**
     * @When /^I proceed to checkout$/
     */
    public function iProceedToCheckout()
    {
        $this->getPage('CheckoutConfirm')->proceedToCheckout();
    }
}