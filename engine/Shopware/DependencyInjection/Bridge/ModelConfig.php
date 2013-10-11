<?php

namespace Shopware\DependencyInjection\Bridge;

use Shopware\Components\Model\Configuration;

class ModelConfig
{
    /**
     * @var array
     */
    protected $option;

    /**
     * Current instance of the application cache layer.
     *
     * @var \Zend_Cache_Core
     */
    protected $cache;

    /**
     * Instance of the application hook manager.
     * Used to make the doctrine repositories hookable.
     *
     * @var \Enlight_Hook_HookManager
     */
    protected $hookManager;

    public function __construct($option, \Zend_Cache_Core $cache, \Enlight_Hook_HookManager $hookManager)
    {
        $this->option = $option;
        $this->cache = $cache;
        $this->hookManager = $hookManager;
    }

    public function factory()
    {
        $config = new Configuration(
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
