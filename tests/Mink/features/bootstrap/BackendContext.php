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

        $this->spin(function ($context) use ($page) {
            return $page->verifyLogin();
        });

        $this->spin(function ($context) use ($page) {
            return $page->login('demo', 'demo');
        });

        $this->spin(function ($context) use ($page) {
            return $page->verifyIsLoggedIn();
        });
    }

    /**
     * @When /^I open the module "([^"]*)"$/
     */
    public function iOpenTheModule($moduleName)
    {
        $this->spin(function ($context) use ($moduleName) {
            $context->getPage('Backend')->openModule($moduleName);
            return true;
        });
    }

    /**
     * @Then /^The module should open a window$/
     */
    public function theModuleShouldOpenAWindow()
    {
        $page = $this->getPage('Backend');

        $this->spin(function ($context) use ($page) {
            $context->getPage('Backend')->verifyModule();
            return true;
        });
    }

    /**
     * Wait for an command to be true and abort after $maxRetries iterations
     * Sleeps for 1 second after each try
     *
     * @param callable $lambda
     * @param int $maxRetries
     * @return bool
     * @throws \Exception
     */
    public function spin($lambda, $maxRetries = 10)
    {
        for ($i = 0; $i < $maxRetries; $i++) {
            try {
                if ($lambda($this)) {
                    return true;
                }
            } catch (\Exception $e) {
            }

            sleep(1);
        }

        if (!empty($e) && $e instanceof \Exception) {
            throw $e;
        } else {
            $backtrace = debug_backtrace();
            throw new \Exception("Timeout thrown by " . $backtrace[1]['class'] . "::" . $backtrace[1]['function'] . "()");
        }
    }
}
