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

namespace Shopware\Tests\Unit\Components\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Shopware\Components\ContainerAwareEventManager;
use Shopware\Components\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;

class ContainerTest extends TestCase
{
    /**
     * @var Container
     */
    private $container;

    protected function setUp(): void
    {
        $this->container = new ProjectServiceContainer();
        $service = $this->createMock(\Enlight_Event_EventManager::class);

        $this->container->set('events', $service);
    }

    public function testSet(): void
    {
        $object = new \stdClass();

        $this->container->set('someKey', $object);
        static::assertSame($object, $this->container->get('someKey'));
    }

    public function testHas(): void
    {
        static::assertTrue($this->container->has('bar'));
        static::assertTrue($this->container->has('alias'));

        static::assertFalse($this->container->has('some'));
    }

    public function testGetOnNonExistentWithDefaultBehaviour(): void
    {
        $this->expectException(\Exception::class);

        $this->container->get('foo');
    }

    public function testGetOnNonExistentWithExceptionBehaviour(): void
    {
        $this->expectException(\Exception::class);
        $this->container->get('foo', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE);
    }

    public function testGetOnNonExistentWithNullBehaviour(): void
    {
        static::assertNull(
            $this->container->get('foo', ContainerInterface::NULL_ON_INVALID_REFERENCE)
        );
    }

    public function testGetOnNonExistentWithIgnoreBehaviour(): void
    {
        static::assertNull(
            $this->container->get('foo', ContainerInterface::IGNORE_ON_INVALID_REFERENCE)
        );
    }

    public function testEventsAreEmitedDuringServiceInitialisation(): void
    {
        $service = $this->prophesize(\Enlight_Event_EventManager::class);

        $service->notify('Enlight_Bootstrap_AfterRegisterResource_events', Argument::any())->shouldBeCalled();
        $service->notifyUntil('Enlight_Bootstrap_InitResource_bar', Argument::any())->shouldBeCalled();
        $service->notify('Enlight_Bootstrap_AfterInitResource_bar', Argument::any())->shouldBeCalled();

        $service = $service->reveal();
        $this->container->set('events', $service);

        static::assertInstanceOf('stdClass', $this->container->get('bar'));
    }

    public function testEventsAreEmitedDuringServiceInitialisationWhenUsingAlias(): void
    {
        $service = $this->prophesize(\Enlight_Event_EventManager::class);

        $service->notify('Enlight_Bootstrap_AfterRegisterResource_events', Argument::any())->shouldBeCalled();
        $service->notifyUntil('Enlight_Bootstrap_InitResource_bar', Argument::any())->shouldBeCalled();
        $service->notifyUntil('Enlight_Bootstrap_InitResource_alias', Argument::any())->shouldBeCalled();
        $service->notify('Enlight_Bootstrap_AfterInitResource_bar', Argument::any())->shouldBeCalled();
        $service->notify('Enlight_Bootstrap_AfterInitResource_alias', Argument::any())->shouldBeCalled();

        $service = $service->reveal();
        $this->container->set('events', $service);

        static::assertInstanceOf('stdClass', $this->container->get('alias'));
    }

    public function testEventsAreEmitedDuringServiceInitialisationWhenUsingUnknownServices(): void
    {
        $service = $this->prophesize(\Enlight_Event_EventManager::class);

        $service->notify('Enlight_Bootstrap_AfterRegisterResource_events', Argument::any())->shouldBeCalled();
        $service->notifyUntil('Enlight_Bootstrap_InitResource_foo', Argument::any())->shouldBeCalled();
        $service->notify('Enlight_Bootstrap_AfterInitResource_foo', Argument::any())->shouldBeCalled();

        $service = $service->reveal();
        $this->container->set('events', $service);

        $this->expectException(\Exception::class);

        $this->container->get('foo');
    }

    public function testAfterInitEventDecorator(): void
    {
        $this->container = new ProjectServiceContainer();
        $eventManager = new ContainerAwareEventManager($this->container);
        $this->container->set('events', $eventManager);

        $class = new \stdClass();
        $class->name = 'decorated';

        $this->container->get('events')->addListener(
            'Enlight_Bootstrap_AfterInitResource_bar',
            function (\Enlight_Event_EventArgs $e) use ($class) {
                /** @var ProjectServiceContainer $container */
                $container = $e->getSubject();
                $container->set('bar', $class);
            }
        );

        static::assertSame($class, $this->container->get('bar'));
    }

    public function testAfterInitEventDecoratorService(): void
    {
        $this->container = new ProjectServiceContainer();
        $eventManager = new ContainerAwareEventManager($this->container);
        $this->container->set('events', $eventManager);

        $class = new \stdClass();
        $class->name = 'decorated';

        $this->container->set('service.listener', new Service($class));

        $this->container->get('events')->addListenerService(
            'Enlight_Bootstrap_AfterInitResource_bar',
            ['service.listener', 'onEvent']
        );

        static::assertSame($class, $this->container->get('bar'));
    }

    public function testServiceCircularReferenceExceptionException(): void
    {
        $this->container = new ProjectServiceContainer();
        $eventManager = new ContainerAwareEventManager($this->container);
        $this->container->set('events', $eventManager);

        $this->container->get('events')->addListener(
            'Enlight_Bootstrap_InitResource_child',
            function (\Enlight_Event_EventArgs $e) {
                /** @var ProjectServiceContainer $container */
                $container = $e->getSubject();

                // Cause circular reference
                $container->get('parent');
            }
        );

        $this->container->get('events')->addListener(
            'Enlight_Bootstrap_AfterInitResource_parent',
            function (\Enlight_Event_EventArgs $e) {
                /** @var ProjectServiceContainer $container */
                $container = $e->getSubject();

                $coreParent = $container->get('parent');

                $decoratedParent = new \stdClass();
                $decoratedParent->name = 'decorated_parent';
                $decoratedParent->coreParent = $coreParent;

                $container->set('parent', $decoratedParent);
            }
        );

        $this->expectException(ServiceCircularReferenceException::class);

        $this->container->get('parent');
    }
}
