<?php

use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\TableNode;
require_once 'SubContext.php';

class NoteContext extends SubContext
{
    /**
     * @Given /^I am on my note$/
     * @When /^I go to my note$/
     */
    public function iAmOnMyNote()
    {
        $this->getPage('Note')->open();
    }

    /**
     * @When /^I remove the article on position (?P<num>\d+) of my note$/
     */
    public function iRemoveTheArticleOnPositionOfMyNote($position)
    {
        $this->getPage('Note')->removeArticle($position);
    }

    /**
     * @When /^I put the article on position (?P<num>\d+) of my note in the basket$/
     */
    public function iPutTheArticleOnPositionOfMyNoteInTheBasket($position)
    {
        $this->getPage('Note')->buyArticle($position);
    }

    /**
     * @When /^I compare the article on position (?P<num>\d+) of my note$/
     */
    public function iCompareTheArticleOnPositionOfMyNote($position)
    {
        $this->getPage('Note')->compareArticle($position);
    }

    /**
     * @When /^I visit the detail page of the article on position (?P<num>\d+) of my note$/
     */
    public function iVisitTheDetailPageOfTheArticleOnPositionOfMyNote($position)
    {
        $this->getPage('Note')->visitArticleDetails($position);
    }

    /**
     * @Then /^My note should look like this:$/
     */
    public function myNoteShouldLookLikeThis(TableNode $articles)
    {
        $articles = $articles->getHash();

        $this->getPage('Note')->checkList($articles);
    }

    /**
     * @Then /^My note should be empty$/
     * @Then /^My note should contain (?P<num>\d+) articles$/
     */
    public function myNoteShouldBeEmpty($count = 0)
    {
        $this->getPage('Note')->countArticles($count);
    }
}