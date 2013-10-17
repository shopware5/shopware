<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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

namespace Shopware;

use Shopware\Components\DependencyInjection\ServiceDefinition;
use Shopware\Components\ConfigLoader;
use Shopware\Components\ResourceLoader;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel as SymfonyKernel;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Enlight_Controller_Response_ResponseHttp as EnlightResponse;
use Enlight_Controller_Request_RequestHttp as EnlightRequest;

/**
 * Middleware class between the old Shopware bootstrap mechanism
 * and the Symfony Kernel handling
 *
 * @category  Shopware
 * @package   Shopware\ShopwareKernel
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Kernel implements HttpKernelInterface
{
    /**
     * @var \Shopware
     */
    protected $shopware;

    /**
     * Contains the merged shopware configuration
     * @var array
     */
    protected $config;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Container
     */
    protected $pluginContainer;

    /**
     * @var ResourceLoader
     */
    protected $resourceLoader;

    /**
     * Enables the debug mode
     * @var boolean
     */
    protected $debug;

    /**
     * Contains the current environment
     * @var string
     */
    protected $environment;

    /**
     * Flag if the kernel already booted
     * @var bool
     */
    protected $booted;

    /**
     * @param string $environment
     * @param boolean $debug
     */
    public function __construct($environment, $debug)
    {
        $this->environment = $environment;
        $this->debug = $debug;
        $this->booted = false;
        $this->name = 'Shopware';
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
     * @return SymfonyResponse
     */
    public function handle(SymfonyRequest $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        if (false === $this->booted) {
            $this->boot();
        }

        $front = $this->getShopware()->Front();

        $front->returnResponse(true);
        $front->throwExceptions(!$catch);

        $request = $this->transformSymfonyRequestToEnlightRequest($request);

        if ($front->Request() === null) {
            $front->setRequest($request);
            $response = $front->dispatch();
        } else {
            $dispatcher = clone $front->Dispatcher();
            $response   = clone $front->Response();

            $response->clearHeaders()
                     ->clearRawHeaders()
                     ->clearBody();

            $response->setHttpResponseCode(200);
            $request->setDispatched(true);
            $dispatcher->dispatch($request, $response);
        }

        $response = $this->transformEnlightResponseToSymfonyResponse($response);

        return $response;
    }

    /**
     * @param SymfonyRequest $request
     * @return EnlightRequest
     */
    public function transformSymfonyRequestToEnlightRequest(SymfonyRequest $request)
    {
        // Overwrite superglobals with state of the SymfonyRequest
        $request->overrideGlobals();

        // Create englight request from global state
        $enlightRequest = new EnlightRequest();

        return $enlightRequest;
    }

    /**
     * @param EnlightResponse $response
     * @return SymfonyResponse
     */
    public function transformEnlightResponseToSymfonyResponse(EnlightResponse $response)
    {
        $rawHeaders = $response->getHeaders();
        $headers = array();
        foreach ($rawHeaders as $header) {
            if (!isset($headers[$header['name']]) || !empty($header['replace'])) {
                $headers[$header['name']] = array($header['value']);
            } else {
                $headers[$header['name']][] = $header['value'];
            }
        }

        $symfonyResponse = new SymfonyResponse(
            $response->getBody(),
            $response->getHttpResponseCode(),
            $headers
        );

        foreach ($response->getCookies() as $cookieName => $cookieContent) {
            $sfCookie = new Cookie(
                $cookieName,
                $cookieContent['value'],
                $cookieContent['expire'],
                $cookieContent['path'],
                (bool) $cookieContent['secure'],
                (bool) $cookieContent['httpOnly']
            );

            $symfonyResponse->headers->setCookie($sfCookie);
        }

        return $symfonyResponse;
    }

    /**
     * Boots the shopware and symfony di container
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }

        $this->initializeConfig();
        $this->initializeContainer();
        $this->initializeResourceLoader();
        $this->initializeShopware();

        $this->getContainer()->set('application', $this->shopware);
        $this->shopware->boot();

        $this->initializePluginContainer();
        if ($this->pluginContainer) {
            $this->resourceLoader->setPluginContainer($this->pluginContainer);
        }

        $this->booted = true;
    }

    /**
     * Loads the shopware configuration, which will be injected into
     * the Shopware_Application.
     * The shopware configuration is required before the shopware application booted,
     * to pass the configuration to the Symfony di container.
     */
    protected function initializeConfig()
    {
        $configLoader = new ConfigLoader(
            $this->getDocumentRoot(),
            $this->environment,
            $this->name
        );

        $this->config = $configLoader->loadConfig(
            $this->getConfigPath()
        );
    }

    /**
     * Creates a new instance of the Shopware application
     */
    protected function initializeShopware()
    {
        $this->shopware = new \Shopware(
            $this->environment,
            $this->getConfig(),
            $this->resourceLoader
        );
    }

    /**
     * Creates a new instance of the ResourceLoader
     */
    protected function initializeResourceLoader()
    {
        $this->resourceLoader = new ResourceLoader($this->getContainer());
        $this->getContainer()->set('resource_loader', $this->resourceLoader);
    }

    /**
     * Initializes the service container.
     *
     * The cached version of the service container is used when fresh, otherwise the
     * container is built.
     */
    protected function initializeContainer()
    {
        $class = $this->getContainerClass();

        $cache = new ConfigCache(
            $this->getCacheDir() . '/' . $class . '.php',
            $this->debug
        );

        if (!$cache->isFresh()) {
            $container = $this->buildContainer();
            $container->compile();
            $this->dumpContainer($cache, $container, $class, 'Container');
        }

        require_once $cache;

        $this->container = new $class();
        $this->container->set('kernel', $this);
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return __DIR__ . '/../../cache/symfony';
    }

    /**
     * @return string
     */
    protected function getConfigPath()
    {
        return __DIR__  . '/Configs/Default.php';
    }

    /**
     * @return string
     */
    protected function getDocumentRoot()
    {
        return __DIR__ . '/../../';
    }

    /**
     * Dumps the service container to PHP code in the cache.
     *
     * @param ConfigCache $cache     The config cache
     * @param ContainerBuilder $container The service container
     * @param string $class     The name of the class to generate
     * @param string $baseClass The name of the container's base class
     */
    protected function dumpContainer(ConfigCache $cache, ContainerBuilder $container, $class, $baseClass)
    {
        // cache the container
        $dumper = new PhpDumper($container);

        $content = $dumper->dump(array('class' => $class, 'base_class' => $baseClass));

        if (!$this->debug) {
            $content = SymfonyKernel::stripComments($content);
        }

        $cache->write($content, $container->getResources());
    }

    /**
     * Builds the service container.
     *
     * @return ContainerBuilder The compiled service container
     *
     * @throws \RuntimeException
     */
    protected function buildContainer()
    {
        foreach (array('cache' => $this->getCacheDir()) as $name => $dir) {
            if (!is_dir($dir)) {
                if (false === @mkdir($dir, 0777, true)) {
                    throw new \RuntimeException(sprintf("Unable to create the %s directory (%s)\n", $name, $dir));
                }
            } elseif (!is_writable($dir)) {
                throw new \RuntimeException(sprintf("Unable to write in the %s directory (%s)\n", $name, $dir));
            }
        }

        $container = $this->getContainerBuilder();
        $container->addObjectResource($this);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/Configs/'));
        $loader->load('services.xml');

        $this->addShopwareConfig($container, 'shopware', $this->config);

        return $container;
    }

    /**
     * Adds all shopware configuration as di container parameter.
     * Each shopware configuration has the alias "shopware."
     *
     * @param Container $container
     * @param string $alias
     * @param array|string $options
     */
    protected function addShopwareConfig(Container $container, $alias, $options)
    {
        foreach ($options as $key => $option) {
            $container->setParameter($alias . '.' . $key, $option);

            if (is_array($option)) {
                $this->addShopwareConfig($container, $alias . '.' . $key, $option);
            }
        }
    }

    /**
     * Gets a new ContainerBuilder instance used to build the service container.
     *
     * @return ContainerBuilder
     */
    protected function getContainerBuilder()
    {
        return new ContainerBuilder(
            new ParameterBag(
                $this->getKernelParameters()
            )
        );
    }

    /**
     * Returns the kernel parameters.
     *
     * @return array An array of kernel parameters
     */
    protected function getKernelParameters()
    {
        return array(
            'kernel.root_dir' => $this->getDocumentRoot(),
            'kernel.environment' => $this->environment,
            'kernel.debug' => $this->debug,
            'kernel.name' => $this->name,
            'kernel.cache_dir' => $this->getCacheDir(),
            'kernel.charset' => 'UTF-8',
            'kernel.container_class' => $this->getContainerClass(),
        );
    }

    /**
     * @return \Shopware
     */
    protected function getShopware()
    {
        return $this->shopware;
    }

    /**
     * Returns the di container
     * @return Container
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * Gets the container class.
     *
     * @param string $nameSuffix
     * @return string The container class
     */
    protected function getContainerClass($nameSuffix = null)
    {

        return $this->name . \Shopware::REVISION . ($nameSuffix? ucfirst($nameSuffix) : '') . ucfirst($this->environment) . ($this->debug ? 'Debug' : '') . 'ProjectContainer';
    }

    /**
     * @return array
     */
    protected function getConfig()
    {
        return $this->config;
    }

    /**
     *
     */
    protected function initializePluginContainer()
    {
        try {
            $this->pluginContainer = $this->buildPluginContainer();
            $this->pluginContainer->compile();
        } catch (\Exception $e) {
            $this->kernelException = $e;
            $this->container->set('kernel.exception', $e);
        }
    }

    /**
     * @return ContainerBuilder
     */
    protected function buildPluginContainer()
    {
        $container = $this->getContainerBuilder();
        $container->addObjectResource($this);

        $this->addShopwareConfig($container, 'shopware', $this->config);
        $this->addPluginContainerExtensions($container);

        return $container;
    }

    /**
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function addPluginContainerExtensions(ContainerBuilder $container)
    {
        $this->getShopware()->Front();

        $collection = array();

        //throw event to collect all plugin container service definitions.
        $collection = $this->shopware->Events()->filter(
            'Shopware_Plugin_Container_Add_Services',
            $collection,
            array()
        );

        /** @var $service ServiceDefinition */
        foreach ($collection as $service) {
            if (!$service instanceof ServiceDefinition) {
                throw new \Exception('Some plugin tries to add a service without using the \Shopware\DependencyInjection\ServiceDefinition class');
            }

            if ($service->getConfig()) {
                $this->addShopwareConfig($container, $service->getAlias(), $service->getConfig());
            }

            $loader = new XmlFileLoader($container, new FileLocator(dirname($service->getXmlPath())));
            $loader->load(basename($service->getXmlPath()));
        }
    }
}
