<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Components;

use Enlight\Event\SubscriberInterface;
use Shopware\Components\Filesystem\PrefixFilesystem;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

abstract class Plugin extends Bundle implements SubscriberInterface
{
    /**
     * @var string
     */
    protected $pluginNamespace;

    /**
     * @var bool
     */
    protected $autoloadViews = false;

    private bool $isActive;

    final public function __construct(bool $isActive, string $namespace)
    {
        $this->isActive = $isActive;
        $this->pluginNamespace = $namespace;
    }

    /**
     * @internal Only to use in core for plugin namespace identification
     */
    final public function getPluginNamespace(): string
    {
        return $this->pluginNamespace;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [];
    }

    /**
     * @return bool
     */
    final public function isActive()
    {
        return $this->isActive;
    }

    public function hasAutoloadViews(): bool
    {
        return $this->autoloadViews;
    }

    /**
     * Registers Commands.
     *
     * @param Application $application An Application instance
     *
     * @return void
     */
    public function registerCommands(Application $application)
    {
    }

    /**
     * This method can be overridden
     *
     * @return void
     */
    public function install(InstallContext $context)
    {
    }

    /**
     * This method can be overridden
     *
     * @return void
     */
    public function update(UpdateContext $context)
    {
        $context->scheduleClearCache(InstallContext::CACHE_LIST_DEFAULT);
    }

    /**
     * This method can be overridden
     *
     * @return void
     */
    public function activate(ActivateContext $context)
    {
        $context->scheduleClearCache(InstallContext::CACHE_LIST_DEFAULT);
    }

    /**
     * This method can be overridden
     *
     * @return void
     */
    public function deactivate(DeactivateContext $context)
    {
        $context->scheduleClearCache(InstallContext::CACHE_LIST_DEFAULT);
    }

    /**
     * This method can be overridden
     *
     * @return void
     */
    public function uninstall(UninstallContext $context)
    {
        $context->scheduleClearCache(InstallContext::CACHE_LIST_DEFAULT);
    }

    /**
     * Builds the Plugin.
     *
     * It is only ever called once when the cache is empty.
     *
     * This method can be overridden to register compilation passes,
     * other extensions, ...
     *
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @return void
     */
    public function build(ContainerBuilder $container)
    {
        $container->setParameter($this->getContainerPrefix() . '.plugin_dir', $this->getPath());
        $container->setParameter($this->getContainerPrefix() . '.plugin_name', $this->getName());
        $this->registerFilesystems($container);
        $this->loadFiles($container);
    }

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    final public function setContainer(?ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getContainerPrefix()
    {
        return $this->camelCaseToUnderscore($this->getName());
    }

    final protected function loadFiles(ContainerBuilder $container): void
    {
        if (!is_file($this->getPath() . '/Resources/services.xml')) {
            return;
        }

        $loader = new XmlFileLoader(
            $container,
            new FileLocator()
        );

        $loader->load($this->getPath() . '/Resources/services.xml');
    }

    private function camelCaseToUnderscore(string $string): string
    {
        return (new CamelCaseToSnakeCaseNameConverter())->normalize($string);
    }

    private function registerFilesystems(ContainerBuilder $container): void
    {
        $this->registerFilesystem($container, 'private');
        $this->registerFilesystem($container, 'public');
    }

    private function registerFilesystem(ContainerBuilder $container, string $key): void
    {
        $parameterKey = sprintf('shopware.filesystem.%s', $key);
        $serviceId = sprintf('%s.filesystem.%s', $this->getContainerPrefix(), $key);

        $filesystem = new Definition(
            PrefixFilesystem::class,
            [
                new Reference($parameterKey),
                'plugins/' . $this->getContainerPrefix(),
            ]
        );
        $filesystem->setPublic(true);

        $container->setDefinition($serviceId, $filesystem);
    }
}
