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
        $this->getPage('CheckoutCart')->assertSum($sum, '#aggregation p.textright');
    }

    /**
     * @Given /^The shipping costs should be "([^"]*)"$/
     */
    public function theShippingCostsShouldBe($costs)
    {
        $this->getPage('CheckoutCart')->assertSum($costs, '#aggregation div:nth-of-type(1) p.textright');
    }

    /**
     * @Then /^The total sum should be "([^"]*)"$/
     */
    public function theTotalSumShouldBe($sum)
    {
        $this->getPage('CheckoutCart')->assertSum($sum, '#aggregation div.totalamount p.textright');
    }

    /**
     * @Given /^The sum without VAT should be "([^"]*)"$/
     */
    public function theSumWithoutVatShouldBe($sum)
    {
        $this->getPage('CheckoutCart')->assertSum($sum, '#aggregation div.tax p.textright');
    }

    /**
     * @Given /^The VAT should be "([^"]*)"$/
     */
    public function theVatShouldBe($vat)
    {
        $this->getPage('CheckoutCart')->assertSum($vat, '#aggregation div:nth-of-type(4) p.textright');
    }

    /**
     * @When /^I add the voucher "([^"]*)" to my basket$/
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
     * @When /^I add the article "([^"]*)" to my basket$/
     */
    public function iAddTheArticleToMyBasket($article)
    {
        $this->getPage('CheckoutCart')->addArticle($article);
    }

    /**
     * @When /^I remove the article on position "([^"]*)"$/
     */
    public function iRemoveTheArticleOnPosition($position)
    {
        $this->getPage('CheckoutCart')->removeArticle($position);
    }

    /**
     * @Given /^I log in as "([^"]*)" with password "([^"]*)" on checkout$/
     */
    public function iLogInAsWithPasswordOnCheckout($email, $password)
    {
        $this->getPage('CheckoutConfirm')->login($email, $password);
    }

    /**
     * @Then /^I change my billing adress on confirm page:$/
     */
    public function iChangeMyBillingAdressOnConfirmPage(TableNode $fieldValues)
    {
        $values = $fieldValues->getHash();

        $this->getPage('CheckoutConfirm')->changeBilling($values);
    }

    /**
     * @Then /^I change my shipping adress on confirm page:$/
     */
    public function iChangeMyShippingAdressOnConfirmPage(TableNode $fieldValues)
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
     * @When /^I change my payment method to debit using account of "([^"]*)" \(no\. "(\d+)"\) of bank "([^"]*)" \(code "(\d+)"\)$/
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
        $this->getPage('CheckoutCart')->proceedToCheckout();
    }
}

