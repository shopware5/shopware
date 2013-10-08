<?php

namespace Shopware\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * Loads all shopware services and passes shopware options
 * to container
 *
 * @package Shopware\DependencyInjection
 */
class ShopwareExtension extends Extension
{
    private $shopwareOptions;

    public function __construct(array $shopwareOptions = array())
    {
        $this->shopwareOptions = $shopwareOptions;
    }

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        // pass all original shopware options as container parameter
        foreach ($this->shopwareOptions as $key => $value) {
            $container->setParameter('shopware.' . $key, $value);
        }

        //$configuration = new Configuration();
        //$config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Configs'));
        $loader->load('services.xml');
        $loader->load('twig.xml');
    }
}
