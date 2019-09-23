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

namespace Shopware\Bundle\ControllerBundle\DependencyInjection\Compiler;

use Shopware\Bundle\ControllerBundle\Listener\ControllerPathListener;
use Shopware\Components\Plugin;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Finder\Finder;

class RegisterControllerCompilerPass implements CompilerPassInterface
{
    const MODULES = ['Backend', 'Frontend', 'Widgets', 'Api'];

    /**
     * @var Plugin[]
     */
    private $plugins;

    /**
     * @param Plugin[] $plugins
     */
    public function __construct(array $plugins)
    {
        $this->plugins = $plugins;
    }

    public function process(ContainerBuilder $container)
    {
        $paths = $this->collectControllerPaths($this->plugins);
        if (count($paths) === 0) {
            return;
        }

        $controllers = $this->getControllers($paths);
        if (count($controllers) === 0) {
            return;
        }

        $listener = new Definition(ControllerPathListener::class);

        foreach ($controllers as $eventName => $file) {
            $listener
                ->addTag(
                    'shopware.event_listener',
                    [
                        'event' => $eventName,
                        'method' => 'getControllerPath',
                        'priority' => 500,
                    ]
                )
                ->addMethodCall('addController', [$eventName, $file]);
        }

        $container->setDefinition('shopware.generic_controller_listener', $listener);
    }

    /**
     * @param string[] $paths
     *
     * @return string[]
     */
    public function getControllers($paths)
    {
        $controllers = [];
        $finder = new Finder();
        $finder
            ->in($paths)
            ->files()
            ->name('*.php');

        foreach (self::MODULES as $module) {
            $finder->path($module);
        }

        foreach ($finder as $file) {
            $eventName = $this->buildEventName(
               $file->getPathInfo()->getBasename(),
               $file->getBasename('.php')
           );
            $controllers[$eventName] = $file->getPathname();
        }

        return $controllers;
    }

    /**
     * @param Plugin[] $actives
     *
     * @return string[]
     */
    private function collectControllerPaths($actives)
    {
        $controllerPaths = array_map(function (Plugin $plugin) {
            if (is_dir($plugin->getPath() . '/Controllers')) {
                return $plugin->getPath() . '/Controllers';
            }

            return null;
        }, $actives);

        return array_filter($controllerPaths);
    }

    /**
     * @param string $module
     * @param string $controller
     *
     * @return string
     */
    private function buildEventName($module, $controller)
    {
        return sprintf(
            'Enlight_Controller_Dispatcher_ControllerPath_%s_%s',
            $module,
            $controller
        );
    }
}
