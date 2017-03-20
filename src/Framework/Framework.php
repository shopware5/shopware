<?php

namespace Shopware\Framework;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Asset\StringAsset;
use Shopware\Framework\Component\Theme\AsseticAssetsCompilerPass;
use Shopware\Framework\Component\Theme\JavascriptCompilerPass;
use Shopware\Framework\Component\Theme\LessVariablesCompilerPass;
use Shopware\Framework\Component\Theme\SourceMapCompilerPass;
use Shopware\Framework\DependencyInjection\FrameworkExtension;
use Shopware\Framework\Doctrine\BridgeDatabaseCompilerPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class Framework extends Bundle
{
    const VERSION = '___VERSION___';
    const VERSION_TEXT = '___VERSION_TEXT___';
    const REVISION = '___REVISION___';

    protected $name = 'Shopware';

    /**
     * @inheritDoc
     */
    public function getContainerExtension()
    {
        return new FrameworkExtension();
    }

    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/DependencyInjection/'));
        $loader->load('services.xml');

        $container->addCompilerPass(new BridgeDatabaseCompilerPass());
    }
}