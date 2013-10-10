<?php

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Enlight_Controller_Response_ResponseHttp as EnlightResponse;
use Enlight_Controller_Request_RequestHttp as EnlightRequest;

/**
 * Middleware class between the old Shopware bootstrap mechanism
 * and the Symfony Kernel handling
 */
class ShopwareKernel implements HttpKernelInterface
{
    /**
     * @var Shopware
     */
    protected $shopware;

    /**
     * @var Container
     */
    protected $container;

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

        $response = $front->dispatch();
        $response = $this->transformEnlightResponseToSymfonyResponse($response);

        return $response;
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
        $this->initializeShopware();

        $this->initializeContainer();

        $this->getShopware()->setContainer($this->getContainer());

        $this->booted = true;
    }

    /**
     * Creates a new instance of the Shopware application
     */
    protected function initializeShopware()
    {
        $this->shopware = new Shopware($this->environment);
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

        //TODO implement the stripComments to reduce the container content
//        if (!$this->debug) {
//            $content = self::stripComments($content);
//        }

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
        $loader->load('twig.xml');

        $this->addShopwareConfig($container, 'shopware.', $this->getShopware()->getOptions());

        return $container;
    }

    /**
     * Adds all shopware configuration as di container parameter.
     * Each shopware configuration has the alias "shopware."
     * @param Container $container
     * @param string $alias
     * @param array|string $options
     */
    protected function addShopwareConfig(Container $container, $alias, $options)
    {
        foreach($options as $key => $option) {
            $container->setParameter($alias . $key, $option);

            if (is_array($option)) {
                $this->addShopwareConfig($container, $alias . $key . '.' , $option);
            }
        }

        // fill container parameters with needed but currently optional options
        $defaults = array(
            'config' => array()
        );

        foreach ($defaults as $key => $defaultConfigs) {
            if (!isset($options[$key])) {
                $container->setParameter($alias . $key, $defaultConfigs);
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
            'kernel.root_dir' => __DIR__ . '/../../',
            'kernel.environment' => $this->environment,
            'kernel.debug' => $this->debug,
            'kernel.name' => $this->name,
            'kernel.cache_dir' => $this->getCacheDir(),
            'kernel.charset' => 'UTF-8',
            'kernel.container_class' => $this->getContainerClass(),
        );
    }

    /**
     * @return Shopware
     */
    protected function getShopware()
    {
        return $this->shopware;
    }

    public function getCacheDir()
    {
        return __DIR__ . '/../../cache/symfony';
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
     * @return string The container class
     */
    protected function getContainerClass()
    {
        return $this->name . ucfirst($this->environment) . ($this->debug ? 'Debug' : '') . 'ProjectContainer';
    }
}
