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

namespace Shopware\Components\DependencyInjection\Compiler;

use Shopware\Components\Plugin;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class PluginResourceCompilerPass implements CompilerPassInterface
{
    /**
     * @var Plugin[]
     */
    private $plugins;

    public function __construct(array $plugins)
    {
        $this->plugins = $plugins;
    }

    public function process(ContainerBuilder $container): void
    {
        foreach ($this->plugins as $plugin) {
            $definition = new Definition(Plugin\ResourceSubscriber::class);
            $definition->setPublic(true);
            $definition->setArguments([
                $plugin->getPath(),
            ]);

            if (is_dir($plugin->getPath() . '/Resources/frontend/css')) {
                $definition->addTag('shopware.event_listener', [
                    'event' => 'Theme_Compiler_Collect_Plugin_Css',
                    'method' => 'onCollectCss',
                ]);
            }

            if (is_dir($plugin->getPath() . '/Resources/frontend/less')) {
                $definition->addTag('shopware.event_listener', [
                    'event' => 'Theme_Compiler_Collect_Plugin_Less',
                    'method' => 'onCollectLess',
                ]);
            }

            if (is_dir($plugin->getPath() . '/Resources/frontend/js')) {
                $definition->addTag('shopware.event_listener', [
                    'event' => 'Theme_Compiler_Collect_Plugin_JavaScript',
                    'method' => 'onCollectJavascript',
                ]);
            }

            if ($plugin->hasAutoloadViews() && is_dir($plugin->getPath() . '/Resources/views')) {
                $definition->addTag('shopware.event_listener', [
                    'event' => 'Theme_Inheritance_Template_Directories_Collected',
                    'method' => 'onRegisterTemplate',
                ]);

                $definition->addTag('shopware.event_listener', [
                    'event' => 'Enlight_Controller_Action_PreDispatch_Backend',
                    'method' => 'onRegisterControllerTemplate',
                ]);
            }

            if (!$definition->hasTag('shopware.event_listener')) {
                continue;
            }

            $container->setDefinition($plugin->getContainerPrefix() . '.internal.resource_subscriber', $definition);
        }
    }
}
