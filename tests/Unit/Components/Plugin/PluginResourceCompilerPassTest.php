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

namespace Shopware\Tests\Unit\Components\Plugin;

use PHPUnit\Framework\TestCase;
use Shopware\Components\DependencyInjection\Compiler\PluginResourceCompilerPass;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\ResourceSubscriber;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PluginResourceCompilerPassTest extends TestCase
{
    public function testOnEmptyPlugin(): void
    {
        $stub = $this->createMock(Plugin::class);
        $stub->method('getPath')
            ->willReturn(__DIR__ . '/examples/EmptyPlugin');

        $stub
            ->method('getContainerPrefix')
            ->willReturn('foo');

        $container = new ContainerBuilder();
        $container->addCompilerPass(new PluginResourceCompilerPass([$stub]));
        $container->compile();

        // Container it self
        static::assertCount(1, $container->getDefinitions());
    }

    public function testWithDisabledAutoload(): void
    {
        $stub = $this->createMock(Plugin::class);
        $stub->method('getPath')
            ->willReturn(__DIR__ . '/examples/TestPlugin');

        $stub
            ->method('getContainerPrefix')
            ->willReturn('foo');

        $container = new ContainerBuilder();
        $container->addCompilerPass(new PluginResourceCompilerPass([$stub]));
        $container->compile();

        static::assertCount(2, $container->getDefinitions());

        $definition = $container->getDefinition('foo.internal.resource_subscriber');

        static::assertTrue($definition->isPublic());

        $tags = $definition->getTag('shopware.event_listener');

        static::assertNotEmpty($tags);
        // JS, CSS, LESS
        static::assertCount(3, $tags);
    }

    public function testWithEnabledAutoload(): void
    {
        $stub = $this->createMock(Plugin::class);
        $stub->method('getPath')
            ->willReturn(__DIR__ . '/examples/TestPlugin');

        $stub
            ->method('hasAutoloadViews')
            ->willReturn(true);

        $stub
            ->method('getContainerPrefix')
            ->willReturn('foo');

        $container = new ContainerBuilder();
        $container->addCompilerPass(new PluginResourceCompilerPass([$stub]));
        $container->compile();

        static::assertCount(2, $container->getDefinitions());

        $definition = $container->getDefinition('foo.internal.resource_subscriber');

        static::assertTrue($definition->isPublic());

        $tags = $definition->getTag('shopware.event_listener');

        static::assertNotEmpty($tags);

        // JS, CSS, LESS, FRONTEND TPL, BACKEND TPL
        static::assertCount(5, $tags);
    }

    public function testTwoPlugins(): void
    {
        $emptyPlugin = $this->createMock(Plugin::class);
        $emptyPlugin->method('getPath')
            ->willReturn(__DIR__ . '/examples/EmptyPlugin');

        $emptyPlugin
            ->method('hasAutoloadViews')
            ->willReturn(true);

        $emptyPlugin
            ->method('getContainerPrefix')
            ->willReturn('empty_plugin');

        $filledPlugin = $this->createMock(Plugin::class);
        $filledPlugin->method('getPath')
            ->willReturn(__DIR__ . '/examples/TestPlugin');

        $filledPlugin
            ->method('hasAutoloadViews')
            ->willReturn(true);

        $filledPlugin
            ->method('getContainerPrefix')
            ->willReturn('filled_plugin');

        $container = new ContainerBuilder();
        $container->addCompilerPass(new PluginResourceCompilerPass([$emptyPlugin, $filledPlugin]));
        $container->compile();

        static::assertCount(2, $container->getDefinitions());

        static::assertInstanceOf(ResourceSubscriber::class, $container->get('filled_plugin.internal.resource_subscriber'));
    }
}
