<?php

use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\TableNode;
require_once 'SubContext.php';

class DetailContext extends SubContext
{
    /**
     * @Given /^I am on the detail page for article (?P<articleId>\d+)$/
     */
    public function iAmOnTheDetailPageForArticle($articleId)
    {
        $this->getPage('Detail')->open(array('articleId' => $articleId));
    }

    /**
     * @When /^I put the article "(?P<quantity>[^"]*)" times into the basket$/
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

    /**
     * @Given /^I should see an average customer evaluation of (?P<average>\d+) from following evaluations$/
     */
    public function iShouldSeeAnAverageCustomerEvaluationOfFromFollowingEvaluations($average, TableNode $evaluations)
    {
        $evaluations = $evaluations->getHash();

        $this->getPage('Detail')->checkEvaluations($average, $evaluations);
    }

}

