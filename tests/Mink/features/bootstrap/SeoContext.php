<?php

namespace Shopware\Tests\Mink;

use Shopware\Tests\Mink\Page\Emotion\GenericPage;

class SeoContext extends SubContext
{
    /**
     * @Then /^I should not see pagination metas$/
     */
    public function iShouldNotSeePaginationMetas()
    {
        /** @var GenericPage $page */
        $page = $this->getPage('GenericPage');
        $page->checkLink('prev');
        $page->checkLink('next');
    }

    /**
     * @Then /^I should see (canonical) link "(?P<path>[^"]*)"$/
     * @Then /^I should see (canonical) link "(?P<path>[^"]*)" and page (?P<page>\d+)$/
     * @Then /^I should see (prev|next) page meta with link "(?P<path>[^"]*)"$/
     * @Then /^I should see (prev|next) page meta with link "(?P<path>[^"]*)" and page (?P<page>\d+)$/
     */
    public function iShouldSeePageMetaWithLinkAndPage($locator, $path, $page = null)
    {
        if ($page) {
            $params = [
                'p' => $page
            ];
        } else {
            $params = null;
        }

        $this->getPage('GenericPage')->checkLink($locator, $path, $params);
    }

    /**
     * @Then /^I should not see (canonical) link$/
     * @Then /^I should not see (prev|next) page meta$/
     */
    public function iShouldNotSeePageMeta($locator)
    {
        $this->getPage('GenericPage')->checkLink($locator);
    }


    /**
     * @Then /^I should robots meta "(?P<metaOne>[^"]*)"$/
     * @Then /^I should robots metas "(?P<metaOne>[^"]*)" and "(?P<metaTwo>[^"]*)"$/
     */
    public function iShouldRobotsMeta($metaOne, $metaTwo = null)
    {
        $metas = [$metaOne];

        if ($metaTwo) {
            $metas[] = $metaTwo;
        }

        $this->getPage('GenericPage')->checkRobots($metas);
    }
}
