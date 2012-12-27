<?php
class Shopware_Components_StoreConfig extends Enlight_Class
{
    public function setVersion($version)
    {
        Shopware()->BackendSession()->storeApiConfigVersion = $version;
        return $this;
    }

    public function setLanguage($language)
    {
        Shopware()->BackendSession()->storeApiConfigLanguage = $language;
        return $this;
    }
}