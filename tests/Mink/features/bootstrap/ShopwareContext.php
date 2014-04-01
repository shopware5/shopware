<?php

use Behat\Behat\Context\Step;
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
     * @Then /^The total sum should be "([^"]*)"$/
     */
    public function theTotalSumShouldBe($sum)
    {
        $this->getPage('CheckoutCart')->assertTotalSum($sum);
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
}
