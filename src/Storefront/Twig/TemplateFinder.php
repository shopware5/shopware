<?php

namespace Shopware\Storefront\Twig;

use AppKernel;
use Shopware\Storefront\Component\Theme;
use Symfony\Bundle\TwigBundle\Loader\FilesystemLoader;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class TemplateFinder
{
    /**
     * @var array
     */
    private $directories = [];

    /**
     * @var FilesystemLoader
     */
    private $loader;

    /**
     * @var array[]
     */
    private $queue = [];

    /**
     * @param AppKernel $kernel
     * @param FilesystemLoader $loader
     */
    public function __construct(AppKernel $kernel, FilesystemLoader $loader)
    {
        $this->loader = $loader;

        array_map([$this, 'addBundle'], $kernel->getPlugins()->getPlugins());
        array_map([$this, 'addTheme'], $kernel->getThemes());

        $this->directories[] = '@Storefront';
    }

    public function addBundle(BundleInterface $bundle): void
    {
        $directory = $bundle->getPath() . '/Resources/views/';
        if (!file_exists($directory)) {
            return;
        }

        $this->loader->addPath($directory, $bundle->getName());
        $this->directories[] = '@' . $bundle->getName();
    }

    public function addTheme(Theme $theme): void
    {
        $directory = $theme->getPath() . '/Resources/views/';
        if (!file_exists($directory)) {
            return;
        }

        $this->loader->addPath($directory, $theme->getName());
        $this->directories[] = '@' . $theme->getName();
    }

    /**
     * @throws \Twig_Error_Loader
     */
    public function find(string $template, $wholeInheritance = false): string
    {
        if (!$wholeInheritance && array_key_exists($template, $this->queue)) {
            $queue = $this->queue[$template];
        } else {
            $queue = $this->queue[$template] = $this->directories;
        }

        foreach ($queue as $index => $prefix) {
            $name = $prefix . '/' . $template;

            unset($this->queue[$template][$index]);

            if ($this->loader->exists($name)) {
                return $name;
            }
        }

        throw new \Twig_Error_Loader(sprintf('Unable to load template "%s".', $template));
    }
}