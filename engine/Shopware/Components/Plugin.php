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

use Shopware\Components\Console\Application;
use Shopware\Components\Plugin\PluginContext;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Enlight\Event\SubscriberInterface;

abstract class Plugin implements ContainerAwareInterface, SubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $path;

    /**
     * @var bool
     */
    private $isActive;

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [];
    }

    /**
     * @param bool $isActive
     */
    final public function __construct($isActive)
    {
        $this->isActive = (bool)$isActive;
    }

    /**
     * @return bool
     */
    final public function isActive()
    {
        return $this->isActive;
    }

    /**
     * Registers Commands.
     *
     * @param Application $application An Application instance
     */
    public function registerCommands(Application $application)
    {
    }

    /**
     * This method can be overridden
     * @param PluginContext $context
     */
    public function install(PluginContext $context)
    {
    }

    /**
     * This method can be overridden
     * @param PluginContext $context
     */
    public function update(PluginContext $context)
    {
        $context->scheduleClearCache(PluginContext::CACHE_LIST_DEFAULT);
    }

    /**
     * This method can be overridden
     * @param PluginContext $context
     */
    public function activate(PluginContext $context)
    {
        $context->scheduleClearCache(PluginContext::CACHE_LIST_DEFAULT);
    }

    /**
     * This method can be overridden
     * @param PluginContext $context
     */
    public function deactivate(PluginContext $context)
    {
        $context->scheduleClearCache(PluginContext::CACHE_LIST_DEFAULT);
    }

    /**
     * This method can be overridden
     * @param PluginContext $context
     */
    public function uninstall(PluginContext $context)
    {
        $context->scheduleClearCache(PluginContext::CACHE_LIST_DEFAULT);
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
     */
    public function build(ContainerBuilder $container)
    {
        $this->loadFiles($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    final protected function loadFiles(ContainerBuilder $container)
    {
        if (!is_file($this->getPath().'/Resources/services.xml')) {
            return;
        }

        $loader = new XmlFileLoader(
            $container,
            new FileLocator()
        );

        $loader->load($this->getPath().'/Resources/services.xml');
    }

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    final public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Returns the Plugin name (the class short name).
     *
     * @return string The Plugin name
     */
    final public function getName()
    {
        if (null !== $this->name) {
            return $this->name;
        }

        $name = get_class($this);
        $pos = strrpos($name, '\\');

        return $this->name = false === $pos ? $name : substr($name, $pos + 1);
    }

     /**
     * Gets the Plugin directory path.
     *
     * @return string The Plugin absolute path
     */
    final public function getPath()
    {
        if (null === $this->path) {
            $reflected = new \ReflectionObject($this);
            $this->path = dirname($reflected->getFileName());
        }

        return $this->path;
    }
}
