<?php

namespace Shopware\Storefront\Theme;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SourceMapCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $options = [
            'sourceMap' => true,
            'sourceMapWriteTo' => $container->getParameter('kernel.root_dir') . '/../web/css/app.css.map',
            'sourceMapURL' => '/css/app.css.map',
            'outputSourceFiles' => true,
        ];

        $lessphpFilter = $container->getDefinition('assetic.filter.lessphp');
        $lessphpFilter->addMethodCall('setOptions', [$options]);
    }
}