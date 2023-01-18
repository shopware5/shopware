<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Tests\Mink\Tests\Backend\Listing\bootstrap;

use Behat\Gherkin\Node\TableNode;
use Shopware\Tests\Mink\Page\Frontend\Article\Elements\ArticleBox;
use Shopware\Tests\Mink\Page\Frontend\Listing\Elements\Paging;
use Shopware\Tests\Mink\Page\Frontend\Listing\Listing;
use Shopware\Tests\Mink\Page\Helper\Elements\FilterGroup;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;
use Shopware\Tests\Mink\Tests\General\Helpers\SubContext;

class ListingContext extends SubContext
{
    /**
     * @Given /^I am on the listing page:$/
     * @Given /^I go to the listing page:$/
     */
    public function iAmOnTheListingPage(TableNode $params): void
    {
        $params = $params->getHash();

        $this->getPage(Listing::class)->openListing($params);
    }

    /**
     * @Given /^I am on the listing page for category (?P<categoryId>\d+)$/
     * @Given /^I am on the listing page for category (?P<categoryId>\d+) on page (?P<page>\d+)$/
     */
    public function iAmOnTheListingPageForCategoryOnPage(int $categoryId, ?int $page = null): void
    {
        $params = [
            [
                'parameter' => 'sCategory',
                'value' => $categoryId,
            ],
        ];

        if ($page) {
            $params[] = [
                'parameter' => 'sPage',
                'value' => $page,
            ];
        }

        $this->getPage(Listing::class)->openListing($params, false);
    }

    /**
     * @When /^I set the filter to:$/
     * @When /^I reset all filters$/
     */
    public function iSetTheFilterTo(TableNode $filter = null): void
    {
        $properties = [];

        if ($filter) {
            $properties = $filter->getHash();
        }

        $page = $this->getPage(Listing::class);

        $filterGroups = $this->getMultipleElement($page, FilterGroup::class);
        $page->filter($filterGroups, $properties);
    }

    /**
     * @Then /^the articles should be shown in a table-view$/
     */
    public function theArticlesShouldBeShownInATableView(): void
    {
        $this->getPage(Listing::class)->checkView('viewTable');
    }

    /**
     * @Then /^the articles should be shown in a list-view$/
     */
    public function theArticlesShouldBeShownInAListView(): void
    {
        $this->getPage(Listing::class)->checkView('viewList');
    }

    /**
     * @Then /^the article on position (?P<num>\d+) should have this properties:$/
     */
    public function theArticleOnPositionShouldHaveThisProperties(int $position, TableNode $properties): void
    {
        $page = $this->getPage(Listing::class);

        $articleBox = $this->getMultipleElement($page, ArticleBox::class, $position);
        $properties = Helper::convertTableHashToArray($properties->getHash());
        $page->checkArticleBox($articleBox, $properties);
    }

    /**
     * @When /^I order the article on position (?P<position>\d+)$/
     */
    public function iOrderTheArticleOnPosition(int $position): void
    {
        $page = $this->getPage(Listing::class);

        $articleBox = $this->getMultipleElement($page, ArticleBox::class, $position);
        Helper::clickNamedLink($articleBox, 'order');
    }

    /**
     * @When /^I browse to (previous|next) page$/
     * @When /^I browse to (previous|next) page (\d+) times$/
     */
    public function iBrowseTimesToPage(string $direction, int $steps = 1): void
    {
        $this->getElement(Paging::class)->moveDirection($direction, $steps);
    }

    /**
     * @Then /^I should not be able to browse to (previous|next) page$/
     */
    public function iShouldNotBeAbleToBrowseToPage(string $direction): void
    {
        $this->getElement(Paging::class)->noElement($direction);
    }

    /**
     * @When /^I browse to page (\d+)$/
     */
    public function iBrowseToPage(int $page): void
    {
        $this->getElement(Paging::class)->moveToPage($page);
    }

    /**
     * @Given /^I should not be able to browse to page (\d+)$/
     */
    public function iShouldNotBeAbleToBrowseToPage2(string $page): void
    {
        $this->getElement(Paging::class)->noElement($page);
    }

    /**
     * @Then /^I should see the article "([^"]*)" in listing$/
     */
    public function iShouldSeeTheArticleInListing(string $name): void
    {
        $this->getPage(Listing::class)->checkListing($name);
    }

    /**
     * @Given /^I should not see the article "([^"]*)" in listing$/
     */
    public function iShouldNotSeeTheArticleInListing(string $name): void
    {
        $this->getPage(Listing::class)->checkListing($name, true);
    }
}
