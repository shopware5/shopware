<?php

namespace Shopware\Components\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class EventSubscriberCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('model_event_manager')) {
            return;
        }

        $definition = $container->getDefinition(
            'model_event_manager'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'model_event_manager.event_subscriber'
        );

        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall(
                'addEventSubscriber',
                array(new Reference($id))
            );
        }
    }
}
