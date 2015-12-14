<?php

namespace Shopware\Tests\Mink;

use Behat\Gherkin\Node\TableNode;

class SitemapContext extends SubContext
{
    /**
     * @Given /^I am on the sitemap\.xml$/
     */
    public function iAmOnTheSitemapXml()
    {
        $this->getPage('Sitemap')->open(['xml' => '.xml']);
    }

    /**
     * @Then /^I should see the group "([^"]*)"$/
     * @Then /^I should see the group "([^"]*)":$/
     */
    public function iShouldSeeTheGroup($name, TableNode $entries = null)
    {
        $this->iShouldSeeTheGroupWithLink($name, null, $entries);
    }

    /**
     * @Then /^I should see the group "([^"]*)" with link "([^"]*)"$/
     * @Then /^I should see the group "([^"]*)" with link "([^"]*)":$/
     */
    public function iShouldSeeTheGroupWithLink($name, $link, TableNode $entries = null)
    {
        $links = [];

        if ($entries) {
            $links = $entries->getHash();
        }

        /** @var \Shopware\Tests\Mink\Page\Sitemap $page */
        $page = $this->getPage('Sitemap');

        /** @var \Shopware\Tests\Mink\Element\SitemapGroup $groups */
        $groups = $this->getMultipleElement($page, 'SitemapGroup');

        $sitemapGroup = $name;

        /** @var \Shopware\Tests\Mink\Element\SitemapGroup $group */
        foreach ($groups as $group) {
            if ($group->getTitle() === $name) {
                $sitemapGroup = $group;
                break;
            }
        }

        $page->checkGroup($sitemapGroup, $link, $links);
    }

    /**
     * @Then /^there should be these links in the XML:$/
     */
    public function thereShouldBeTheseLinksInTheXml(TableNode $links)
    {
        $links = $links->getHash();
        $this->getPage('Sitemap')->checkXml($links);
    }
}
