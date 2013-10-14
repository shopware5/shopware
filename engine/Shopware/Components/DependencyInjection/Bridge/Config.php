<?php

namespace Shopware\Components\DependencyInjection\Bridge;

use Enlight_Components_Db_Adapter_Pdo_Mysql;
use Shopware_Components_Config;
use Zend_Cache_Core;

class Config
{
    private $cache;
    private $db;
    private $configOptions;

    public function __construct(
        Zend_Cache_Core $cache,
        $configOptions = array(),
        Enlight_Components_Db_Adapter_Pdo_Mysql $db = null
    ) {
        $this->cache = $cache;
        $this->configOptions = $configOptions;
        $this->db = $db;

    }

    public function factory()
    {
        if (!$this->db) {
            return null;
        }

        $configs = $this->configOptions;

        if (!isset($configs['cache'])) {
            $configs['cache'] = $this->cache;
        }
        $configs['db'] = $this->db;

        return new Shopware_Components_Config($configs);
    }
}
