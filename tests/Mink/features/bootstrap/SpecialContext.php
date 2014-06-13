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
     * @Given /^the password of user "(?P<email>[^"]*)" is "(?P<password>[^"]*)"$/
     */
    public function thePasswordOfUserIs($email, $password)
    {
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        $password = md5($password);

        if (empty($email)) {
            Helper::throwException(array('The email-address is invalid!'));
        }

        $sql = sprintf(
            'UPDATE s_user SET password = "%s", encoder = "md5" WHERE email = "%s"',
            $password,
            $email
        );

        $this->getContainer()->get('db')->exec($sql);
    }
}