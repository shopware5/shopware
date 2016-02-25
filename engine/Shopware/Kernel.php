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

namespace Shopware;

use Shopware\Bundle\ESIndexingBundle\DependencyInjection\CompilerPass\SettingsCompilerPass;
use Shopware\Bundle\ESIndexingBundle\DependencyInjection\CompilerPass\SynchronizerCompilerPass;
use Shopware\Bundle\ESIndexingBundle\DependencyInjection\CompilerPass\DataIndexerCompilerPass;
use Shopware\Bundle\ESIndexingBundle\DependencyInjection\CompilerPass\MappingCompilerPass;
use Shopware\Bundle\SearchBundle\DependencyInjection\Compiler\CriteriaRequestHandlerCompilerPass;
use Shopware\Bundle\SearchBundleDBAL\DependencyInjection\Compiler\DBALCompilerPass;
use Shopware\Bundle\SearchBundleES\DependencyInjection\CompilerPass\SearchHandlerCompilerPass;
use Shopware\Components\DependencyInjection\Compiler\DoctrineEventSubscriberCompilerPass;
use Shopware\Components\DependencyInjection\Compiler\EventListenerCompilerPass;
use Shopware\Components\DependencyInjection\Compiler\EventSubscriberCompilerPass;
use Shopware\Components\ConfigLoader;
use Shopware\Components\DependencyInjection\Container;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Resource\FileResource;
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
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
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
    protected $booted = false;

    /**
     * @var string
     */
    protected $name;

    const VERSION      = \Shopware::VERSION;
    const VERSION_TEXT = \Shopware::VERSION_TEXT;
    const REVISION     = \Shopware::REVISION;

    /**
     * @param string $environment
     * @param boolean $debug
     */
    public function __construct($environment, $debug)
    {
        $this->environment = $environment;
        $this->debug = (boolean) $debug;
        $this->name = 'Shopware';

        $this->initializeConfig();

        if (!empty($this->config['phpsettings'])) {
            $this->setPhpSettings($this->config['phpsettings']);
        }

        if ($trustedProxies = $this->config['trustedproxies']) {
            SymfonyRequest::setTrustedProxies($trustedProxies);
        }
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

        /** @var $front \Enlight_Controller_Front **/
        $front = $this->container->get('front');

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

        // Set commandline args as request uri
        // This is used for legacy cronjob routing.
        // e.g: /usr/bin/php shopware.php /backend/cron
        if (PHP_SAPI === 'cli'
            && is_array($argv = $request->server->get('argv'))
            && isset($argv[1])
        ) {
            $enlightRequest->setRequestUri($argv[1]);
        }

        // Let the symfony request handle the trusted proxies
        $enlightRequest->setRemoteAddress($request->getClientIp());
        $enlightRequest->setSecure($request->isSecure());

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
                header_remove($header['name']);
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
                $cookieContent['domain'],
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

        $this->initializeContainer();
        $this->initializeShopware();

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
            $this->getRootDir(),
            $this->getCacheDir(),
            $this->environment,
            $this->name
        );

        $this->config = $configLoader->loadConfig(
            $this->getConfigPath()
        );

        // Set up mpdf cache dirs
        define("_MPDF_TEMP_PATH", $this->getCacheDir() .'/mpdf/tmp/');
        define("_MPDF_TTFONTDATAPATH", $this->getCacheDir() .'/mpdf/ttfontdata/');
    }

    /**
     * Sets the php settings from the config
     *
     * @param array $settings
     * @param string $prefix
     */
    public function setPhpSettings(array $settings, $prefix = '')
    {
        foreach ($settings as $key => $value) {
            $key = empty($prefix) ? $key : $prefix . $key;
            if (is_scalar($value)) {
                ini_set($key, $value);
            } elseif (is_array($value)) {
                $this->setPhpSettings($value, $key . '.');
            }
        }
    }

    /**
     * Creates a new instance of the Shopware application
     */
    protected function initializeShopware()
    {
        $this->shopware = new \Shopware(
            $this->environment,
            $this->config,
            $this->container
        );
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
            $this->config['hook']['proxyDir'] . '/' . $class . '.php',
            true //always check for file modified time
        );

        if (!$cache->isFresh()) {
            $container = $this->buildContainer();
            $container->compile();
            $this->dumpContainer($cache, $container, $class, 'Shopware\Components\DependencyInjection\Container');
        }

        require_once $cache->getPath();

        $this->container = new $class();
        $this->container->set('kernel', $this);
    }

    /**
     * @return bool
     */
    public function isHttpCacheEnabled()
    {
        $config = $this->getHttpCacheConfig();

        return (isset($config['enabled']) && $config['enabled']);
    }

    /**
     * @return bool
     */
    public function isElasticSearchEnabled()
    {
        $config = $this->getElasticSearchConfig();
        return (isset($config['enabled']) && $config['enabled']);
    }

    /**
     * Gets the environment.
     *
     * @return string The current environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Checks if debug mode is enabled.
     *
     * @return Boolean true if debug mode is enabled, false otherwise
     *
     * @api
     */
    public function isDebug()
    {
        return $this->debug;
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
    public function getRootDir()
    {
        return realpath(__DIR__ . '/../../');
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->getRootDir().'/var/cache/'.$this->environment.'_'.\Shopware::REVISION;
    }

    /**
     * Gets the log directory.
     *
     * @return string The log directory
     */
    public function getLogDir()
    {
        return $this->getRootDir().'/var/log';
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
        $runtimeDirectories = array(
            'cache'              => $this->getCacheDir(),
            'coreCache'          => $this->config['cache']['backendOptions']['cache_dir'],
            'mpdfTemp'           => _MPDF_TEMP_PATH,
            'mpdfFontData'       => _MPDF_TTFONTDATAPATH,
            'logs'               => $this->getLogDir()
        );

        foreach ($runtimeDirectories as $name => $dir) {
            if (!is_dir($dir)) {
                if (false === @mkdir($dir, 0777, true) && !is_dir($dir)) {
                    throw new \RuntimeException(sprintf("Unable to create the %s directory (%s)\n", $name, $dir));
                }
            } elseif (!is_writable($dir)) {
                throw new \RuntimeException(sprintf("Unable to write in the %s directory (%s)\n", $name, $dir));
            }
        }

        $container = $this->getContainerBuilder();
        $container->addObjectResource($this);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/Components/DependencyInjection/'));
        $loader->load('services.xml');
        $loader->load('theme.xml');
        $loader->load('logger.xml');

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/Bundle/'));
        $loader->load('SearchBundle/services.xml');
        $loader->load('SearchBundleDBAL/services.xml');
        $loader->load('StoreFrontBundle/services.xml');
        $loader->load('PluginInstallerBundle/services.xml');
        $loader->load('ESIndexingBundle/services.xml');
        $loader->load('MediaBundle/services.xml');

        if ($this->isElasticSearchEnabled()) {
            $loader->load('SearchBundleES/services.xml');
        }

        if (is_file($file = __DIR__ . '/Components/DependencyInjection/services_local.xml')) {
            $loader->load($file);
        }

        $this->addShopwareConfig($container, 'shopware', $this->config);
        $this->addResources($container);

        $container->addCompilerPass(new EventListenerCompilerPass());
        $container->addCompilerPass(new EventSubscriberCompilerPass());
        $container->addCompilerPass(new DoctrineEventSubscriberCompilerPass());
        $container->addCompilerPass(new DBALCompilerPass());
        $container->addCompilerPass(new CriteriaRequestHandlerCompilerPass());
        $container->addCompilerPass(new MappingCompilerPass());
        $container->addCompilerPass(new SynchronizerCompilerPass());
        $container->addCompilerPass(new DataIndexerCompilerPass());
        $container->addCompilerPass(new SettingsCompilerPass());

        if ($this->isElasticSearchEnabled()) {
            $container->addCompilerPass(new SearchHandlerCompilerPass());
        }

        return $container;
    }

    /**
     * @param ContainerBuilder $container
     */
    public function addResources(ContainerBuilder $container)
    {
        $files = array(
            '/config.php',
            '/config_dev.php',
        );
        foreach ($files as $file) {
            if (!is_file($filePath = $this->getRootDir() . $file)) {
                continue;
            }
            $resource = new FileResource($filePath);
            $container->addResource($resource);
        }
    }

    /**
     * Adds all shopware configuration as di container parameter.
     * Each shopware configuration has the alias "shopware."
     *
     * @param \Shopware\Components\DependencyInjection\Container|\Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string $alias
     * @param array|string $options
     */
    protected function addShopwareConfig(ContainerBuilder $container, $alias, $options)
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
            'kernel.root_dir'        => $this->getRootDir(),
            'kernel.environment'     => $this->environment,
            'kernel.debug'           => $this->debug,
            'kernel.name'            => $this->name,
            'kernel.cache_dir'       => $this->getCacheDir(),
            'kernel.logs_dir'        => $this->getLogDir(),
            'kernel.charset'         => 'UTF-8',
            'kernel.container_class' => $this->getContainerClass(),
        );
    }

    /**
     * @return \Shopware
     */
    public function getShopware()
    {
        return $this->shopware;
    }

    /**
     * Returns the di container
     * @return Container
     */
    public function getContainer()
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
        return $this->name.ucfirst($this->environment).($this->debug ? 'Debug' : '').'ProjectContainer';
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return array
     */
    public function getHttpCacheConfig()
    {
        return is_array($this->config['httpcache']) ? $this->config['httpcache'] : [];
    }

    /**
     * @return array
     */
    public function getElasticSearchConfig()
    {
        return is_array($this->config['es']) ? $this->config['es'] : [];
    }
}
