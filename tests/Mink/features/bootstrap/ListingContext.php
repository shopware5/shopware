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
     * @Then /^The price of the article on position "([^"]*)" should be "([^"]*)"$/
     */
    public function thePriceOfTheArticleOnPositionShouldBe($position, $price)
    {
        $this->getPage('Listing')->checkPrice($position, $price);
    }
}