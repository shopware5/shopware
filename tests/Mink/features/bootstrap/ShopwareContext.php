<?php

use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\TableNode;
require_once 'SubContext.php';

class ShopwareContext extends SubContext
{
    /**
     * @Given /^I am on the frontpage$/
     */
    public function iAmOnTheFrontpage()
    {
        $this->getPage('Homepage')->open();
    }

    /**
     * @When /^I search for "([^"]*)"$/
     */
    public function iSearchFor($searchTerm)
    {
        $this->getPage('Homepage')->searchFor($searchTerm);
    }

    /**
     * @Given /^I should see a banner "([^"]*)"$/
     */
    public function iShouldSeeABanner($image)
    {
        $this->getPage('Homepage')->checkBanner($image);
    }

    /**
     * @Given /^I should see a banner "([^"]*)" to "([^"]*)"$/
     */
    public function iShouldSeeABannerTo($image, $link)
    {
        $this->getPage('Homepage')->checkBanner($image, $link);
    }

    /**
     * @Given /^I should see a banner "([^"]*)" with mapping:$/
     */
    public function iShouldSeeABannerWithMapping($image, TableNode $mapping)
    {
        $mapping = $mapping->getHash();

        $this->getPage('Homepage')->checkBanner($image, $mapping);
    }

    /**
     * @Given /^I should see an article:$/
     */
    public function iShouldSeeAnArticle(TableNode $data)
    {
        $data = $data->getHash();

        $this->getPage('Homepage')->checkArticle($data);
    }

    /**
     * @Given /^I should see a categorie teaser "([^"]*)" with image "([^"]*)" to "([^"]*)"$/
     */
    public function iShouldSeeACategorieTeaserWithImageTo($title, $image, $link)
    {
        $this->getPage('Homepage')->checkCategoryTeaser($title, $image, $link);
    }

    /**
     * @Given /^I should see some blog articles:$/
     */
    public function iShouldSeeSomeBlogArticles(TableNode $articles)
    {
        $articles = $articles->getHash();

        $this->getPage('Homepage')->checkBlogArticles($articles);
    }

    /**
     * @Then /^I should see a banner slider:$/
     */
    public function iShouldSeeABannerSlider(TableNode $articles)
    {
        $articles = $articles->getHash();

        $this->getPage('Homepage')->checkSlider('banner', $articles);
    }

    /**
     * @Given /^I should see a YouTube-Video "([^"]*)"$/
     */
    public function iShouldSeeAYoutubeVideo($code)
    {
        $this->getPage('Homepage')->checkYoutubeVideo($code);
    }

    /**
     * @Then /^I should see a manufacturer slider:$/
     */
    public function iShouldSeeAManufacturerSlider(TableNode $articles)
    {
        $articles = $articles->getHash();

        $this->getPage('Homepage')->checkSlider('manufacturer', $articles);
    }

    /**
     * @Then /^I should see an article slider:$/
     */
    public function iShouldSeeAnArticleSlider(TableNode $articles)
    {
        $articles = $articles->getHash();

        $this->getPage('Homepage')->checkSlider('article', $articles);
    }
}

