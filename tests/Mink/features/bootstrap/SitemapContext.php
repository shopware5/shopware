<?php

class SitemapContext extends SubContext
{
    /**
     * @Given /^I am on the sitemap\.xml$/
     */
    public function iAmOnTheSitemapXml()
    {
        $this->getPage('Sitemap')->open(array('xml' => '.xml'));
    }

    /**
     * @Then /^I should see the group "([^"]*)"$/
     * @Then /^I should see the group "([^"]*)":$/
     */
    public function iShouldSeeTheGroup($name, \Behat\Gherkin\Node\TableNode $entries = null)
    {
        $this->iShouldSeeTheGroupWithLink($name, null, $entries);
    }

    /**
     * @Then /^I should see the group "([^"]*)" with link "([^"]*)"$/
     * @Then /^I should see the group "([^"]*)" with link "([^"]*)":$/
     */
    public function iShouldSeeTheGroupWithLink($name, $link, \Behat\Gherkin\Node\TableNode $entries = null)
    {
        $links = array();

        if($entries) {
            $links = $entries->getHash();
        }

        /** @var \Page\Emotion\Sitemap $page */
        $page = $this->getPage('Sitemap');

        /** @var \Element\Emotion\SitemapGroup $groups */
        $groups = $this->getMultipleElement($page, 'SitemapGroup');

        $sitemapGroup = $name;

        /** @var \Element\Emotion\SitemapGroup $group */
        foreach($groups as $group) {
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
    public function thereShouldBeTheseLinksInTheXml(\Behat\Gherkin\Node\TableNode $links)
    {
        $links = $links->getHash();
        $this->getPage('Sitemap')->checkXml($links);
    }
}
