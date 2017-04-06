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

use Enlight_Controller_Request_RequestHttp as EnlightRequest;
use Enlight_Controller_Response_ResponseHttp as EnlightResponse;
use Shopware\Bundle\AttributeBundle\DependencyInjection\Compiler\SearchRepositoryCompilerPass;
use Shopware\Bundle\ControllerBundle\DependencyInjection\Compiler\RegisterControllerCompilerPass;
use Shopware\Bundle\CustomerSearchBundle\DependencyInjection\Compiler\HandlerRegistryCompilerPass;
use Shopware\Bundle\EmotionBundle\DependencyInjection\Compiler\EmotionComponentHandlerCompilerPass;
use Shopware\Bundle\ESIndexingBundle\DependencyInjection\CompilerPass\DataIndexerCompilerPass;
use Shopware\Bundle\ESIndexingBundle\DependencyInjection\CompilerPass\MappingCompilerPass;
use Shopware\Bundle\ESIndexingBundle\DependencyInjection\CompilerPass\SettingsCompilerPass;
use Shopware\Bundle\ESIndexingBundle\DependencyInjection\CompilerPass\SynchronizerCompilerPass;
use Shopware\Bundle\FormBundle\DependencyInjection\CompilerPass\AddConstraintValidatorsPass;
use Shopware\Bundle\FormBundle\DependencyInjection\CompilerPass\FormPass;
use Shopware\Bundle\MediaBundle\DependencyInjection\Compiler\MediaAdapterCompilerPass;
use Shopware\Bundle\MediaBundle\DependencyInjection\Compiler\MediaOptimizerCompilerPass;
use Shopware\Bundle\PluginInstallerBundle\Service\PluginInitializer;
use Shopware\Bundle\SearchBundle\DependencyInjection\Compiler\CriteriaRequestHandlerCompilerPass;
use Shopware\Bundle\SearchBundleDBAL\DependencyInjection\Compiler\DBALCompilerPass;
use Shopware\Bundle\SearchBundleES\DependencyInjection\CompilerPass\SearchHandlerCompilerPass;
use Shopware\Components\ConfigLoader;
use Shopware\Components\DependencyInjection\Compiler\AddCaptchaCompilerPass;
use Shopware\Components\DependencyInjection\Compiler\AddConsoleCommandPass;
use Shopware\Components\DependencyInjection\Compiler\DoctrineEventSubscriberCompilerPass;
use Shopware\Components\DependencyInjection\Compiler\EventListenerCompilerPass;
use Shopware\Components\DependencyInjection\Compiler\EventSubscriberCompilerPass;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\Plugin;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel as SymfonyKernel;

