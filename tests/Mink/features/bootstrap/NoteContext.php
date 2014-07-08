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
     * @When /^I remove the article from my note$/
     * @When /^I remove the article on position (?P<num>\d+) of my note$/
     */
    public function iRemoveTheArticleOnPositionOfMyNote($position = 1)
    {
        $this->clickActionLink($position, 'remove');
    }

    /**
     * @When /^I put the article on position (?P<num>\d+) of my note in the basket$/
     */
    public function iPutTheArticleOnPositionOfMyNoteInTheBasket($position)
    {
        $this->clickActionLink($position, 'order');
    }

    /**
     * @When /^I compare the article on position (?P<num>\d+) of my note$/
     */
    public function iCompareTheArticleOnPositionOfMyNote($position)
    {
        $this->clickActionLink($position, 'compare');
    }

    /**
     * @When /^I visit the detail page of the article on position (?P<num>\d+) of my note$/
     */
    public function iVisitTheDetailPageOfTheArticleOnPositionOfMyNote($position)
    {
        $this->clickActionLink($position, 'details');
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

    private function clickActionLink($position, $name)
    {
        $language = $this->getElement('LanguageSwitcher')->getCurrentLanguage();

        /** @var \Emotion\NotePosition $notePosition */
        $notePosition = Helper::getMultipleElement($this, 'NotePosition', $position + 1);
        $notePosition->clickActionLink($name, $language);
    }
}