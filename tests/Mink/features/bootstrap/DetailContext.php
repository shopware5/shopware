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
     * @When /^I put the article into the basket$/
     * @When /^I put the article "(?P<quantity>[^"]*)" times into the basket$/
     */
    public function iPutTheArticleTimesIntoTheBasket($quantity = 1)
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

    /**
     * @When /^I choose the following article configuration:$/
     */
    public function iChooseTheFollowingArticleConfiguration(TableNode $configuration)
    {
        $configuration = $configuration->getHash();

        $this->getPage('Detail')->configure($configuration);
    }

    /**
     * @When /^I subscribe to the notifier with "([^"]*)"$/
     */
    public function iSubscribeToTheNotifierWith($email)
    {
        $this->getElement('Notifier')->submit($email);
    }

    /**
     * @Then /^I can not select "([^"]*)" from "([^"]*)"$/
     */
    public function iCanNotSelectFrom($configuratorOption, $configuratorGroup)
    {
        $this->getPage('Detail')->canNotSelectConfiguratorOption($configuratorOption, $configuratorGroup);
    }
}

