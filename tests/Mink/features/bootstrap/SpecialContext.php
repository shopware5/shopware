<?php

use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Context\Step\Then;

require_once 'SubContext.php';

class SpecialContext extends SubContext
{
    /**
     * @Given /^the "(?P<name>[^"]*)" plugin is enabled$/
     */
    public function thePluginIsEnabled($name)
    {
        /** @var \Shopware\Components\Plugin\Manager $pluginManager */
        $pluginManager = $this->getContainer()->get('shopware.plugin_Manager');

        // hack to prevent behat error handler kicking in.
        $oldErrorReporting = error_reporting(0);
        $pluginManager->refreshPluginList();
        error_reporting($oldErrorReporting);

        $plugin = $pluginManager->getPluginByName($name);
        $pluginManager->installPlugin($plugin);
        $pluginManager->activatePlugin($plugin);
    }

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
        /** @var \Emotion\Homepage $page */
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
        /** @var \Emotion\Homepage $page */
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
     * @Given /^I submit the form "(?P<formName>[^"]*)" on page "(?P<pageClass>[^"]*)" with:$/
     */
    public function iSubmitTheFormOnPageWith($formLocatorName, $pageClass, TableNode $values)
    {
        $page = $this->getPage($pageClass);
        $this->getPage('Homepage')->submitForm($formLocatorName, $page, $values->getHash());
    }

    /**
     * @When /^I follow the link "(?P<linkName>[^"]*)" of the page "(?P<pageClass>[^"]*)"$/
     */
    public function iFollowTheLinkOfThePage($linkName, $pageClass)
    {
        $page = $this->getPage($pageClass);
        $locators = array('contentBlock');
        $elements = Helper::findElements($page, $locators, $this->getPage('Homepage')->cssLocator);

        $language = Helper::getCurrentLanguage($page);
        $elements['contentBlock']->clickLink($page->namedSelectors[$linkName][$language]);
    }

    /**
     * @When /^I follow the link "(?P<linkName>[^"]*)" of the element "(?P<elementClass>[^"]*)"$/
     * @When /^I follow the link "(?P<linkName>[^"]*)" of the element "(?P<elementClass>[^"]*)" on position (?P<position>\d+)$/
     */
    public function iFollowTheLinkOfTheElement($linkName, $elementClass, $position = 1)
    {
        $element = $this->getElement($elementClass);

        if ($element instanceof MultipleElement) {
            /** @var \Emotion\Homepage $page */
            $page = $this->getPage('Homepage');

            /** @var MultipleElement $element */
            $element->setParent($page);

            $element = $element->setInstance($position);
        }

        $language = Helper::getCurrentLanguage($this->getPage('Homepage'));
        $element->clickLink($element->namedSelectors[$linkName][$language]);
    }

    /**
     * @Given /^only on "(?P<template>[^"]*)" template "(?P<step>[^"]*)"$/
     * @Given /^only on "(?P<template>[^"]*)" template "(?P<step>[^"]*)" :$/
     */
    public function onlyOn($template, $method, TableNode $dataTable = null)
    {
        $page = $this->getPage('Homepage');
        $class = get_class($page);

        if (strpos($class, $template . '\\') === false) {
            return;
        }

        $method = str_replace('\'', '"', $method);

        if (empty($dataTable)) {
            return new Then($method);
        }

        return new Then($method, $dataTable);
    }
}
