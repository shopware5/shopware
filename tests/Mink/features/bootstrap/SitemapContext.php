<?php

require_once 'SubContext.php';

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
     * @Then /^I should see all active categories$/
     */
    public function iShouldSeeAllActiveCategories()
    {
        if (strpos($this->getSession()->getCurrentUrl(), 'sitemap.xml') !== false) {
            $this->getPage('Sitemap')->checkXmlCategories();
        } else {
            $this->getPage('Sitemap')->checkCategories();
        }
    }

    /**
     * @Then /^I should see the homepage in the xml sitemap$/
     */
    public function iShouldSeeTheHomepageInTheXmlSitemap()
    {
        $this->getPage('Sitemap')->checkXmlHomepage();
    }

    /**
     * @Given /^I should see all custom pages$/
     */
    public function iShouldSeeAllCustomPages()
    {
        $this->getPage('Sitemap')->checkCustomPages();
    }

    /**
     * @Given /^I should see all supplier pages$/
     */
    public function iShouldSeeAllSupplierPages()
    {
        $this->getPage('Sitemap')->checkSupplierPages();
    }

    /**
     * @Given /^I should see all landingpages$/
     */
    public function iShouldSeeAllLandingpages()
    {
        $this->getPage('Sitemap')->checkLandingPages();
    }

}
