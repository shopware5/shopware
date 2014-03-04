<?php

use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\TableNode;
require_once 'SubContext.php';

class ShopwareContext extends SubContext
{
    /**
     * @Given /^I am on the frontpage$/
     */
    public function iAmOnTheFrontpage()
    {
        $this->getPage('Homepage')->open();
    }

    /**
     * @When /^I search for "([^"]*)"$/
     */
    public function iSearchFor($searchTerm)
    {
        $this->getPage('Homepage')->searchFor($searchTerm);
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
     * @Given /^I am on the detail page for article "([^"]*)"$/
     */
    public function iAmOnTheDetailPageForArticle($articleId)
    {
        $this->getPage('Detail')->open(array('articleId' => $articleId));
    }

    /**
     * @When /^I put the article "([^"]*)" times into the basket$/
     */
    public function iPutTheArticleTimesIntoTheBasket($quantity)
    {
        $this->getPage('Detail')->toBasket($quantity);
    }

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
     * @When /^I proceed to checkout$/
     */
    public function iProceedToCheckout()
    {
        $this->getPage('CheckoutCart')->proceedToCheckout();
    }

    /**
     * @Given /^I am on my account page$/
     */
    public function iAmOnMyAccountPage()
    {
        $this->getPage('Account')->open();
    }

    /**
     * @Then /^I change my password from "([^"]*)" to "([^"]*)" with confirmation "([^"]*)"$/
     */
    public function iChangeMyPasswordFromToWithConfirmation($currentPassword, $password, $passwordConfirmation)
    {
        $this->getPage('Account')->changePassword($currentPassword, $password, $passwordConfirmation);
    }

    /**
     * @Then /^I change my email with password "([^"]*)" to "([^"]*)" with confirmation "([^"]*)"$/
     */
    public function iChangeMyEmailWithPasswordToWithConfirmation($password, $email, $emailConfirmation)
    {
        $this->getPage('Account')->changeEmail($password, $email, $emailConfirmation);
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

    /**
     * @When /^I add the article "([^"]*)" to my basket$/
     */
    public function iAddTheArticleToMyBasket($article)
    {
        $this->getPage('CheckoutCart')->addArticle($article);
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
     * @When /^I remove the article on position "([^"]*)"$/
     */
    public function iRemoveTheArticleOnPosition($position)
    {
        $position+=3;
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
     * @Given /^I register me$/
     */
    public function iRegisterMe(TableNode $fieldValues)
    {
        $values = $fieldValues->getHash();

        $this->getPage('Account')->register($values);
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
     * @Then /^The price of the article on position "([^"]*)" should be "([^"]*)"$/
     */
    public function thePriceOfTheArticleOnPositionShouldBe($position, $price)
    {
        $this->getPage('Listing')->checkPrice($position, $price);
    }

    /**
     * @Given /^I should see a banner "([^"]*)" with mapping:$/
     */
    public function iShouldSeeABannerWithMapping($image, TableNode $mapping)
    {
        $mapping = $mapping->getHash();

        $this->getPage('Homepage')->checkBanner($image, $mapping);
    }

    /**
     * @Given /^I should see a banner "([^"]*)" to "([^"]*)"$/
     */
    public function iShouldSeeABannerTo($image, $link)
    {
        $this->getPage('Homepage')->checkBanner($image, $link);
    }

    /**
     * @Given /^I should see a banner "([^"]*)"$/
     */
    public function iShouldSeeABanner($image)
    {
        $this->getPage('Homepage')->checkBanner($image);
    }

    /**
     * @Given /^I should see some blog articles:$/
     */
    public function iShouldSeeSomeBlogArticles(TableNode $articles)
    {
        $articles = $articles->getHash();

        $this->getPage('Homepage')->checkBlogArticles($articles);
    }

    /**
     * @Given /^I should see a YouTube-Video "([^"]*)"$/
     */
    public function iShouldSeeAYoutubeVideo($code)
    {
        $this->getPage('Homepage')->checkYoutubeVideo($code);
    }

    /**
     * @Then /^I should see a banner slider:$/
     */
    public function iShouldSeeABannerSlider(TableNode $articles)
    {
        $articles = $articles->getHash();

        $this->getPage('Homepage')->checkSlider('banner', $articles);
    }

    /**
     * @Then /^I should see a manufacturer slider:$/
     */
    public function iShouldSeeAManufacturerSlider(TableNode $articles)
    {
        $articles = $articles->getHash();

        $this->getPage('Homepage')->checkSlider('manufacturer', $articles);
    }

    /**
     * @Then /^I should see an article slider:$/
     */
    public function iShouldSeeAnArticleSlider(TableNode $articles)
    {
        $articles = $articles->getHash();

        $this->getPage('Homepage')->checkSlider('article', $articles);
    }

}

