<?php

use Shopware\Framework\Plugin\Plugin;
use Shopware\Storefront\Theme\Theme;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    /**
     * @var PDO
     */
    private static $connection;

    /**
     * @var PluginCollection
     */
    private $pluginCollection;

    /**
     * @var array
     */
    private $themes = [];

    /**
     * @inheritDoc
     */
    public function __construct($environment, $debug)
    {
        parent::__construct($environment, $debug);

        $this->pluginCollection = new PluginCollection();
    }


    public function boot($withPlugins = true)
    {
        if (true === $this->booted) {
            return;
        }

        if ($withPlugins) {
            $this->initializePluginSystem();
        }

        // init bundles
        $this->initializeBundles();

        // init container
        $this->initializeContainer();

        foreach ($this->getBundles() as $bundle) {
            $bundle->setContainer($this->container);
            $bundle->boot();
        }

        $this->container->set('db_connection', self::getConnection());
        $this->container->set('plugin_collection', $this->getPlugins());

        $this->booted = true;
    }

    public function getPlugins(): PluginCollection
    {
        return $this->pluginCollection;
    }

    /**
     * @return Theme[]
     */
    public function getThemes(): array
    {
        return $this->themes;
    }

    public static function getConnection(): ?PDO
    {
        return self::$connection;
    }

    public function registerBundles()
    {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Shopware\Framework\Framework(),
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        $bundles = array_merge($bundles, $this->getPlugins()->getPlugins());
        $bundles = array_merge($bundles, $this->themes);

        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return $this->getRootDir() . '/../var/cache/' . $this->getEnvironment() . '_' . \Shopware\Framework\Framework::REVISION;
    }

    public function getLogDir()
    {
        return $this->getRootDir() . '/../var/logs';
    }

    public function getPluginDir()
    {
        return $this->getRootDir() . '/../custom/plugins';
    }

    /**
     * @inheritDoc
     */
    protected function getKernelParameters()
    {
        $parameters = parent::getKernelParameters();

        $activePluginMeta = [];

        foreach ($this->getPlugins()->getPlugins() as $namespace => $plugin) {
            if (!$plugin->isActive()) {
                continue;
            }

            $activePluginMeta[$plugin->getName()] = [
                'name' => $plugin->getName(),
                'path' => $plugin->getPath(),
            ];
        }


        return array_merge($parameters, [
            'kernel.plugin_dir' => $this->getPluginDir(),
            'kernel.active_plugins' => $activePluginMeta
        ]);
    }


    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir() . '/config/config_' . $this->getEnvironment() . '.yml');
    }

    protected function getContainerClass()
    {
        $pluginHash = sha1(implode('', array_keys($this->pluginCollection->getPlugins())));

        return $this->name . ucfirst($this->environment) . $pluginHash . ($this->debug ? 'Debug' : '') . 'ProjectContainer';
    }

    protected function initializeConnection()
    {
        self::$connection = DatabaseConnector::connect(
            $this->getRootDir(),
            $this->getEnvironment()
        );
    }

    protected function initializePlugins()
    {
        $stmt = self::$connection->query('SELECT `name` FROM `s_core_plugins` WHERE `namespace` LIKE "ShopwarePlugins" AND `active` = 1 AND `installation_date` IS NOT NULL');
        $activePlugins = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        $finder = new Finder();
        $iterator = $finder->directories()->depth(0)->in($this->getPluginDir())->getIterator();

        foreach ($iterator as $pluginDir) {
            $pluginName = $pluginDir->getFilename();
            $pluginFile = $pluginDir->getPath() . '/' . $pluginName . '/' . $pluginName . '.php';
            if (!is_file($pluginFile)) {
                continue;
            }

            $namespace = $pluginName;
            $className = '\\' . $namespace . '\\' . $pluginName;

            if (!class_exists($className)) {
                throw new \RuntimeException(
                    sprintf('Unable to load class %s for plugin %s in file %s', $className, $pluginName, $pluginFile)
                );
            }

            if (false === in_array($pluginName, $activePlugins, true)) {
                continue;
            }

            /** @var Plugin $plugin */
            $plugin = new $className(true);

            if (!$plugin instanceof Plugin) {
                throw new \RuntimeException(
                    sprintf('Class %s must extend %s in file %s', get_class($plugin), Plugin::class, $pluginFile)
                );
            }

            $this->pluginCollection->add($plugin);
        }
    }

    protected function initializeThemes()
    {
        return $this->themes = [
            new Shopware\Storefront\Storefront()
        ];
    }

    private function initializePluginSystem()
    {
        $this->initializeConnection();
        $this->initializePlugins();
        $this->initializeThemes();
    }
}