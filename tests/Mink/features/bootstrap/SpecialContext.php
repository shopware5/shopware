<?php

use Page\Emotion\Homepage;
use Element\MultipleElement;
use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\TableNode;

require_once 'SubContext.php';

class SpecialContext extends SubContext
{
    /**
     * @Given /^the articles from "(?P<name>[^"]*)" have tax id (?P<num>\d+)$/
     */
    public function theArticlesFromHaveTaxId($supplier, $taxId)
    {
        $taxId = intval($taxId);

        $sql = sprintf(
            'UPDATE s_articles SET taxID = %d WHERE supplierID =
                (SELECT id FROM s_articles_supplier WHERE name = "%s")',
            $taxId,
            $supplier
        );
        $this->getContainer()->get('db')->exec($sql);
    }

    /**
     * @Given /^I am on the page "(?P<page>[^"]*)"$/
     * @Given /^I go to the page "(?P<page>[^"]*)"$/
     */
    public function iAmOnThePage($page)
    {
        $this->getPage($page)->open();
    }

    /**
     * @Then /^I should be on the page "(?P<page>[^"]*)"$/
     */
    public function iShouldBeOnThePage($page)
    {
        $this->getPage($page)->verifyPage();
    }

    /**
     * @Then /^I should see (?P<quantity>\d+) element of type "(?P<elementClass>[^"]*)"$/
     * @Then /^I should see (?P<quantity>\d+) elements of type "(?P<elementClass>[^"]*)"$/
     */
    public function iShouldSeeElementsOfType($count, $elementClass)
    {
        /** @var Homepage $page */
        $page = $this->getPage('Homepage');

        /** @var MultipleElement $elements */
        $elements = $this->getElement($elementClass);
        $elements->setParent($page);

        $page->assertElementCount($elements, intval($count));
    }

    /**
     * @Then /^the page "(?P<pageClass>[^"]*)" should have the content:$/
     */
    public function thePageShouldHaveTheContent($pageClass, TableNode $content)
    {
        $page = $this->getPage($pageClass);
        $this->getPage('Homepage')->assertElementContent($page, $content->getHash());
    }

    /**
     * @Given /^the element "(?P<elementClass>[^"]*)" should have the content:$/
     */
    public function theElementShouldHaveTheContent($elementClass, TableNode $content)
    {
        $this->theElementOnPositionShouldHaveTheContent($elementClass, 1, $content);
    }

    /**
     * @Given /^the element "(?P<elementClass>[^"]*)" on position (?P<position>\d+) should have the content:$/
     */
    public function theElementOnPositionShouldHaveTheContent($elementClass, $position, TableNode $content)
    {
        /** @var Homepage $page */
        $page = $this->getPage('Homepage');

        $element = $this->getElement($elementClass);

        if ($element instanceof MultipleElement) {
            /** @var MultipleElement $element */
            $element->setParent($page);

            $element = $element->setInstance($position);
        }

        $page->assertElementContent($element, $content->getHash());
    }

    /**
     * @When /^I follow the link "(?P<linkName>[^"]*)" of the page "(?P<pageClass>[^"]*)"$/
     */
    public function iFollowTheLinkOfThePage($linkName, $pageClass)
    {
        $page = $this->getPage($pageClass);
        Helper::clickNamedLink($page, $linkName);
    }

    /**
     * @When /^I follow the link of the element "(?P<elementClass>[^"]*)"$/
     * @When /^I follow the link of the element "(?P<elementClass>[^"]*)" on position (?P<position>\d+)$/
     */
    public function iFollowTheLinkOfTheElement($elementClass, $position = 1)
    {
        $this->iFollowTheLinkOfTheElementOnPosition(null, $elementClass, $position);
    }

    /**
     * @When /^I follow the link "(?P<linkName>[^"]*)" of the element "(?P<elementClass>[^"]*)"$/
     * @When /^I follow the link "(?P<linkName>[^"]*)" of the element "(?P<elementClass>[^"]*)" on position (?P<position>\d+)$/
     */
    public function iFollowTheLinkOfTheElementOnPosition($linkName, $elementClass, $position = 1)
    {
        /** @var HelperSelectorInterface $element */
        $element = $this->getElement($elementClass);

        if ($element instanceof MultipleElement) {
            /** @var Homepage $page */
            $page = $this->getPage('Homepage');

            /** @var MultipleElement $element */
            $element->setParent($page);

            $element = $element->setInstance($position);
        }

        if(empty($linkName)) {
            $element->click();
            return;
        }

        $language = Helper::getCurrentLanguage($this->getPage('Homepage'));
        $selectors = $element->getNamedSelectors();
        $element->clickLink($selectors[$linkName][$language]);
    }

    /**
     * @Given /^the "(?P<field>[^"]*)" field should contain:$/
     */
    public function theFieldShouldContain($field, \Behat\Gherkin\Node\PyStringNode $string)
    {
        $assert = new \Behat\Mink\WebAssert($this->getSession());
        $assert->fieldValueEquals($field, $string->getRaw());
    }
}
