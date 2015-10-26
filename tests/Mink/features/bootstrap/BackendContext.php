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

use Behat\Behat\Tester\Exception\PendingException;

class BackendContext extends SubContext
{
    /**
     * @Given /^I am logged in to the backend as an admin user$/
     */
    public function iAmLoggedInToTheBackendAsAnAdminUser()
    {
        $page = $this->getPage('Backend');
        $page->open();

        $page->login('demo', 'demo');
    }

    /**
     * @When /^I open the module "([^"]*)"$/
     */
    public function iOpenTheModule($moduleName)
    {
        $this->getPage('Backend')->openModule($moduleName);
    }

    /**
     * @Then /^The module should open a window$/
     */
    public function theModuleShouldOpenAWindow()
    {
        $this->getPage('Backend')->verifyModule();
    }
}
