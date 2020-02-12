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
use Shopware\Bundle\AccountBundle\AccountBundle;
use Shopware\Bundle\AttributeBundle\AttributeBundle;
use Shopware\Bundle\AttributeBundle\DependencyInjection\Compiler\StaticResourcesCompilerPass;
use Shopware\Bundle\BenchmarkBundle\BenchmarkBundle;
use Shopware\Bundle\BenchmarkBundle\DependencyInjection\Compiler\MatcherCompilerPass;
use Shopware\Bundle\ContentTypeBundle\ContentTypeBundle;
use Shopware\Bundle\ContentTypeBundle\DependencyInjection\RegisterDynamicController;
use Shopware\Bundle\ContentTypeBundle\DependencyInjection\RegisterFieldsCompilerPass;
use Shopware\Bundle\ContentTypeBundle\DependencyInjection\RegisterTypeRepositories;
use Shopware\Bundle\ControllerBundle\ControllerBundle;
use Shopware\Bundle\ControllerBundle\DependencyInjection\Compiler\ControllerCompilerPass;
use Shopware\Bundle\ControllerBundle\DependencyInjection\Compiler\RegisterControllerCompilerPass;
use Shopware\Bundle\CookieBundle\CookieBundle;
use Shopware\Bundle\CustomerSearchBundleDBAL\CustomerSearchBundleDBALBundle;
use Shopware\Bundle\EmotionBundle\EmotionBundle;
use Shopware\Bundle\EsBackendBundle\EsBackendBundle;
use Shopware\Bundle\ESIndexingBundle\DependencyInjection\CompilerPass\VersionCompilerPass;
use Shopware\Bundle\ESIndexingBundle\ESIndexingBundle;
use Shopware\Bundle\FormBundle\DependencyInjection\CompilerPass\AddConstraintValidatorsPass;
use Shopware\Bundle\FormBundle\DependencyInjection\CompilerPass\FormPass;
use Shopware\Bundle\FormBundle\FormBundle;
use Shopware\Bundle\MailBundle\MailBundle;
use Shopware\Bundle\MediaBundle\MediaBundle;
use Shopware\Bundle\PluginInstallerBundle\PluginInstallerBundle;
use Shopware\Bundle\PluginInstallerBundle\Service\PluginInitializer;
use Shopware\Bundle\SearchBundle\SearchBundle;
use Shopware\Bundle\SearchBundleDBAL\SearchBundleDBAL;
use Shopware\Bundle\SearchBundleES\SearchBundleES;
use Shopware\Bundle\SitemapBundle\SitemapBundle;
use Shopware\Bundle\StaticContentBundle\StaticContentBundle;
use Shopware\Bundle\StoreFrontBundle\StoreFrontBundle;
use Shopware\Components\ConfigLoader;
use Shopware\Components\DependencyInjection\Compiler\ConfigureApiResourcesPass;
use Shopware\Components\DependencyInjection\Compiler\ConfigureContainerAwareCommands;
use Shopware\Components\DependencyInjection\Compiler\DoctrineEventSubscriberCompilerPass;
use Shopware\Components\DependencyInjection\Compiler\EventListenerCompilerPass;
use Shopware\Components\DependencyInjection\Compiler\EventSubscriberCompilerPass;
use Shopware\Components\DependencyInjection\Compiler\LegacyApiResourcesPass;
use Shopware\Components\DependencyInjection\Compiler\PluginLoggerCompilerPass;
use Shopware\Components\DependencyInjection\Compiler\PluginResourceCompilerPass;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\DependencyInjection\LegacyPhpDumper;
use Shopware\Components\Plugin;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Console\DependencyInjection\AddConsoleCommandPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\DependencyInjection\MergeExtensionConfigurationPass;
use Symfony\Component\HttpKernel\DependencyInjection\RegisterControllerArgumentLocatorsPass;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel as SymfonyKernel;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Middleware class between the old Shopware bootstrap mechanism
 * and the Symfony Kernel handling
 */
class Kernel extends SymfonyKernel
{
    /**
     * Shopware Version definition. Is being replaced by the correct release information in release packages.
     * Is available in the DIC as parameter 'shopware.release.*' or a Struct containing all the parameters below.
     */
    protected $release = [
        'version' => '___VERSION___',
        'version_text' => '___VERSION_TEXT___',
        'revision' => '___REVISION___',
    ];

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
     * @var Container|null
     */
    protected $container;

