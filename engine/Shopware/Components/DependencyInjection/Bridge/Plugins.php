<?php

namespace Shopware\Components\DependencyInjection\Bridge;


class Plugins
{
    /**
     * @var \Zend_Cache_Core
     */
    protected $cache;

    /**
     * @var \Enlight_Plugin_PluginManager
     */
    protected $pluginManager;

    /**
     * @var \Enlight_Event_EventManager
     */
    protected $eventManager;

    /**
     * @var \Enlight_Loader
     */
    protected $loader;

    /**
     * @var
     */
    protected $pluginDir;

    /**
     * @var array
     */
    protected $pluginOptions;

    /**
     * Class constructor which expects all dependencies of this
     * component.
     *
     * @param                               $pluginDir
     * @param \Zend_Cache_Core              $cache
     * @param \Enlight_Event_EventManager   $eventManager
     * @param \Enlight_Plugin_PluginManager $pluginManager
     * @param \Enlight_Loader               $loader
     * @param array                         $pluginOptions
     */
    public function __construct(
        $pluginDir,
        \Zend_Cache_Core $cache,
        \Enlight_Event_EventManager $eventManager,
        \Enlight_Plugin_PluginManager $pluginManager,
        \Enlight_Loader $loader,
        array $pluginOptions)
    {
        $this->pluginDir = $pluginDir;
        $this->cache = $cache;
        $this->eventManager = $eventManager;
        $this->loader = $loader;
        $this->pluginManager = $pluginManager;
        $this->pluginOptions = $pluginOptions;
    }

    /**
     * This function is called to initial the plugin
     * manager within the shopware application.
     * The plugin manager can be access over the
     * resource loader getService('plugins') function.
     *
     * @return \Enlight_Plugin_PluginManager
     */
    public function factory()
    {
        $config = $this->pluginOptions;
        if (!isset($config['cache'])) {
            $config['cache'] = $this->cache;
        }
        if (!isset($config['namespaces'])) {
            $config['namespaces'] = array('Core', 'Frontend', 'Backend');
        }

        foreach ($config['namespaces'] as $namespace) {
            $namespace = new \Shopware_Components_Plugin_Namespace($namespace);
            $this->pluginManager->registerNamespace($namespace);
            $this->eventManager->registerSubscriber($namespace->Subscriber());
        }

        foreach (array('Local', 'Community', 'Default', 'Commercial') as $dir) {
            $this->loader->registerNamespace(
                'Shopware_Plugins',
                $this->pluginDir . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR
            );
        }
        return $this->pluginManager;
    }
}
