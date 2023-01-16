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

namespace Shopware\Tests\Mink\Tests\General\Helpers;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\DriverException;
use Behat\Mink\Exception\ResponseTextException;
use Behat\Mink\WebAssert;
use Exception;
use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;
use Shopware\Tests\Mink\Page\Frontend\Account\Account;
use Shopware\Tests\Mink\Page\Frontend\Detail\Detail;
use Shopware\Tests\Mink\Page\Frontend\Form\Form;
use Shopware\Tests\Mink\Page\Frontend\Homepage\Homepage;
use Shopware\Tests\Mink\Page\Frontend\Listing\Listing;
use Shopware\Tests\Mink\Page\Frontend\Newsletter\Newsletter;
use Shopware\Tests\Mink\Page\Helper\Elements\MultipleElement;

class SpecialContext extends SubContext
{
    /**
     * @Given /^the articles from "(?P<name>[^"]*)" have tax id (?P<num>\d+)$/
     */
    public function theArticlesFromHaveTaxId(string $supplier, int $taxId): void
    {
        $sql = sprintf(
            'UPDATE s_articles SET taxID = %d WHERE supplierID =
                (SELECT id FROM s_articles_supplier WHERE name = "%s")',
            $taxId,
            $supplier
        );

        $this->getService('db')->exec($sql);
    }

    /**
     * @Given /^I am on the (page "[^"]*")$/
     *
     * @When /^I go to the (page "[^"]*")$/
     */
    public function iAmOnThePage(Page $page): void
    {
        $page->open();
    }

    /**
     * @Given /^I ignore browser validation$/
     */
    public function IignoreFormValidations(): void
    {
        $this->getSession()->executeScript('for(var f=document.forms,i=f.length;i--;)f[i].setAttribute("novalidate",i)');
    }

    /**
     * @Then /^I should be on the (page "[^"]*")$/
     *
     * @param Account|Detail|Newsletter|Listing|Form $page
     */
    public function iShouldBeOnThePage(Page $page): void
    {
        $page->verifyPage();
    }

    /**
     * @Then /^I should see (?P<quantity>\d+) element of type "(?P<elementClass>[^"]*)"( eventually)?$/
     * @Then /^I should see (?P<quantity>\d+) elements of type "(?P<elementClass>[^"]*)"( eventually)?$/
     *
     * @param class-string<MultipleElement> $elementClass
     */
    public function iShouldSeeElementsOfType(int $count, string $elementClass, ?string $mode = null): void
    {
        $page = $this->getPage(Homepage::class);

        if ($mode === null) {
            $elements = $this->getMultipleElement($page, $elementClass);
            Helper::assertElementCount($elements, $count);

            return;
        }

        $this->spin(function (SpecialContext $context) use ($page, $count, $elementClass) {
            try {
                $elements = $context->getMultipleElement($page, $elementClass);
                Helper::assertElementCount($elements, $count);

                return true;
            } catch (ResponseTextException $e) {
                // NOOP
            }

            return false;
        });
    }

    /**
     * @When /^I follow the link "(?P<linkName>[^"]*)" of the (page "[^"]*")$/
     *
     * @param Page&HelperSelectorInterface $page
     */
    public function iFollowTheLinkOfThePage(string $linkName, Page $page): void
    {
        Helper::clickNamedLink($page, $linkName);
    }

    /**
     * @When /^I follow the link of the element "(?P<elementClass>[^"]*)"$/
     * @When /^I follow the link of the element "(?P<elementClass>[^"]*)" on position (?P<position>\d+)$/
     *
     * @param class-string<Element> $elementClass
     */
    public function iFollowTheLinkOfTheElement(string $elementClass, int $position = 1): void
    {
        $this->iFollowTheLinkOfTheElementOnPosition('', $elementClass, $position);
    }

