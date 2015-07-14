<?php

class SeoContext extends SubContext
{
    /**
     * @Then /^I should see canonical link "(?P<path>[^"]*)"$/
     * @Then /^I should see canonical link "(?P<path>[^"]*)" and page (?P<page>\d+)$/
     */
    public function iShouldSeeCanonicalLinkWithQuery($path, $page = null)
    {
        if ($page) {
            $params = array(
                'p' => $page
            );
        } else {
            $params = null;
        }

        $this->getPage('GenericPage')->checkCanonical($path, $params);
    }

    /**
     * @Then /^I should not see canonical link$/
     */
    public function iShouldNotSeeCanonicalLinkWithQuery()
    {
        $this->getPage('GenericPage')->checkCanonical();
    }

    /**
     * @Given /^I should not see pagination metas$/
     */
    public function iShouldNotSeePaginationMetas()
    {
        $this->getPage('GenericPage')->checkPaginationPrev();
        $this->getPage('GenericPage')->checkPaginationNext();
    }

    /**
     * @Given /^I should see prev page meta with link "(?P<path>[^"]*)"$/
     * @Given /^I should see prev page meta with link "(?P<path>[^"]*)" and page (?P<page>\d+)$/
     */
    public function iShouldSeePrevPageMetaWithLinkAndPage($path, $page)
    {
        if ($page) {
            $params = array(
                'p' => $page
            );
        } else {
            $params = null;
        }

        $this->getPage('GenericPage')->checkPaginationPrev($path, $params);
    }

    /**
     * @Given /^I should see next page meta with link "(?P<path>[^"]*)"$/
     * @Given /^I should see next page meta with link "(?P<path>[^"]*)" and page (?P<page>\d+)$/
     */
    public function iShouldSeeNextPageMetaWithLinkAndPage($path, $page)
    {
        if ($page) {
            $params = array(
                'p' => $page
            );
        } else {
            $params = null;
        }

        $this->getPage('GenericPage')->checkPaginationNext($path, $params);
    }

    /**
     * @Given /^I should not see prev page meta$/
     */
    public function iShouldNotSeePrevPageMeta()
    {
        $this->getPage('GenericPage')->checkPaginationPrev();
    }

    /**
     * @Given /^I should not see next page meta$/
     */
    public function iShouldNotSeeNextPageMeta()
    {
        $this->getPage('GenericPage')->checkPaginationNext();
    }


    /**
     * @Then /^I should robots meta "(?P<metaOne>[^"]*)"$/
     * @Then /^I should robots metas "(?P<metaOne>[^"]*)" and "(?P<metaTwo>[^"]*)"$/
     */
    public function iShouldRobotsMeta($metaOne, $metaTwo = null)
    {
        $metas = array($metaOne);

        if ($metaTwo) {
            $metas[] = $metaTwo;
        }

        $this->getPage('GenericPage')->checkRobots($metas);
    }
}
