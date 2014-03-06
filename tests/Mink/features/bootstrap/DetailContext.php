<?php

use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\TableNode;
require_once 'SubContext.php';

class DetailContext extends SubContext
{
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
     * @Given /^I go to previous article$/
     */
    public function iGoToPreviousArticle()
    {
        $this->getPage('Detail')->goToNeighbor('back');
    }

    /**
     * @Given /^I go to next article$/
     */
    public function iGoToNextArticle()
    {
        $this->getPage('Detail')->goToNeighbor('next');
    }
}

