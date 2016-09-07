<?php

namespace Shopware\Bundle\ESIndexingBundle\DependencyInjection\CompilerPass;

use Shopware\Bundle\ESIndexingBundle\Product\ProductModifierInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class ProductModifierCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('shopware_elastic_search.product_provider')) {
            return;
        }

        $productProviderDefinition = $container->getDefinition('shopware_elastic_search.product_provider');

        $productModifiers = $container->findTaggedServiceIds(ProductModifierInterface::SERVICE_TAG_ID);

        foreach (array_keys($productModifiers) as $productModifierId) {
            $productProviderDefinition->addMethodCall(
                'addProductModifier',
                [new Reference($productModifierId)]
            );
        }
    }
}
