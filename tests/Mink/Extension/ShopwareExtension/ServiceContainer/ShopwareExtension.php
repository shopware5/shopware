<?php

declare(strict_types=1);
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
use Shopware\Behat\ShopwareExtension\Context\Initializer\KernelAwareInitializer;
use Shopware\Kernel;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ShopwareExtension implements ExtensionInterface
{
    public const KERNEL_ID = 'shopware_extension.kernel';

    /**
     * Returns the extension config key.
     */
    public function getConfigKey(): string
    {
        return 'shopware';
    }

    public function initialize(ExtensionManager $extensionManager): void
    {
    }

    /**
     * Setups configuration for the extension.
     */
    public function configure(ArrayNodeDefinition $builder): void
    {
        $boolFilter = function ($v) {
            $filtered = filter_var($v, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            return $filtered ?? $v;
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
                            ->defaultValue(Kernel::class)
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
     * @param array<string, mixed> $config
     */
    public function load(ContainerBuilder $container, array $config): void
    {
        $this->loadContextInitializer($container);
        $this->loadKernel($container, $config['kernel']);
    }

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @api
     */
    public function process(ContainerBuilder $container): void
    {
        // get base path
        $basePath = $container->getParameter('paths.base');
        if (!\is_string($basePath)) {
            Helper::throwException('Invalid container parameter "paths.base"');
        }

        // find and require bootstrap
        $bootstrapPath = $container->getParameter('shopware_extension.kernel.bootstrap');
        if (\is_string($bootstrapPath)) {
            $bootstrap = $basePath . '/' . $bootstrapPath;
            if (file_exists($bootstrap)) {
                require_once $bootstrap;
            } elseif (file_exists($bootstrapPath)) {
                require_once $bootstrapPath;
            }
        }

        // find and require kernel
        $kernelPath = $container->getParameter('shopware_extension.kernel.path');
        if (!\is_string($kernelPath)) {
            Helper::throwException('Invalid container parameter "shopware_extension.kernel.path"');
        }

        $kernel = $basePath . '/' . $kernelPath;
        if (file_exists($kernel)) {
            $container->getDefinition(self::KERNEL_ID)->setFile($kernel);
        } elseif (file_exists($kernelPath)) {
            $container->getDefinition(self::KERNEL_ID)->setFile($kernelPath);
        }
    }

    private function loadContextInitializer(ContainerBuilder $container): void
    {
        $definition = new Definition(KernelAwareInitializer::class, [
            new Reference(self::KERNEL_ID),
        ]);
        $definition->addTag(ContextExtension::INITIALIZER_TAG, ['priority' => 0]);
        $definition->addTag(EventDispatcherExtension::SUBSCRIBER_TAG, ['priority' => 0]);
        $container->setDefinition('shopware_extension.context_initializer.kernel_aware', $definition);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function loadKernel(ContainerBuilder $container, array $config): void
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
