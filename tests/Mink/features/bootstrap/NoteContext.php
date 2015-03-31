<?php

use Page\Emotion\Note;
use Element\MultipleElement;
use Element\Emotion\NotePosition;
use Behat\Gherkin\Node\TableNode;

require_once 'SubContext.php';

class NoteContext extends SubContext
{
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

        /** @var Note $page */
        $page = $this->getPage('Note');

        /** @var MultipleElement $notePositions */
        $notePositions = $this->getElement('NotePosition');
        $notePositions->setParent($page);

        $page->checkList($notePositions, $articles);
    }

    private function clickActionLink($position, $name)
    {
        /** @var Note $page */
        $page = $this->getPage('Note');
        $language = Helper::getCurrentLanguage($page);

        /** @var MultipleElement $notePositions */
        $notePositions = $this->getElement('NotePosition');
        $notePositions->setParent($page);

        /** @var NotePosition $notePosition */
        $notePosition = $notePositions->setInstance($position);
        Helper::clickNamedLink($notePosition, $name, $language);
    }
}
