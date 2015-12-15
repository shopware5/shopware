<?php

namespace Shopware\Tests\Mink;

use Shopware\Tests\Mink\Page\Listing;
use Shopware\Tests\Mink\Element\FilterGroup;
use Shopware\Tests\Mink\Element\ArticleBox;
use Behat\Gherkin\Node\TableNode;

class ListingContext extends SubContext
{
    /**
     * @Given /^I am on the listing page:$/
     * @Given /^I go to the listing page:$/
     */
    public function iAmOnTheListingPage(TableNode $params)
    {
        $params = $params->getHash();

        $this->getPage('Listing')->openListing($params);
    }

    /**
     * @Given /^I am on the listing page for category (?P<categoryId>\d+)$/
     * @Given /^I am on the listing page for category (?P<categoryId>\d+) on page (?P<page>\d+)$/
     */
    public function iAmOnTheListingPageForCategoryOnPage($categoryId, $page = null)
    {
        $params = [
            [
                'parameter' => 'sCategory',
                'value'=> $categoryId
            ]
        ];

        if ($page) {
            $params[] = [
                'parameter' => 'sPage',
                'value'=> $page
            ];
        }

        $this->getPage('Listing')->openListing($params, false);
    }

        /**
     * @When /^I set the filter to:$/
     * @When /^I reset all filters$/
     */
    public function iSetTheFilterTo(TableNode $filter = null)
    {
        $properties = [];

        if ($filter) {
            $properties = $filter->getHash();
        }

        /** @var Listing $page */
        $page = $this->getPage('Listing');

        /** @var FilterGroup $filterGroups */
        $filterGroups = $this->getMultipleElement($page, 'FilterGroup');
        $page->filter($filterGroups, $properties);
    }

    /**
     * @Then /^the articles should be shown in a table-view$/
     */
    public function theArticlesShouldBeShownInATableView()
    {
        $this->getPage('Listing')->checkView('viewTable');
    }

    /**
     * @Then /^the articles should be shown in a list-view$/
     */
    public function theArticlesShouldBeShownInAListView()
    {
        $this->getPage('Listing')->checkView('viewList');
    }

    /**
     * @Then /^the article on position (?P<num>\d+) should have this properties:$/
     */
    public function theArticleOnPositionShouldHaveThisProperties($position, TableNode $properties)
    {
        /** @var Listing $page */
        $page = $this->getPage('Listing');

        /** @var ArticleBox $articleBox */
        $articleBox = $this->getMultipleElement($page, 'ArticleBox', $position);
        $properties = Helper::convertTableHashToArray($properties->getHash());
        $page->checkArticleBox($articleBox, $properties);
    }

    /**
     * @When /^I order the article on position (?P<position>\d+)$/
     */
    public function iOrderTheArticleOnPosition($position)
    {
        /** @var Listing $page */
        $page = $this->getPage('Listing');

        /** @var ArticleBox $articleBox */
        $articleBox = $this->getMultipleElement($page, 'ArticleBox', $position);
        Helper::clickNamedLink($articleBox, 'order');
    }

    /**
     * @When /^I browse to (previous|next) page$/
     * @When /^I browse to (previous|next) page (\d+) times$/
     */
    public function iBrowseTimesToPage($direction, $steps = 1)
    {
        $this->getElement('Paging')->moveDirection($direction, $steps);
    }

    /**
     * @Then /^I should not be able to browse to (previous|next) page$/
     */
    public function iShouldNotBeAbleToBrowseToPage($direction)
    {
        $this->getElement('Paging')->noElement($direction);
    }

    /**
     * @When /^I browse to page (\d+)$/
     */
    public function iBrowseToPage($page)
    {
        $this->getElement('Paging')->moveToPage($page);
    }

    /**
     * @Given /^I should not be able to browse to page (\d+)$/
     */
    public function iShouldNotBeAbleToBrowseToPage2($page)
    {
        $this->getElement('Paging')->noElement($page);
    }

    /**
     * @Then /^I should see the article "([^"]*)" in listing$/
     */
    public function iShouldSeeTheArticleInListing($name)
    {
        $this->getPage('Listing')->checkListing($name);
    }

    /**
     * @Given /^I should not see the article "([^"]*)" in listing$/
     */
    public function iShouldNotSeeTheArticleInListing($name)
    {
        $this->getPage('Listing')->checkListing($name, true);
    }
}
