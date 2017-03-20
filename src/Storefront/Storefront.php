<?php

namespace Shopware\Storefront;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Asset\StringAsset;
use Shopware\Storefront\Component\Theme;
use Shopware\Storefront\Component\Theme\LessVariablesCompilerPass;
use Shopware\Storefront\Component\Theme\SourceMapCompilerPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Finder\Finder;

class Storefront extends Theme
{
    protected $name = 'Storefront';

    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/DependencyInjection/'));
        $loader->load('services.xml');

        $this->registerCompilerPasses($container);
        $this->registerNamedAssets($container);
    }

    private function registerCompilerPasses(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new LessVariablesCompilerPass());

        if ($container->getParameter('kernel.debug')) {
            $container->addCompilerPass(new SourceMapCompilerPass());
        }
    }

    private function registerNamedAssets(ContainerBuilder $container): void
    {
        $this->registerNamedJavascripts($container);
        $this->registerNamedStylesheets($container);
    }

    private function registerNamedJavascripts(ContainerBuilder $container): void
    {
        $activePlugins = $container->getParameter('kernel.active_plugins');

        $paths = array_column($activePlugins, 'path');
        $paths = array_map(
            function (string $path) {
                return $path . '/Resources/public';
            },
            $paths
        );

        $paths = array_filter($paths, function ($path) {
            return file_exists($path);
        });

        $collection = new AssetCollection();

        if (count($paths)) {
            $finder = new Finder();
            $files = $finder->files()->in($paths)->name('*.js')->getIterator();

            /** @var \SplFileInfo $file */
            foreach ($files as $file) {
                $collection->add(new FileAsset($file->getRealPath()));
            }
        }

        $definition = new Definition(StringAsset::class, [$collection->dump()]);
        $definition->addTag('assetic.asset', ['alias' => 'plugin_javascripts', 'output' => 'js/plugin.js']);
        $definition->addMethodCall('setLastModified', [$collection->getLastModified()]);
        $definition->addMethodCall('setTargetPath', ['js/plugin.js']);

        $container->setDefinition('shopware.storefront.theme.plugin_javascripts', $definition);
    }

    private function registerNamedStylesheets(ContainerBuilder $container): void
    {
        $collection = new AssetCollection();
        $activePlugins = $container->getParameter('kernel.active_plugins');

        foreach ($activePlugins as $plugin) {
            $lessFile = $plugin['path'] . '/Resources/public/less/all.less';
            if (!file_exists($lessFile)) {
                continue;
            }

            $collection->add(new FileAsset($lessFile));
        }

        $definition = new Definition(StringAsset::class, [$collection->dump()]);
        $definition->addTag('assetic.asset', ['alias' => 'plugin_stylesheets', 'output' => 'css/plugin.css']);
        $definition->addMethodCall('setLastModified', [$collection->getLastModified()]);
        $definition->addMethodCall('setTargetPath', ['css/plugin.css']);

        $container->setDefinition('shopware.storefront.theme.plugin_stylesheets', $definition);
    }
}
