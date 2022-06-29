<?php
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

namespace Shopware\Tests\Mink\Tests\Frontend\Sitemap\bootstrap;

use Behat\Gherkin\Node\TableNode;
use Shopware\Tests\Mink\Page\Frontend\Sitemap\Elements\SitemapGroup;
use Shopware\Tests\Mink\Page\Sitemap;
use Shopware\Tests\Mink\Tests\General\Helpers\SubContext;

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
     * @Given /^I am on the sitemap_index\.xml$/
     */
    public function iAmOnTheSitemapIndexXml()
    {
        $this->getPage('SitemapIndexXml')->open();
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

        /** @var Sitemap $page */
        $page = $this->getPage('Sitemap');

        /** @var SitemapGroup $groups */
        $groups = $this->getMultipleElement($page, 'SitemapGroup');

        $sitemapGroup = $name;

        /** @var SitemapGroup $group */
        foreach ($groups as $group) {
            if ($group->getTitle() === $name) {
                $sitemapGroup = $group;
                break;
            }
        }

        $page->checkGroup($sitemapGroup, $link, $links);
    }

    /**
     * @Then /^I should see the sitemap files:$/
     */
    public function thereShouldBeTheseLinksInTheXml(TableNode $links)
    {
        $links = $links->getHash();
        $this->getPage('SitemapIndexXml')->checkXml($links);
    }
}
