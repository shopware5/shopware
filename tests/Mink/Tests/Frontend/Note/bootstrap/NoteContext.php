<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Mink\Tests\Frontend\Note\bootstrap;

use Behat\Gherkin\Node\TableNode;
use Shopware\Tests\Mink\Page\Frontend\Note\Elements\NotePosition;
use Shopware\Tests\Mink\Page\Frontend\Note\Note;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;
use Shopware\Tests\Mink\Tests\General\Helpers\SubContext;

class NoteContext extends SubContext
{
    /**
     * @When /^I remove the article from my note$/
     * @When /^I remove the article on position (?P<num>\d+) of my note$/
     */
    public function iRemoveTheArticleOnPositionOfMyNote(int $position = 1): void
    {
        $this->pressActionButton($position, 'remove');
    }

    /**
     * @When /^I put the article on position (?P<num>\d+) of my note in the basket$/
     */
    public function iPutTheArticleOnPositionOfMyNoteInTheBasket(int $position): void
    {
        $this->clickActionLink($position, 'order');
    }

    /**
     * @When /^I compare the article on position (?P<num>\d+) of my note$/
     */
    public function iCompareTheArticleOnPositionOfMyNote(int $position): void
    {
        $this->pressActionButton($position, 'compare');
    }

    /**
     * @When /^I visit the detail page of the article on position (?P<num>\d+) of my note$/
     */
    public function iVisitTheDetailPageOfTheArticleOnPositionOfMyNote(int $position): void
    {
        $this->clickActionLink($position, 'details');
    }

    /**
     * @Given /^the note contains the following products:$/
     */
    public function theNoteContainsTheFollowingProducts(TableNode $items): void
    {
        $page = $this->getPage(Note::class);
        $page->open();
        $page->fillNoteWithProducts($items->getHash());
        $this->theNoteShouldContainTheFollowingProducts($items);
    }

    /**
     * @Then /^the note should contain the following products:$/
     */
    public function theNoteShouldContainTheFollowingProducts(TableNode $items): void
    {
        $page = $this->getPage(Note::class);

        $notePositions = $this->getMultipleElement($page, NotePosition::class);

        $page->checkNoteProducts($notePositions, $items->getHash());
    }

    private function clickActionLink(int $position, string $name): void
    {
        $page = $this->getPage(Note::class);

        $notePosition = $this->getMultipleElement($page, NotePosition::class, $position);
        Helper::clickNamedLink($notePosition, $name);
    }

    private function pressActionButton(int $position, string $name): void
    {
        $page = $this->getPage(Note::class);

        $notePosition = $this->getMultipleElement($page, NotePosition::class, $position);
        Helper::pressNamedButton($notePosition, $name);
    }
}
