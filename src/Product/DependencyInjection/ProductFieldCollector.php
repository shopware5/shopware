<?php declare(strict_types=1);

namespace Shopware\Product\DependencyInjection;

use Shopware\Framework\DependencyInjection\TagReplaceTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ProductFieldCollector implements CompilerPassInterface
{
    use TagReplaceTrait;

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $taggedServices = $this->findAndSortTaggedServices(
            'shopware.product.writer_field',
            $container
        );

        foreach($taggedServices as $service) {
            $container
                ->findDefinition('shopware.product.field_collection')
                ->addArgument($service);
        }
    }
}