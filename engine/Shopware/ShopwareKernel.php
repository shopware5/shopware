<?php

use Shopware\DependencyInjection\ShopwareExtension;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Middleware class between the old Shopware bootstrap mechanism
 * and the Symfony Kernel handling
 */
class ShopwareKernel extends Kernel
{
    /**
     * @var Shopware
     */
    protected $shopware;

    protected function initializeContainer()
    {
        // shopware has to be initialized because we need its options
        // before the container is loaded
        $this->initializeShopware();

        parent::initializeContainer();

        $this->getShopware()->setContainer($this->getContainer());
    }

    protected function prepareContainer(ContainerBuilder $container)
    {
        $extension = new ShopwareExtension($this->getShopware()->getOptions());
        $container->registerExtension($extension);
        $container->loadFromExtension($extension->getAlias(), array());

        parent::prepareContainer($container);
    }

    protected function initializeShopware()
    {
        $this->shopware = new Shopware($this->environment);
    }

    /**
     * @return Shopware
     */
    protected function getShopware()
    {
        return $this->shopware;
    }

    /**
     * @deprecated This makes the Shopware instance accessible to e.g. HttpCache, this has to be removed!
     */
    public function getApp()
    {
        return $this->getShopware();
    }

    /**
     * This wraps Shopware:run execution and does not execute
     * the default dispatching process from symfony.
     * Therefore:
     * Arguments are currently ignored. No dispatcher, no response handling.
     * Shopware instance returns currently the rendered response directly.
     *
     * {@inheritdoc}
     *
     * @return string
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        if (false === $this->booted) {
            $this->boot();
        }

        // httpkernel could not be executed here because
        // the filled request is ignored currently
        return $this->shopware->run();
    }

    /**
     * Returns an array of bundles to registers.
     *
     * @return BundleInterface[] An array of bundle instances.
     */
    public function registerBundles()
    {
        return array();
    }

    /**
     * Loads the container configuration
     *
     * @param LoaderInterface $loader A LoaderInterface instance
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/Configs/config_'.$this->getEnvironment().'.xml');
    }

    /**
     * {@inheritdoc}
     *
     * Needs to be overwritten to fulfill shopware project requirements
     *
     * @return string
     */
    public function getCacheDir()
    {
        // @TODO is the cache folder configurable? then this has to be refactored!
        return __DIR__ . '/../../cache/symfony';
    }

    /**
     * {@inheritdoc}
     *
     * Needs to be overwritten to fulfill shopware project requirements
     *
     * @return string
     */
    public function getLogDir()
    {
        // @TODO logging is not needed currently. however the kernel needs a valid log folder.
        return __DIR__ . '/../../cache/symfony/logs';
    }

    /**
     * {@inheritdoc}
     *
     * Needs to be overwritten to fulfill shopware project requirements
     *
     * @return string
     */
    public function getRootDir()
    {
        return __DIR__ . '/../..';
    }

    /**
     * {@inheritdoc}
     *
     * Needs to be overwritten to fulfill shopware project requirements
     *
     * @return string
     */
    public function getName()
    {
        return 'Shopware';
    }

    public function getCharset()
    {
        $options = $this->getShopware()->getOption('front');

        return isset($options['charset']) ? $options['charset'] : 'UTF-8';
    }
}
