<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Behat\ShopwareExtension\ServiceContainer;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Testwork\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ShopwareExtension implements ExtensionInterface
{
    const KERNEL_ID = 'shopware_extension.kernel';

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'shopware';
    }

    /**
     * @param ExtensionManager $extensionManager
     */
    public function initialize(ExtensionManager $extensionManager)
    {
    }

    /**
     * Setups configuration for the extension.
     *
     * @param ArrayNodeDefinition $builder
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $boolFilter = function ($v) {
            $filtered = filter_var($v, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            return (null === $filtered) ? $v : $filtered;
        };

        $builder
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('kernel')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('bootstrap')
                            ->defaultValue('../../autoload.php')
                        ->end()
                        ->scalarNode('path')
                            ->defaultValue('engine/Shopware/Kernel.php')
                        ->end()
                        ->scalarNode('class')
                            ->defaultValue('Shopware\Kernel')
                        ->end()
                        ->scalarNode('env')
                            ->defaultValue('production')
                        ->end()
                        ->booleanNode('debug')
                            ->beforeNormalization()
                                ->ifString()->then($boolFilter)
                            ->end()
                            ->defaultFalse()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }

    /**
     * Loads extension services into temporary container.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $this->loadContextInitializer($container);
        $this->loadKernel($container, $config['kernel']);
    }

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        // get base path
        $basePath = $container->getParameter('paths.base');

        // find and require bootstrap
        $bootstrapPath = $container->getParameter('shopware_extension.kernel.bootstrap');

        if ($bootstrapPath) {
            if (file_exists($bootstrap = $basePath . '/' . $bootstrapPath)) {
                require_once $bootstrap;
            } elseif (file_exists($bootstrapPath)) {
                require_once $bootstrapPath;
            }
        }

        // find and require kernel
        $kernelPath = $container->getParameter('shopware_extension.kernel.path');
        if (file_exists($kernel = $basePath . '/' . $kernelPath)) {
            $container->getDefinition(self::KERNEL_ID)->setFile($kernel);
        } elseif (file_exists($kernelPath)) {
            $container->getDefinition(self::KERNEL_ID)->setFile($kernelPath);
        }
    }

    private function loadContextInitializer(ContainerBuilder $container)
    {
        $definition = new Definition('Shopware\Behat\ShopwareExtension\Context\Initializer\KernelAwareInitializer', [
            new Reference(self::KERNEL_ID),
        ]);
        $definition->addTag(ContextExtension::INITIALIZER_TAG, ['priority' => 0]);
        $definition->addTag(EventDispatcherExtension::SUBSCRIBER_TAG, ['priority' => 0]);
        $container->setDefinition('shopware_extension.context_initializer.kernel_aware', $definition);
    }

    private function loadKernel(ContainerBuilder $container, array $config)
    {
        $definition = new Definition($config['class'], [
            $config['env'],
            $config['debug'],
        ]);
        $definition->addMethodCall('boot');
        $container->setDefinition(self::KERNEL_ID, $definition);
        $container->setParameter(self::KERNEL_ID . '.path', $config['path']);
        $container->setParameter(self::KERNEL_ID . '.bootstrap', $config['bootstrap']);
    }
}
