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

namespace Shopware\Components;

use Shopware\Components\Plugin\SubscriberInterface;
use Shopware\Components\Filesystem\PrefixFilesystem;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\Bundle;

abstract class Plugin extends Bundle implements SubscriberInterface
{
    /**
     * @var bool
     */
    private $isActive;

    final public function __construct(bool $isActive)
    {
        $this->isActive = $isActive;
    }

    public function build(ContainerBuilder $container)
    {
        $container->setParameter($this->getContainerPrefix() . '.plugin_dir', $this->getPath());
        $container->setParameter($this->getContainerPrefix() . '.plugin_name', $this->getName());

        $container->addObjectResource($this);

        $this->registerFilesystem($container, 'private');
        $this->registerFilesystem($container, 'public');

        if (is_file($this->getPath() . '/Resources/services.xml')) {
            $loader = new XmlFileLoader($container, new FileLocator());
            $loader->load($this->getPath() . '/Resources/services.xml');
        }
    }

    final public function getPath(): string
    {
        return parent::getPath();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [];
    }

    final public function isActive(): bool
    {
        return $this->isActive;
    }

    public function install(InstallContext $context): void
    {
    }

    public function update(UpdateContext $context): void
    {
        $context->scheduleClearCache(InstallContext::CACHE_LIST_DEFAULT);
    }

    public function activate(ActivateContext $context)
    {
        $context->scheduleClearCache(InstallContext::CACHE_LIST_DEFAULT);
    }

    public function deactivate(DeactivateContext $context): void
    {
        $context->scheduleClearCache(InstallContext::CACHE_LIST_DEFAULT);
    }

    public function uninstall(UninstallContext $context): void
    {
        $context->scheduleClearCache(InstallContext::CACHE_LIST_DEFAULT);
    }

    public function getContainerPrefix(): string
    {
        return $this->camelCaseToUnderscore($this->getName());
    }

    private function camelCaseToUnderscore(string $string): string
    {
        return ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $string)), '_');
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $key
     */
    private function registerFilesystem(ContainerBuilder $container, string $key)
    {
        $parameterKey = sprintf('shopware.filesystem.%s', $key);
        $serviceId = sprintf('%s.filesystem.%s', $this->getContainerPrefix(), $key);

        $filesystem = new Definition(
            PrefixFilesystem::class,
            [
                new Reference($parameterKey),
                'pluginData/' . $this->getName(),
            ]
        );

        $container->setDefinition($serviceId, $filesystem);
    }
}
