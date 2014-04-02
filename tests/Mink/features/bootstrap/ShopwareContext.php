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
     * @When /^I search for "(?P<searchTerm>[^"]*)"$/
     */
    public function iSearchFor($searchTerm)
    {
        $this->getPage('Homepage')->searchFor($searchTerm);
    }

    /**
     * @When /^I received the search-results for "(?P<searchTerm>[^"]*)"$/
     */
    public function iReceivedTheSearchResultsFor($searchTerm)
    {
        $this->getPage('Homepage')->receiveSearchResultsFor($searchTerm);
    }

    /**
     * @Given /^I should see a banner "(?P<image>[^"]*)"$/
     */
    public function iShouldSeeABanner($image)
    {
        $this->getPage('Homepage')->checkBanner($image);
    }

    /**
     * @Given /^I should see a banner "(?P<image>[^"]*)" to "(?P<link>[^"]*)"$/
     */
    public function iShouldSeeABannerTo($image, $link)
    {
        $this->getPage('Homepage')->checkBanner($image, $link);
    }

    /**
     * @Given /^I should see a banner "(?P<image>[^"]*)" with mapping:$/
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
     * @Given /^I should see a categorie teaser "(?P<title>[^"]*)" with image "(?P<image>[^"]*)" to "(?P<link>[^"]*)"$/
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
     * @Given /^I should see a YouTube-Video "(?P<code>[^"]*)"$/
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

    /**
     * @Then /^The comparison should look like this:$/
     */
    public function theComparisonShouldLookLikeThis(TableNode $articles)
    {
        $articles = $articles->getHash();

        $this->getPage('Homepage')->checkComparison($articles);
    }

    /**
     * @Given /^the "(?P<name>[^"]*)" plugin is enabled$/
     */
    public function thePluginIsEnabled($name)
    {
        /** @var \Shopware\Components\Plugin\Manager $pluginManager */
        $pluginManager = $this->getContainer()->get('shopware.plugin_Manager');
        $pluginManager->refreshPluginList();

        $plugin = $pluginManager->getPluginByName($name);
        $pluginManager->installPlugin($plugin);
        $pluginManager->activatePlugin($plugin);
    }

    /**
     * @Given /^the articles from "(?P<name>[^"]*)" have tax id (?P<num>\d+)$/
     */
    public function theArticlesFromHaveTaxId($supplier, $taxId)
    {
        $taxId = intval($taxId);

        $sql = sprintf(
            'UPDATE s_articles SET taxID = %d WHERE supplierID =
                    (SELECT id FROM s_articles_supplier WHERE name = "%s")',
            $taxId,
            $supplier
        );
        $this->getContainer()->get('db')->exec($sql);
    }
}

