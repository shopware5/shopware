<?php

class BasicSettingsContext extends SubContext
{
    /**
     * @Given /^basic settings element "(?P<configElementName>[^"]*)" has value (?P<configValue>[^"]*)$/
     *
     * @param $configElementName
     * @param $configValue
     * @throws Zend_Db_Adapter_Exception
     */
    public function basicSettingsElementHasValue($configElementName, $configValue)
    {
        $database = $this->getContainer()->get('db');

        $sql = "SET @configElementId = (SELECT id FROM `s_core_config_elements` WHERE `name`= '" . $configElementName . "' LIMIT 1);";
        $database->exec($sql);

        $sql = 'INSERT INTO `s_core_config_values` (`element_id`, `shop_id`, `value`) VALUES
            (@configElementId, 1, \'' . serialize($configValue) . '\'),
            (@configElementId, 2,  \'' . serialize($configValue) . '\');'
        ;
        $database->exec($sql);

        $this->clearCache();
    }

    /**
     *
     */
    private function clearCache()
    {
        /** @var \Shopware\Components\CacheManager $cacheManager */
        $cacheManager = $this->getContainer()->get('shopware.cache_manager');

        $cacheManager->clearHttpCache();
        $cacheManager->clearTemplateCache();
        $cacheManager->clearConfigCache();
        $cacheManager->clearSearchCache();
        $cacheManager->clearProxyCache();
    }
}
