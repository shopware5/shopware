<?php

namespace Shopware\Components\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @see Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\AddConsoleCommandPass
 */
class AddConsoleCommandPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $commandServices = $container->findTaggedServiceIds('console.command');
        $serviceIds = [];

        foreach ($commandServices as $id => $tags) {
            $definition = $container->getDefinition($id);

            if ($definition->isAbstract()) {
                throw new \InvalidArgumentException(sprintf('The service "%s" tagged "console.command" must not be abstract.', $id));
            }

            $class = $container->getParameterBag()->resolveValue($definition->getClass());
            if (!is_subclass_of($class, 'Symfony\\Component\\Console\\Command\\Command')) {
                throw new \InvalidArgumentException(sprintf('The service "%s" tagged "console.command" must be a subclass of "Symfony\\Component\\Console\\Command\\Command".', $id));
            }

            $container->setAlias($serviceId = 'console.command.' . strtolower(str_replace('\\', '_', $class)), $id);
            $serviceIds[] = $serviceId;
        }

        $container->setParameter('console.command.ids', $serviceIds);
    }
}