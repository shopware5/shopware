<?php

namespace Shopware\Components\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class EventListenerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('events')) {
            return;
        }

        $definition = $container->getDefinition(
            'events'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'shopware.event_manager.listener'
        );

        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {

                $callback = array(new Reference($id), $attributes["method"]);

                $definition->addMethodCall(
                    'registerSimpleListener',
                    array($attributes["event"], $callback)
                );
            }
        }
    }
}
