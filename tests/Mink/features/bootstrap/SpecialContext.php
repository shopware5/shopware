<?php

use Behat\Behat\Context\Step;

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
}