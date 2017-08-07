<?php

namespace Shopware\Product;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Shopware\Product\DependencyInjection\ProductFieldCollector;

class Product extends Bundle
{
    protected $name = 'Product';

    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/DependencyInjection/'));
        $loader->load('services.xml');

        $container->addCompilerPass(new ProductFieldCollector());
    }
}