    /**
     * @When /^I follow the link "(?P<linkName>[^"]*)" of the element "(?P<elementClass>[^"]*)"$/
     * @When /^I follow the link "(?P<linkName>[^"]*)" of the element "(?P<elementClass>[^"]*)" on position (?P<position>\d+)$/
     *
     * @param class-string<Element> $elementClass
     */
    public function iFollowTheLinkOfTheElementOnPosition(string $linkName, string $elementClass, int $position = 1): void
    {
        $element = $this->getElement($elementClass);

        if ($element instanceof MultipleElement) {
            $page = $this->getPage(Homepage::class);

            $element->setParent($page);

            $element = $element->setInstance($position);
        }

        if (empty($linkName)) {
            $this->clickElementWhenClickable($element);

            return;
        }

        if (!$element instanceof HelperSelectorInterface) {
            Helper::throwException('Element does not implement necessary interface "HelperSelectorInterface"');
        }

        $this->clickNamedLinkWhenClickable($element, $linkName);
    }

    /**
     * @Given /^the "(?P<field>[^"]*)" field should contain:$/
     */
    public function theFieldShouldContain(string $field, PyStringNode $string): void
    {
        $assert = new WebAssert($this->getSession());
        $assert->fieldValueEquals($field, $string->getRaw());
    }

    /**
     * @When /^I press the button "([^"]*)" of the element "([^"]*)" on position (\d+)$/
     *
     * @param class-string<Element> $elementClass
     */
    public function iPressTheButtonOfTheElementOnPosition(string $linkName, string $elementClass, int $position = 1): void
    {
        $element = $this->getElement($elementClass);

        if ($element instanceof MultipleElement) {
            $page = $this->getPage(Homepage::class);

            $element->setParent($page);

            $element = $element->setInstance($position);
        }

        if (empty($linkName)) {
            $element->press();

            return;
        }

        if (!$element instanceof HelperSelectorInterface) {
            Helper::throwException('Element does not implement necessary interface "HelperSelectorInterface"');
        }

        $this->clickNamedButtonWhenClickable($element, $linkName);
    }

    /**
     * Based on Behat's own example
     *
     * @see http://docs.behat.org/en/v2.5/cookbook/using_spin_functions.html#adding-a-timeout
     *
     * @throws Exception
     */
    protected function spin(callable $lambda, int $wait = 60): void
    {
        if (!$this->spinWithNoException($lambda, $wait)) {
            throw new Exception(sprintf('Spin function timed out after %s seconds', $wait));
        }
    }

    /**
     * Based on Behat's own example
     *
     * @see http://docs.behat.org/en/v2.5/cookbook/using_spin_functions.html#adding-a-timeout
     *
     * @return bool
     */
    protected function spinWithNoException(callable $lambda, int $wait = 60)
    {
        $time = time();
        $stopTime = $time + $wait;
        while (time() < $stopTime) {
            try {
                if ($lambda($this)) {
                    return true;
                }
            } catch (Exception $e) {
                // do nothing
            }

            usleep(250000);
        }

        return false;
    }

    /**
     * Tries to click on a named link until the click is successful or the timeout is reached
     *
     * @param (Page|Element)&HelperSelectorInterface $element
     */
    protected function clickNamedLinkWhenClickable($element, string $linkName, int $timeout = 60): void
    {
        $this->spin(function (SpecialContext $context) use ($element, $linkName) {
            try {
                Helper::clickNamedLink($element, $linkName);

                return true;
            } catch (DriverException $e) {
                // NOOP
            }

            return false;
        }, $timeout);
    }

    /**
     * Tries to click on a named button until the click is successfull or the timeout is reached
     *
     * @param (Page|Element)&HelperSelectorInterface $element
     */
    protected function clickNamedButtonWhenClickable($element, string $key, int $timeout = 60): void
    {
        $this->spin(function (SpecialContext $context) use ($element, $key) {
            try {
                Helper::pressNamedButton($element, $key);

                return true;
            } catch (DriverException $e) {
                // NOOP
            }

            return false;
        }, $timeout);
    }

    /**
     * Tries to click on an element until the click is successfull or the timeout is reached
     *
     * @param int $timeout Defaults to 60 seconds
     */
    protected function clickElementWhenClickable(NodeElement $element, int $timeout = 60): void
    {
        $this->spin(function (SpecialContext $context) use ($element) {
            try {
                $element->click();

                return true;
            } catch (DriverException $e) {
                // NOOP
            }

            return false;
        }, $timeout);
    }
}
