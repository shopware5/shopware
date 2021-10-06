<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Unit\Components\Event;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use Enlight_Event_Handler_Default;
use PHPUnit\Framework\TestCase;
use Shopware\Components\ContainerAwareEventManager;
use Symfony\Component\DependencyInjection\Container;

class ContainerAwareEventManagerTest extends TestCase
{
    private Container $container;

    private ContainerAwareEventManager $eventManager;

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->eventManager = new ContainerAwareEventManager($this->container);
    }

    public function testAddAListenerService(): void
    {
        $service = $this->createMock(Service::class);

        $eventArgs = new Enlight_Event_EventArgs(['some' => 'args']);

        $service
            ->expects(static::once())
            ->method('onEvent')
            ->with($eventArgs)
        ;

        $this->container->set('service.listener', $service);
        $this->eventManager->addListenerService('onEvent', ['service.listener', 'onEvent']);

        $this->eventManager->notify('onEvent', $eventArgs);
    }

    public function testAddAListenerServiceCallMultipleTimes(): void
    {
        $service = $this->createMock(Service::class);

        $eventArgs = new Enlight_Event_EventArgs(['some' => 'args']);

        $service
            ->expects(static::exactly(2))
            ->method('onEvent')
            ->with($eventArgs)
        ;

        $this->container->set('service.listener', $service);
        $this->eventManager->addListenerService('onEvent', ['service.listener', 'onEvent']);

        $this->eventManager->notify('onEvent', $eventArgs);
        $this->eventManager->notify('onEvent', $eventArgs);
    }

    public function testAddASubscriberService(): void
    {
        $eventArgs = new Enlight_Event_EventArgs(['some' => 'args']);

        $service = $this->createMock(SubscriberService::class);
        $service
            ->expects(static::once())
            ->method('onEvent')
            ->with($eventArgs)
        ;
        $service
            ->expects(static::once())
            ->method('onEventWithPriority')
            ->with($eventArgs)
        ;
        $service
            ->expects(static::once())
            ->method('onEventNested')
            ->with($eventArgs)
        ;
        $this->container->set('service.subscriber', $service);

        $this->eventManager->addSubscriberService('service.subscriber', SubscriberService::class);
        $this->eventManager->notify('onEvent', $eventArgs);
        $this->eventManager->notify('onEventWithPriority', $eventArgs);
        $this->eventManager->notify('onEventNested', $eventArgs);
    }

    public function testPreventDuplicateListenerService(): void
    {
        $eventArgs = new Enlight_Event_EventArgs(['some' => 'args']);
        $service = $this->createMock(Service::class);
        $service
            ->expects(static::once())
            ->method('onEvent')
            ->with($eventArgs)
        ;

        $this->container->set('service.listener', $service);

        $this->eventManager->addListenerService('onEvent', ['service.listener', 'onEvent'], 5);
        $this->eventManager->addListenerService('onEvent', ['service.listener', 'onEvent'], 10);

        $this->eventManager->notify('onEvent', $eventArgs);
    }

    public function testHasListenersOnLazyLoad(): void
    {
        $service = $this->createMock(Service::class);

        $this->container->set('service.listener', $service);

        $this->eventManager->addListenerService('onEvent', ['service.listener', 'onEvent']);
        $service
            ->expects(static::once())
            ->method('onEvent')
        ;

        if ($this->eventManager->hasListeners('onEvent')) {
            $this->eventManager->notify('onEvent');
        }
    }

    public function testGetListenersOnLazyLoad(): void
    {
        $service = $this->createMock(Service::class);
        $this->container->set('service.listener', $service);

        $this->eventManager->addListenerService('onEvent', ['service.listener', 'onEvent']);
        $listeners = $this->eventManager->getAllListeners();

        static::assertTrue(isset($listeners['onevent']));
        static::assertCount(1, $this->eventManager->getListeners('onEvent'));
    }

    public function testRemoveAfterDispatch(): void
    {
        $eventArgs = new Enlight_Event_EventArgs(['some' => 'args']);

        $service = $this->createMock(Service::class);
        $this->container->set('service.listener', $service);

        $this->eventManager->addListenerService('onEvent', ['service.listener', 'onEvent']);

        $listener = $this->container->get('service.listener');
        static::assertNotNull($listener);
        $handler = new Enlight_Event_Handler_Default('onEvent', [$listener, 'onEvent']);

        $this->eventManager->notify('onEvent', $eventArgs);

        $this->eventManager->removeListener($handler);

        static::assertFalse($this->eventManager->hasListeners('onEvent'));
    }

    public function testRemoveBeforeDispatch(): void
    {
        $service = $this->createMock(Service::class);
        $this->container->set('service.listener', $service);
        $this->eventManager->addListenerService('onEvent', ['service.listener', 'onEvent']);

        $listener = $this->container->get('service.listener');
        static::assertNotNull($listener);
        $this->eventManager->removeListener(new Enlight_Event_Handler_Default('onEvent', [$listener, 'onEvent']));

        static::assertFalse($this->eventManager->hasListeners('onEvent'));
    }
}

class Service
{
    public function onEvent(Enlight_Event_EventArgs $e): void
    {
    }
}
class SubscriberService implements SubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'onEvent' => 'onEvent',
            'onEventWithPriority' => ['onEventWithPriority', 10],
            'onEventNested' => [['onEventNested']],
        ];
    }

    public function onEvent(Enlight_Event_EventArgs $e): void
    {
    }

    public function onEventWithPriority(Enlight_Event_EventArgs $e): void
    {
    }

    public function onEventNested(Enlight_Event_EventArgs $e): void
    {
    }
}
