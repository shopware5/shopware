<?php

namespace Shopware\Components\Theme\EventListener;

use Shopware\Components\DependencyInjection\Container;

class BackendTheme
{
    /**
     * @var \Shopware\Components\DependencyInjection\Container
     */
    private $container;

    function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param \Enlight_Controller_EventArgs $args
     */
    public function registerBackendTheme(\Enlight_Controller_EventArgs $args)
    {
        if ($args->getRequest()->getModuleName() != 'backend') {
            return;
        }

        $directory = $this->container->get('theme_path_resolver')->getExtJsThemeDirectory();

        $this->container->get('template')->setTemplateDir(array(
            'backend' => $directory,
            'include_dir' => '.'
        ));
    }
}