/**
 * Middleware class between the old Shopware bootstrap mechanism
 * and the Symfony Kernel handling
 *
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Kernel implements HttpKernelInterface
{
    const VERSION = \Shopware::VERSION;
    const VERSION_TEXT = \Shopware::VERSION_TEXT;
    const REVISION = \Shopware::REVISION;
    /**
     * @var \Shopware
     */
    protected $shopware;

    /**
     * Contains the merged shopware configuration
     *
     * @var array
     */
    protected $config;

    /**
     * @var Container
     */
    protected $container;

    /**
     * Enables the debug mode
     *
     * @var bool
     */
    protected $debug;

    /**
     * Contains the current environment
     *
     * @var string
     */
    protected $environment;

    /**
     * Flag if the kernel already booted
     *
     * @var bool
     */
    protected $booted = false;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var Plugin[]
     */
    private $plugins = [];

    /**
     * @var string
     */
    private $pluginHash;

    /**
     * @var \PDO
     */
    private $connection;

    /**
     * @param string $environment
     * @param bool   $debug
     */
    public function __construct($environment, $debug)
    {
        $debug = false;

        $this->environment = $environment;
        $this->debug = (bool) $debug;
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

        /** @var $front \Enlight_Controller_Front * */
        $front = $this->container->get('front');

        $request = $this->transformSymfonyRequestToEnlightRequest($request);

        if ($front->Request() === null) {
            $front->setRequest($request);
            $response = $front->dispatch();
        } else {
            $dispatcher = clone $front->Dispatcher();
            $response = clone $front->Response();

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
     *
     * @return EnlightRequest
     */
    public function transformSymfonyRequestToEnlightRequest(SymfonyRequest $request)
    {
        // Overwrite superglobals with state of the SymfonyRequest
        $request->overrideGlobals();

        // Create englight request from global state
        $enlightRequest = new EnlightRequest();

        // Let the symfony request handle the trusted proxies
        $enlightRequest->setRemoteAddress($request->getClientIp());
        $enlightRequest->setSecure($request->isSecure());

        return $enlightRequest;
    }

    /**
     * @param EnlightResponse $response
     *
     * @return SymfonyResponse
     */
    public function transformEnlightResponseToSymfonyResponse(EnlightResponse $response)
    {
        $rawHeaders = $response->getHeaders();
        $headers = [];
        foreach ($rawHeaders as $header) {
            if (!isset($headers[$header['name']]) || !empty($header['replace'])) {
                header_remove($header['name']);
                $headers[$header['name']] = [$header['value']];
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
     *
     * @param bool $skipDatabase
     */
    public function boot($skipDatabase = false)
    {
        if ($this->booted) {
            return;
        }

        if (!$skipDatabase) {
            $dbConn = $this->config['db'];
            $this->connection = Components\DependencyInjection\Bridge\Db::createPDO($dbConn);
            $this->initializePlugins();
        }

        $this->initializeContainer();
        $this->initializeShopware();

        foreach ($this->getPlugins() as $plugin) {
            $plugin->setContainer($this->container);

            if (!$plugin->isActive()) {
                continue;
            }

            $this->container->get('events')->addSubscriber($plugin);
            $this->container->get('events')->addSubscriber(new Plugin\ResourceSubscriber($plugin->getPath()));
        }

        $this->booted = true;
    }

    /**
     * @return Plugin[]
     */
    public function getPlugins()
    {
        return $this->plugins;
    }

    /**
     * Sets the php settings from the config
     *
     * @param array  $settings
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
     * @return bool
     */
    public function isHttpCacheEnabled()
    {
        $config = $this->getHttpCacheConfig();

        return isset($config['enabled']) && $config['enabled'];
    }

    /**
     * @return bool
     */
    public function isElasticSearchEnabled()
    {
        $config = $this->getElasticSearchConfig();

        return isset($config['enabled']) && $config['enabled'];
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
     * @return bool true if debug mode is enabled, false otherwise
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
    public function getRootDir()
    {
        return realpath(__DIR__ . '/../../');
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->getRootDir() . '/var/cache/' . $this->environment . '_' . \Shopware::REVISION;
    }

    /**
     * Gets the log directory.
     *
     * @return string The log directory
     */
    public function getLogDir()
    {
        return $this->getRootDir() . '/var/log';
    }

    /**
     * @param ContainerBuilder $container
     */
    public function addResources(ContainerBuilder $container)
    {
        $files = [
            '/config.php',
            '/config_dev.php',
        ];
        foreach ($files as $file) {
            if (!is_file($filePath = $this->getRootDir() . $file)) {
                continue;
            }
            $resource = new FileResource($filePath);
            $container->addResource($resource);
        }
    }

    /**
     * Returns the di container
     *
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
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

    protected function initializePlugins()
    {
        $initializer = new PluginInitializer(
            $this->connection,
            $this->config['plugin_directories']['ShopwarePlugins']
        );

        $this->plugins = $initializer->initializePlugins();

        $this->pluginHash = $this->createPluginHash($this->plugins);
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
        if (!defined('_MPDF_TEMP_PATH')) {
            define('_MPDF_TEMP_PATH', $this->getCacheDir() . '/mpdf/tmp/');
        }

        if (!defined('_MPDF_TTFONTDATAPATH')) {
            define('_MPDF_TTFONTDATAPATH', $this->getCacheDir() . '/mpdf/ttfontdata/');
        }
    }

    /**
     * Creates a new instance of the Shopware application
     */
    protected function initializeShopware()
    {
        $this->shopware = new \Shopware($this->container);
        $this->container->setApplication($this->shopware);
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
        $this->container->set('db_connection', $this->connection);
    }

    /**
     * @return string
     */
    protected function getConfigPath()
    {
        return __DIR__ . '/Configs/Default.php';
    }

    /**
     * Dumps the service container to PHP code in the cache.
     *
     * @param ConfigCache      $cache     The config cache
     * @param ContainerBuilder $container The service container
     * @param string           $class     The name of the class to generate
     * @param string           $baseClass The name of the container's base class
     */
    protected function dumpContainer(ConfigCache $cache, ContainerBuilder $container, $class, $baseClass)
    {
        // cache the container
        $dumper = new PhpDumper($container);

        $content = $dumper->dump(['class' => $class, 'base_class' => $baseClass]);

        if (!$this->debug) {
            $content = SymfonyKernel::stripComments($content);
        }

        $cache->write($content, $container->getResources());
    }

    /**
     * Builds the service container.
     *
     * @throws \RuntimeException
     *
     * @return ContainerBuilder The compiled service container
     */
    protected function buildContainer()
    {
        $runtimeDirectories = [
            'cache' => $this->getCacheDir(),
            'coreCache' => $this->config['cache']['backendOptions']['cache_dir'],
            'mpdfTemp' => _MPDF_TEMP_PATH,
            'mpdfFontData' => _MPDF_TTFONTDATAPATH,
            'logs' => $this->getLogDir(),
        ];

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
        $this->prepareContainer($container);

        return $container;
    }

    /**
     * Prepares the ContainerBuilder before it is compiled.
     *
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    protected function prepareContainer(ContainerBuilder $container)
    {
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
        $loader->load('FormBundle/services.xml');
        $loader->load('AccountBundle/services.xml');
        $loader->load('AttributeBundle/services.xml');
        $loader->load('EmotionBundle/services.xml');
        $loader->load('SearchBundleES/services.xml');
        $loader->load('CustomerSearchBundle/services.xml');

        if (is_file($file = __DIR__ . '/Components/DependencyInjection/services_local.xml')) {
            $loader->load($file);
        }

        $this->addShopwareConfig($container, 'shopware', $this->config);
        $this->addResources($container);

        $container->addCompilerPass(new EventListenerCompilerPass(), PassConfig::TYPE_BEFORE_REMOVING);
        $container->addCompilerPass(new EventSubscriberCompilerPass(), PassConfig::TYPE_BEFORE_REMOVING);
        $container->addCompilerPass(new DoctrineEventSubscriberCompilerPass());
        $container->addCompilerPass(new DBALCompilerPass());
        $container->addCompilerPass(new CriteriaRequestHandlerCompilerPass());
        $container->addCompilerPass(new MappingCompilerPass());
        $container->addCompilerPass(new SynchronizerCompilerPass());
        $container->addCompilerPass(new DataIndexerCompilerPass());
        $container->addCompilerPass(new SettingsCompilerPass());
        $container->addCompilerPass(new FormPass());
        $container->addCompilerPass(new AddConstraintValidatorsPass());
        $container->addCompilerPass(new SearchRepositoryCompilerPass());
        $container->addCompilerPass(new AddConsoleCommandPass());
        $container->addCompilerPass(new AddCaptchaCompilerPass());
        $container->addCompilerPass(new EmotionComponentHandlerCompilerPass());
        $container->addCompilerPass(new MediaAdapterCompilerPass());
        $container->addCompilerPass(new MediaOptimizerCompilerPass());
        $container->addCompilerPass(new SearchHandlerCompilerPass());
        $container->addCompilerPass(new HandlerRegistryCompilerPass());

        $this->loadPlugins($container);
    }

    /**
     * Adds all shopware configuration as di container parameter.
     * Each shopware configuration has the alias "shopware."
     *
     * @param \Shopware\Components\DependencyInjection\Container|\Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param string                                                                                                     $alias
     * @param array|string                                                                                               $options
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
        return [
            'kernel.root_dir' => $this->getRootDir(),
            'kernel.environment' => $this->environment,
            'kernel.debug' => $this->debug,
            'kernel.name' => $this->name,
            'kernel.cache_dir' => $this->getCacheDir(),
            'kernel.logs_dir' => $this->getLogDir(),
            'kernel.charset' => 'UTF-8',
            'kernel.container_class' => $this->getContainerClass(),
        ];
    }

    /**
     * Gets the container class.
     *
     * @return string The container class
     */
    protected function getContainerClass()
    {
        return $this->name . ucfirst($this->environment) . $this->pluginHash . ($this->debug ? 'Debug' : '') . 'ProjectContainer';
    }

    /**
     * Returns a hash containing the plugin names
     *
     * @param Plugin[] $plugins
     *
     * @return string
     */
    private function createPluginHash(array $plugins)
    {
        $string = '';
        foreach ($plugins as $plugin) {
            $string .= $plugin->getPath() . $plugin->getName();
        }

        return sha1($string);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function loadPlugins(ContainerBuilder $container)
    {
        if (count($this->plugins) === 0) {
            return;
        }

        $activePlugins = [];
        foreach ($this->plugins as $plugin) {
            if (!$plugin->isActive()) {
                continue;
            }

            $container->addObjectResource($plugin);
            $plugin->build($container);
            $activePlugins[] = $plugin;
        }

        $container->addCompilerPass(new RegisterControllerCompilerPass($activePlugins));
    }
}
