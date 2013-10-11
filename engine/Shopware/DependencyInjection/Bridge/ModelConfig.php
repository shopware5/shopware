<?php

namespace Shopware\DependencyInjection\Bridge;

class ModelConfig
{
    protected $option;
    protected $cache;
    protected $hookManager;

    public function __construct($option, \Zend_Cache_Core $cache, \Enlight_Hook_HookManager $hookManager)
    {
        $this->option = $option;
        $this->cache = $cache;
        $this->hookManager = $hookManager;
    }

    public function factory()
    {
        $config = new \Shopware\Components\Model\Configuration(
            $this->option
        );

        if ($config->getMetadataCacheImpl() === null) {
            $cacheResource = $this->cache;
            $config->setCacheResource($cacheResource);
        }

        $config->setHookManager($this->hookManager);

        return $config;
    }
}
