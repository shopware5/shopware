<?php

namespace Shopware\Bundle\ControllerBundle\DependencyInjection\Compiler;

use Shopware\Bundle\ControllerBundle\Finder\ControllerFinder;
use Shopware\Bundle\ControllerBundle\Listener\ControllerPathListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class RegisterControllerCompilerPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $path;

    /**
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $finder = new ControllerFinder();
        $controllers = $finder->getControllers($this->path);

        if (count($controllers) === 0) {
            return;
        }

        $listener = new Definition(ControllerPathListener::class);

        foreach ($controllers as $controller) {
            $listener
                ->addTag(
                    'shopware.event_listener',
                    [
                        'event'  => $controller->getEvent(),
                        'method' => 'getControllerPath',
                    ]
                )
                ->addMethodCall('addController', [$controller->getEvent(), $controller->getPath()]);
        }

        $container->setDefinition('shopware.generic_controller_listener.' . uniqid(), $listener);
    }
}
