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

namespace Shopware\Tests\Mink;

use Shopware\Tests\Mink\Page\GenericPage;

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
                'p' => $page,
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
