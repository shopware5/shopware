<?php

namespace Shopware\Storefront\Component\Theme;

use Assetic\Asset\AssetInterface;
use Shopware\Framework\Component\Plugin;

class LessphpFilter extends \Assetic\Filter\LessphpFilter
{
    /**
     * @var \AppKernel
     */
    private $kernel;

    public function __construct(\AppKernel $kernel)
    {
        $this->kernel = $kernel;
    }

    public function filterLoad(AssetInterface $asset): void
    {
        $this->dumpPluginLess();
        $this->dumpThemeConfiguration();

        if (!$this->kernel->isDebug()) {
            $this->setFormatter('compressed');
        }

        parent::filterLoad($asset);
    }

    private function dumpPluginLess(): void
    {
        $output = '// ' . date('Y-m-d H:i:s') . PHP_EOL;
        $allPath = '/Resources/public/less/all.less';
        $pluginImportTemplate = '@import (optional) "@{%s}%s";';

        /** @var Plugin $plugin */
        foreach ($this->kernel->getPlugins()->getPlugins() as $plugin) {
            $output .= sprintf($pluginImportTemplate, $plugin->getName(), $allPath) . PHP_EOL;
        }

        file_put_contents(
            $this->kernel->getCacheDir() . '/plugins.less',
            $output
        );
    }

    private function dumpThemeConfiguration(): void
    {
    }
}