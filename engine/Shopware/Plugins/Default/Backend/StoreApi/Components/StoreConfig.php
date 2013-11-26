<?php
class Shopware_Components_StoreConfig extends Enlight_Class
{
    protected $version;
    protected $language;

    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }
}
