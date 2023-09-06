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

namespace Shopware\Tests\Mink\Tests\Frontend\Listing\bootstrap;

use Shopware\Tests\Mink\Page\Frontend\Generic\GenericPage;
use Shopware\Tests\Mink\Tests\General\Helpers\SubContext;

class SeoContext extends SubContext
{
    /**
     * @Then /^I should not see pagination metas$/
     */
    public function iShouldNotSeePaginationMetas(): void
    {
        $page = $this->getPage(GenericPage::class);
        $page->checkLink('prev');
        $page->checkLink('next');
    }

    /**
     * @Then /^I should see (canonical) link "(?P<path>[^"]*)"$/
     * @Then /^I should see (canonical) link "(?P<path>[^"]*)" and page (?P<page>\d+)$/
     * @Then /^I should see (prev|next) page meta with link "(?P<path>[^"]*)"$/
     * @Then /^I should see (prev|next) page meta with link "(?P<path>[^"]*)" and page (?P<page>\d+)$/
     */
    public function iShouldSeePageMetaWithLinkAndPage(string $locator, string $path, ?string $page = null): void
    {
        if ($page) {
            $params = [
                'p' => $page,
            ];
        } else {
            $params = [];
        }

        $this->getPage(GenericPage::class)->checkLink($locator, $path, $params);
    }

    /**
     * @Then /^I should see (canonical) link$/
     * @Then /^I should see (prev|next) page meta$/
     */
    public function iShouldSeePageMeta(string $locator): void
    {
        $this->getPage(GenericPage::class)->checkLink($locator);
    }

    /**
     * @Then /^I should not see (canonical) link$/
     * @Then /^I should not see (prev|next) page meta$/
     */
    public function iShouldNotSeePageMeta(string $locator): void
    {
        $this->getPage(GenericPage::class)->checkLink($locator);
    }

    /**
     * @Then /^I should see robots meta "(?P<metaOne>[^"]*)"$/
     * @Then /^I should see robots metas "(?P<metaOne>[^"]*)" and "(?P<metaTwo>[^"]*)"$/
     */
    public function iShouldRobotsMeta(string $metaOne, ?string $metaTwo = null): void
    {
        $metas = [$metaOne];

        if ($metaTwo) {
            $metas[] = $metaTwo;
        }

        $this->getPage(GenericPage::class)->checkRobots($metas);
    }
}
