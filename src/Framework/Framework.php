<?php
declare(strict_types=1);

namespace Shopware\Framework;

use Shopware\Framework\DependencyInjection\FrameworkExtension;
use Shopware\Framework\Doctrine\BridgeDatabaseCompilerPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
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
    public function getContainerExtension(): Extension
    {
        return new FrameworkExtension();
    }

    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/DependencyInjection/'));
        $loader->load('services.xml');

        $container->addCompilerPass(new BridgeDatabaseCompilerPass());
    }
}