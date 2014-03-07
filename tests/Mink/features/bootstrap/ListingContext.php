<?php

use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\TableNode;
require_once 'SubContext.php';

class ListingContext extends SubContext
{
    /**
     * @Given /^I am on the listing page:$/
     */
    public function iAmOnTheListingPage(TableNode $params)
    {
        $params = $params->getHash();

        $this->getPage('Listing')->openListing($params);
    }

    /**
     * @Then /^The price of the article on position (?P<num>\d+) should be "([^"]*)"$/
     */
    public function thePriceOfTheArticleOnPositionShouldBe($position, $price)
    {
        $this->getPage('Listing')->checkPrice($position, $price);
    }

    /**
     * @When /^I set the filter to:$/
     * @When /^I reset all filters$/
     */
    public function iSetTheFilterTo(TableNode $filter = null)
    {
        $properties = array();

        if($filter)
        {
            $properties = $filter->getHash();
        }

        $this->getPage('Listing')->filter($properties);
    }

    /**
     * @Then /^I should see (?P<num>\d+) articles$/
     */
    public function iShouldSeeArticles($count)
    {
        $this->getPage('Listing')->countArticles($count);
    }
}