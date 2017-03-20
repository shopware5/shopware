<?php

class PluginCollection
{
    /**
     * @var \Shopware\Framework\Component\Plugin[]
     */
    private $plugins;

    /**
     * @param \Shopware\Framework\Component\Plugin[] $plugins
     */
    public function __construct(array $plugins = [])
    {
        $this->plugins = $plugins;
    }

    public function add(\Shopware\Framework\Component\Plugin $plugin)
    {
        $class = get_class($plugin);

        if ($this->has($class)) {
            return;
        }

        $this->plugins[$class] = $plugin;
    }

    /**
     * @param \Shopware\Framework\Component\Plugin[] $plugins
     */
    public function addList(array $plugins)
    {
        array_map([$this, 'add'], $plugins);
    }

    public function has($name)
    {
        return array_key_exists($name, $this->plugins);
    }

    /**
     * @return \Shopware\Framework\Component\Plugin[]
     */
    public function getPlugins(): array
    {
        return $this->plugins;
    }

    public function filter(Closure $closure)
    {
        return new static(array_filter($this->plugins, $closure));
    }
}