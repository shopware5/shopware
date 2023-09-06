<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Mink\Tests\Frontend\Sitemap\bootstrap;

use Behat\Gherkin\Node\TableNode;
use Shopware\Tests\Mink\Page\Frontend\Sitemap\Elements\SitemapGroup;
use Shopware\Tests\Mink\Page\Frontend\Sitemap\Sitemap;
use Shopware\Tests\Mink\Page\Frontend\Sitemap\SitemapIndexXml;
use Shopware\Tests\Mink\Tests\General\Helpers\SubContext;

class SitemapContext extends SubContext
{
    /**
     * @Given /^I am on the sitemap\.xml$/
     */
    public function iAmOnTheSitemapXml(): void
    {
        $this->getPage(Sitemap::class)->open(['xml' => '.xml']);
    }

    /**
     * @Given /^I am on the sitemap_index\.xml$/
     */
    public function iAmOnTheSitemapIndexXml(): void
    {
        $this->getPage(SitemapIndexXml::class)->open();
    }

    /**
     * @Then /^I should see the group "([^"]*)"$/
     * @Then /^I should see the group "([^"]*)":$/
     */
    public function iShouldSeeTheGroup(string $name, ?TableNode $entries = null): void
    {
        $this->iShouldSeeTheGroupWithLink($name, '', $entries);
    }

    /**
     * @Then /^I should see the group "([^"]*)" with link "([^"]*)"$/
     * @Then /^I should see the group "([^"]*)" with link "([^"]*)":$/
     */
    public function iShouldSeeTheGroupWithLink(string $name, string $link, ?TableNode $entries = null): void
    {
        $links = [];

        if ($entries) {
            $links = $entries->getHash();
        }

        $page = $this->getPage(Sitemap::class);

        $groups = $this->getMultipleElement($page, SitemapGroup::class);

        $sitemapGroup = $name;

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
    public function thereShouldBeTheseLinksInTheXml(TableNode $links): void
    {
        $links = $links->getHash();
        $this->getPage(SitemapIndexXml::class)->checkXml($links);
    }
}
