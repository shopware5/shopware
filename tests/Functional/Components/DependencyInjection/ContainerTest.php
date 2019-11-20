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

namespace Shopware\Tests\Functional\Components\DependencyInjection;

use Enlight_Event_EventArgs;
use PHPUnit\Framework\TestCase;
use Shopware\Components\DependencyInjection\Container;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class ContainerTest extends TestCase
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var bool
     */
    private $eventHasBeenFired;

    public function setUp(): void
    {
        $this->eventHasBeenFired = false;
        $this->container = Shopware()->Container();
    }

    public function testFetchingAliasFiresDefaultCreationEvents(): void
    {
        $this->attachEvent();

        $this->container->reset(\Shopware\Components\Cart\NetRounding\RoundLineAfterQuantity::class);

        $aliasedService = $this->container->get(\Shopware\Components\Cart\NetRounding\RoundLineAfterQuantity::class);

        static::assertInstanceOf(\Shopware\Components\Cart\NetRounding\RoundLineAfterQuantity::class, $aliasedService);
        static::assertTrue($this->eventHasBeenFired);
    }

    public function testResettingServiceFiresEventsAgain(): void
    {
        $this->attachEvent();

        $this->container->reset('shopware.cart.net_rounding.after_quantity');
        $this->container->get('shopware.cart.net_rounding.after_quantity');
        static::assertTrue($this->eventHasBeenFired);

        $this->eventHasBeenFired = false;
        $this->container->reset('shopware.cart.net_rounding.after_quantity');

        $this->container->get('shopware.cart.net_rounding.after_quantity');
        static::assertTrue($this->eventHasBeenFired);
    }

    public function testResettingAliasResetsOriginalService(): void
    {
        $this->attachEvent();

        $this->container->reset(\Shopware\Components\Cart\NetRounding\RoundLineAfterQuantity::class);
        $this->container->get(\Shopware\Components\Cart\NetRounding\RoundLineAfterQuantity::class);
        static::assertTrue($this->eventHasBeenFired);

        $this->eventHasBeenFired = false;
        $this->container->reset(\Shopware\Components\Cart\NetRounding\RoundLineAfterQuantity::class);

        $this->container->get(\Shopware\Components\Cart\NetRounding\RoundLineAfterQuantity::class);
        static::assertTrue($this->eventHasBeenFired);
    }

    public function testChangingServiceInAnEvent()
    {
        $this->attachEvent('listenerCallbackReturningOtherClass');

        $this->container->reset(\Shopware\Components\Cart\NetRounding\RoundLineAfterQuantity::class);
        $service = $this->container->get(\Shopware\Components\Cart\NetRounding\RoundLineAfterQuantity::class);

        static::assertTrue($this->eventHasBeenFired);
        static::assertInstanceOf(OriginalService::class, $service);

        $this->container->reset(\Shopware\Components\Cart\NetRounding\RoundLineAfterQuantity::class);
    }

    public function listenerCallback(Enlight_Event_EventArgs $args): void
    {
        $this->eventHasBeenFired = true;
    }

    public function listenerCallbackReturningOtherClass(Enlight_Event_EventArgs $args)
    {
        $this->eventHasBeenFired = true;

        return new OriginalService();
    }

    public function testDecorationOfAliasWorks()
    {
        $containerBuilder = new ContainerBuilder();
        $loader = new XmlFileLoader($containerBuilder, new FileLocator(__DIR__ . '/Resources'));
        $loader->load('services.xml');
        $containerBuilder->compile();

        static::assertInstanceOf(DecoratingService::class, $containerBuilder->get('originalservice'));
        static::assertInstanceOf(DecoratingService::class, $containerBuilder->get('decoratingservice'));
        static::assertInstanceOf(DecoratingService::class, $containerBuilder->get(OriginalService::class));
        static::assertInstanceOf(DecoratingService::class, $containerBuilder->get(DecoratingService::class));

        static::assertEquals(DecoratingService::class, $containerBuilder->get(OriginalService::class)->getName());
        static::assertEquals(DecoratingService::class, $containerBuilder->get(DecoratingService::class)->getName());
        static::assertEquals(OriginalService::class, $containerBuilder->get(DecoratingService::class)->getOriginalClass());
    }

    private function attachEvent($callback = 'listenerCallback'): void
    {
        $events = $this->container->get('events');

        $event = new \Enlight_Event_Handler_Default(
            'Enlight_Bootstrap_InitResource_shopware.cart.net_rounding.after_quantity',
            [
                $this,
                $callback,
            ]
        );

        $events->registerListener($event);
    }
}
