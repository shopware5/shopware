<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Mink\Tests\Backend\Backend\bootstrap;

use Shopware\Tests\Mink\Page\Backend\Backend\Backend;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;
use Shopware\Tests\Mink\Tests\General\Helpers\SubContext;

class BackendContext extends SubContext
{
    /**
     * @Given /^I am logged in to the backend as an admin user$/
     */
    public function iAmLoggedInToTheBackendAsAnAdminUser(): void
    {
        $page = $this->getPage(Backend::class);
        $page->open();

        // See if we already are logged in
        if ($this->waitIfThereIsText('Marketing')) {
            return;
        }

        $this->waitForText('Shopware Backend Login', 10);

        $page->login('demo', 'demo');
        $this->waitForText('Marketing');
    }

    /**
     * @When /^I open the module "([^"]*)"$/
     */
    public function iOpenTheModule(string $moduleName): void
    {
        Helper::spin(function (BackendContext $context) use ($moduleName) {
            $backendPage = $context->getPage(Backend::class);
            \assert($backendPage instanceof Backend);
            $backendPage->openModule($moduleName);

            return true;
        }, Helper::DEFAULT_WAIT_TIME, $this);
    }

    /**
     * @Then /^The module should open a window$/
     */
    public function theModuleShouldOpenAWindow(): void
    {
        $this->getPage(Backend::class);

        Helper::spin(function (BackendContext $context) {
            $backendPage = $context->getPage(Backend::class);
            \assert($backendPage instanceof Backend);
            $backendPage->verifyModule();

            return true;
        }, Helper::DEFAULT_WAIT_TIME, $this);
    }

    /**
     * @Then I should see a dropdown appear
     */
    public function iShouldSeeADropdownAppear(): void
    {
        Helper::spin(function (BackendContext $context) {
            return $context->getPage(Backend::class)->find('css', '.x-boundlist-item') !== null;
        }, Helper::DEFAULT_WAIT_TIME, $this);
    }

    /**
     * @Then I should see a success message
     */
    public function iShouldSeeASuccessMessage(): void
    {
        $this->waitIfThereIsText('Erfolgreich');
    }

    /**
     * @Then There should be a window with the alias :alias
     */
    public function thereShouldBeAWindowWithTheAlias(string $alias): bool
    {
        $script = <<<'JS'
return Shopware.app.Application.getActiveWindows().filter(function(activeWindow) {
    return activeWindow.alias.some(function (alias) {
        return alias.endsWith('%s');
    });
}).length > 0;
JS;

        return (bool) $this->getDriver()->evaluateScript(sprintf($script, $alias));
    }

    /**
     * Checks via a string exists
     */
    protected function checkIfThereIsText(string $text, SubContext $context): bool
    {
        $result = $context->getSession()->getPage()->findAll('xpath', sprintf("//*[contains(., '%s')]", $text));

        return !empty($result);
    }

    /**
     * Checks via spin function if a string exists, with sleep at the beginning (default 2)
     */
    protected function waitForText(string $text, int $sleep = 2): void
    {
        sleep($sleep);
        Helper::spin(function (BackendContext $context) use ($text) {
            return $this->checkIfThereIsText($text, $context);
        }, Helper::DEFAULT_WAIT_TIME, $this);
    }

    /**
     * Checks via spin function if a string exists, with sleep at the beginning (default 2)
     */
    protected function waitIfThereIsText(string $text, int $wait = 5): bool
    {
        return Helper::spinWithNoException(function (BackendContext $context) use ($text) {
            return $this->checkIfThereIsText($text, $context);
        }, $wait, $this);
    }
}
