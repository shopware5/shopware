<?php

namespace Shopware\Tests\Mink;

use Shopware\Tests\Mink\Page\Note;
use Shopware\Tests\Mink\Element\NotePosition;
use Behat\Gherkin\Node\TableNode;

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

    private function clickActionLink($position, $name)
    {
        /** @var Note $page */
        $page = $this->getPage('Note');

        /** @var NotePosition $notePosition */
        $notePosition = $this->getMultipleElement($page, 'NotePosition', $position);
        Helper::clickNamedLink($notePosition, $name);
    }

    /**
     * @Given /^the note contains the following products:$/
     */
    public function theNoteContainsTheFollowingProducts(TableNode $items)
    {
        /** @var Note $page */
        $page = $this->getPage('Note');
        $page->open();
        $page->fillNoteWithProducts($items->getHash());
        $this->theNoteShouldContainTheFollowingProducts($items);
    }

    /**
     * @Then /^the note should contain the following products:$/
     */
    public function theNoteShouldContainTheFollowingProducts(TableNode $items)
    {
        /** @var Note $page */
        $page = $this->getPage('Note');

        /** @var NotePosition $cartPosition */
        $notePositions = $this->getMultipleElement($page, 'NotePosition');

        $page->checkNoteProducts($notePositions, $items->getHash());
    }
}
