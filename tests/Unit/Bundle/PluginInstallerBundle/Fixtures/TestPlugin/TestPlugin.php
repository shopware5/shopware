<?php

namespace TestPlugin;

use Shopware\Components\Plugin;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Shopware-Plugin TestPlugin.
 */
class TestPlugin extends Plugin
{

    /**
    * @param ContainerBuilder $container
    */
    public function build(ContainerBuilder $container)
    {
        $container->setParameter('test_plugin.plugin_dir', $this->getPath());
        parent::build($container);
    }

}