    /**
     * @var string[]
     */
    private $activePlugins = [];

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
     *
     * @throws \Exception
     */
    public function __construct($environment, $debug)
    {
        parent::__construct($environment, $debug);

        if ($debug) {
            $this->startTime = microtime(true);
        }

        $this->initializeBundles();
        $this->initializeConfig();

        if (!empty($this->config['phpsettings'])) {
            $this->setPhpSettings($this->config['phpsettings']);
        }

        if ($trustedProxies = $this->config['trustedproxies']) {
            SymfonyRequest::setTrustedProxies($trustedProxies, $this->config['trustedheaderset']);
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
        if ($this->booted === false) {
            $this->boot();
        }

        /** @var \Enlight_Controller_Front $front */
        $front = $this->container->get('front');

        $enlightRequest = $this->transformSymfonyRequestToEnlightRequest($request);

        if ($front->Request() === null) {
            $front->setRequest($enlightRequest);
            $response = $front->dispatch();
        } else {
            $dispatcher = clone $front->Dispatcher();
            $response = clone $front->Response();

            $response->clearHeaders()
                ->clearBody();

            $response->setStatusCode(SymfonyResponse::HTTP_OK);
            $enlightRequest->setDispatched();
            $dispatcher->dispatch($enlightRequest, $response);
        }

        $response->prepare($request);

        return $response;
    }

    /**
     * @return EnlightRequest
     */
    public function transformSymfonyRequestToEnlightRequest(SymfonyRequest $request)
    {
        // Overwrite superglobals with state of the SymfonyRequest
        $request->overrideGlobals();

        $enlightRequest = EnlightRequest::createFromGlobals();
        $enlightRequest->setContent($request->getContent());
        $enlightRequest->setFiles($request->files->all());

        return $enlightRequest;
    }

    /**
     * Boots the Shopware and Symfony DI container
     *
     * @param bool $skipDatabase
     *
     * @throws \Exception
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

        foreach ($this->getBundles() as $bundle) {
            $bundle->setContainer($this->container);

            if ((!$bundle instanceof Plugin) || $bundle->isActive()) {
                $bundle->boot();
            }

            if (!$bundle instanceof Plugin) {
                continue;
            }

            if (!$bundle->isActive()) {
                continue;
            }

            $this->container->get('events')->addSubscriber($bundle);
        }

        $this->booted = true;
    }

    /**
     * @return Plugin[]
     */
    public function getPlugins()
    {
        return array_filter($this->bundles, function (BundleInterface $bundle) {
            return $bundle instanceof Plugin;
        });
    }

    /**
     * Sets the php settings from the config
     *
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
        return dirname(__DIR__, 2);
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->getRootDir() . '/var/cache/' . $this->environment . '_' . $this->release['revision'];
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

    /**
     * @return array
     */
    public function getRelease()
    {
        return $this->release;
    }

    /**
     * {@inheritdoc}
     */
    public function terminate(SymfonyRequest $request, SymfonyResponse $response)
    {
        if ($this->container && $this->container->initialized('events')) {
            $this->container->get('events')->notify(KernelEvents::TERMINATE, [
                'postResponseEvent' => new PostResponseEvent($this, $request, $response),
                'container' => $this->container,
            ]);
        }
    }

    public function getName(): string
    {
        return 'Shopware';
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $config = $this->config;
        $loader->load(static function (ContainerBuilder $containerBuilder) use ($config) {
            foreach ($config as $key => $values) {
                if ($containerBuilder->hasExtension($key)) {
                    $containerBuilder->loadFromExtension($key, $values);
                }
            }
        });
    }

    public function registerBundles(): array
    {
        return [
            new AccountBundle(),
            new AttributeBundle(),
            new BenchmarkBundle(),
            new CookieBundle(),
            new ContentTypeBundle(),
            new ControllerBundle(),
            new CustomerSearchBundleDBALBundle(),
            new EmotionBundle(),
            new EsBackendBundle(),
            new ESIndexingBundle(),
            new FormBundle(),
            new MailBundle(),
            new MediaBundle(),
            new PluginInstallerBundle(),
            new SearchBundle(),
            new SearchBundleDBAL(),
            new SearchBundleES(),
            new SitemapBundle(),
            new StaticContentBundle(),
            new StoreFrontBundle(),
        ];
    }

    protected function initializePlugins()
    {
        $initializer = new PluginInitializer(
            $this->connection,
            [
                'ShopwarePlugins' => $this->config['plugin_directories']['ShopwarePlugins'],
                'ProjectPlugins' => $this->config['plugin_directories']['ProjectPlugins'],
            ]
        );

        $plugins = $initializer->initializePlugins();

        /*
         * @deprecated since 5.5, is true by default since 5.6 will be removed in Shopware 5.7
         */
        if ($this->config['backward_compatibility']['predictable_plugin_order'] === true) {
            ksort($plugins);
        }

        $this->bundles = array_merge($this->bundles, $plugins);

        $this->activePlugins = $initializer->getActivePlugins();

        $this->pluginHash = $this->createPluginHash($this->bundles);
    }

    /**
     * Loads the shopware configuration, which will be injected into
     * the Shopware_Application.
     * The shopware configuration is required before the shopware application booted,
     * to pass the configuration to the Symfony di container.
     *
     * @throws \Exception
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
     *
     * @throws \Exception
     * @throws \RuntimeException
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

            $this->dumpContainer($cache, $container, $class, Container::class);
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
     *
     * @throws \RuntimeException
     */
    protected function dumpContainer(ConfigCache $cache, ContainerBuilder $container, $class, $baseClass)
    {
        // cache the container
        $dumper = new LegacyPhpDumper($container);

        $content = $dumper->dump(['class' => $class, 'base_class' => $baseClass]);

        if (!$this->debug) {
            $content = SymfonyKernel::stripComments($content);
        }

        $cache->write($content, $container->getResources());
    }

    /**
     * Builds the service container.
     *
     * @throws \Exception
     * @throws \RuntimeException
     *
     * @return ContainerBuilder The compiled service container
     */
    protected function buildContainer()
    {
        $runtimeDirectories = [
            'cache' => $this->getCacheDir(),
            'coreCache' => $this->config['cache']['backendOptions']['cache_dir'],
            'mpdfTemp' => $this->config['mpdf']['defaultConfig']['tempDir'],
            'mpdfFontData' => $this->config['mpdf']['defaultConfig']['tempDir'] . '/ttfontdata',
            'logs' => $this->getLogDir(),
        ];

        foreach ($runtimeDirectories as $name => $dir) {
            if (!is_dir($dir)) {
                if (@mkdir($dir, 0777, true) === false && !is_dir($dir)) {
                    throw new \RuntimeException(sprintf("Unable to create the %s directory (%s)\n", $name, $dir));
                }
            } elseif (!is_writable($dir)) {
                throw new \RuntimeException(sprintf("Unable to write in the %s directory (%s)\n", $name, $dir));
            }
        }

        $container = $this->getContainerBuilder();
        $container->addObjectResource($this);
        $this->prepareContainer($container);

        if (null !== $cont = $this->registerContainerConfiguration($this->getContainerLoader($container))) {
            $container->merge($cont);
        }

        return $container;
    }

    /**
     * Prepares the ContainerBuilder before it is compiled.
     *
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \Exception
     */
    protected function prepareContainer(ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/Components/DependencyInjection/'));
        $loader->load('services.xml');
        $loader->load('theme.xml');
        $loader->load('logger.xml');
        $loader->load('commands.xml');

        if (is_file($file = __DIR__ . '/Components/DependencyInjection/services_local.xml')) {
            $loader->load($file);
        }

        $this->addShopwareConfig($container, 'shopware', $this->config);
        $this->addResources($container);

        $container->addCompilerPass(new EventListenerCompilerPass(), PassConfig::TYPE_BEFORE_REMOVING);
        $container->addCompilerPass(new EventSubscriberCompilerPass(), PassConfig::TYPE_BEFORE_REMOVING);
        $container->addCompilerPass(new DoctrineEventSubscriberCompilerPass());
        $container->addCompilerPass(new FormPass());
        $container->addCompilerPass(new AddConstraintValidatorsPass());
        $container->addCompilerPass(new StaticResourcesCompilerPass());
        $container->addCompilerPass(new AddConsoleCommandPass());
        $container->addCompilerPass(new ConfigureContainerAwareCommands());
        $container->addCompilerPass(new MatcherCompilerPass());
        $container->addCompilerPass(new LegacyApiResourcesPass());
        $container->addCompilerPass(new ConfigureApiResourcesPass(), PassConfig::TYPE_OPTIMIZE, -500);
        $container->addCompilerPass(new RegisterFieldsCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 500);
        $container->addCompilerPass(new RegisterDynamicController());
        $container->addCompilerPass(new RegisterTypeRepositories());
        $container->addCompilerPass(new ControllerCompilerPass());
        $container->addCompilerPass(new RegisterControllerArgumentLocatorsPass('argument_resolver.service', 'shopware.controller'));
        $container->addCompilerPass(new VersionCompilerPass());

        $container->setParameter('active_plugins', $this->activePlugins);

        $this->loadPlugins($container);
    }

    /**
     * Adds all shopware configuration as di container parameter.
     * Each shopware configuration has the alias "shopware."
     *
     * @param string $alias
     * @param array  $options
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
        $bundles = [];
        $bundlesMetadata = [];

        foreach ($this->bundles as $name => $bundle) {
            $bundles[$name] = \get_class($bundle);
            $bundlesMetadata[$name] = [
                'parent' => $bundle->getParent(),
                'path' => $bundle->getPath(),
                'namespace' => $bundle->getNamespace(),
            ];
        }

        return [
            'kernel.root_dir' => $this->getRootDir(),
            'kernel.project_dir' => realpath($this->getProjectDir()) ?: $this->getProjectDir(),
            'kernel.environment' => $this->environment,
            'kernel.debug' => $this->debug,
            'kernel.name' => $this->name,
            'kernel.cache_dir' => $this->getCacheDir(),
            'kernel.logs_dir' => $this->getLogDir(),
            'kernel.bundles' => $bundles,
            'kernel.bundles_metadata' => $bundlesMetadata,
            'kernel.charset' => 'UTF-8',
            'kernel.container_class' => $this->getContainerClass(),
            'shopware.release.version' => $this->release['version'],
            'shopware.release.version_text' => $this->release['version_text'],
            'shopware.release.revision' => $this->release['revision'],
            'kernel.default_error_level' => $this->config['logger']['level'],
            'shopware.bundle.content_type.types' => $this->loadContentTypes(),
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
     * @param array<int, BundleInterface> $plugins
     */
    private function createPluginHash(array $plugins): string
    {
        $string = '';
        foreach ($plugins as $plugin) {
            $string .= $plugin->getPath() . $plugin->getName();
        }

        return sha1($string);
    }

    private function loadPlugins(ContainerBuilder $container): void
    {
        if (count($this->bundles) === 0) {
            return;
        }

        $activePlugins = [];
        foreach ($this->getBundles() as $bundle) {
            if ($bundle instanceof Plugin && !$bundle->isActive()) {
                continue;
            }

            if ($extension = $bundle->getContainerExtension()) {
                $container->registerExtension($extension);
            }

            $container->addObjectResource($bundle);
            $bundle->build($container);

            if ($bundle instanceof Plugin) {
                $activePlugins[] = $bundle;
            }
        }

        $container->addCompilerPass(new RegisterControllerCompilerPass($activePlugins));
        $container->addCompilerPass(new PluginLoggerCompilerPass($activePlugins));
        $container->addCompilerPass(new PluginResourceCompilerPass($activePlugins));

        $extensions = [];

        foreach ($container->getExtensions() as $extension) {
            $extensions[] = $extension->getAlias();
        }
        // ensure these extensions are implicitly loaded
        $container->getCompilerPassConfig()->setMergePass(new MergeExtensionConfigurationPass($extensions));
    }

    private function loadContentTypes(): array
    {
        if ($this->connection === null) {
            return [];
        }

        try {
            $contentTypes = $this->connection->query('SELECT internalName, config FROM s_content_types');
        } catch (\Exception $e) {
            return [];
        }

        $result = [];

        try {
            foreach ($contentTypes->fetchAll(\PDO::FETCH_KEY_PAIR) as $key => $type) {
                $result[$key] = json_decode($type, true);
            }
        } catch (\Exception $e) {
        }

        return $result;
    }
}
