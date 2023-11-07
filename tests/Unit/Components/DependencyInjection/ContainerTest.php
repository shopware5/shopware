<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Unit\Components\DependencyInjection;

use Enlight_Event_EventArgs;
use Enlight_Event_EventManager;
use Exception;
use PHPUnit\Framework\TestCase;
use Shopware\Components\ContainerAwareEventManager;
use Shopware\Components\DependencyInjection\Container;
use stdClass;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;

class ContainerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $this->container = new ProjectServiceContainer();
        $service = $this->createMock(Enlight_Event_EventManager::class);

        $this->container->set('events', $service);
    }

    public function testSet(): void
    {
        $object = new stdClass();

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
        $this->expectException(Exception::class);

        $this->container->get('foo');
    }

    public function testGetOnNonExistentWithExceptionBehaviour(): void
    {
        $this->expectException(Exception::class);
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

    public function testEventsAreEmittedDuringServiceInitialisation(): void
    {
        $eventManager = $this->createMock(Enlight_Event_EventManager::class);
        $eventManager->expects(static::exactly(2))
            ->method('notify')
            ->with(static::logicalOr(
                'Enlight_Bootstrap_AfterRegisterResource_events',
                'Enlight_Bootstrap_AfterInitResource_bar'
            ));
        $eventManager->expects(static::once())
            ->method('notifyUntil')
            ->with('Enlight_Bootstrap_InitResource_bar');

        $this->container->set('events', $eventManager);

        static::assertInstanceOf('stdClass', $this->container->get('bar'));
    }

    public function testEventsAreEmitedDuringServiceInitialisationWhenUsingAlias(): void
    {
        $eventManager = $this->createMock(Enlight_Event_EventManager::class);
        $eventManager->expects(static::exactly(3))
            ->method('notify')
            ->with(static::logicalOr(
                'Enlight_Bootstrap_AfterRegisterResource_events',
                'Enlight_Bootstrap_AfterInitResource_bar',
                'Enlight_Bootstrap_AfterInitResource_alias'
            ));
        $eventManager->expects(static::exactly(2))
            ->method('notifyUntil')
            ->with(static::logicalOr(
                'Enlight_Bootstrap_InitResource_bar',
                'Enlight_Bootstrap_InitResource_alias'
            ));

        $this->container->set('events', $eventManager);

        static::assertInstanceOf('stdClass', $this->container->get('alias'));
    }

    public function testEventsAreEmitedDuringServiceInitialisationWhenUsingUnknownServices(): void
    {
        $eventManager = $this->createMock(Enlight_Event_EventManager::class);
        $eventManager->expects(static::exactly(2))
            ->method('notify')
            ->with(static::logicalOr(
                'Enlight_Bootstrap_AfterRegisterResource_events',
                'Enlight_Bootstrap_AfterInitResource_foo'
            ));
        $eventManager->expects(static::once())
            ->method('notifyUntil')
            ->with('Enlight_Bootstrap_InitResource_foo');

        $this->container->set('events', $eventManager);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('You have requested a non-existent service "foo".');

        $this->container->get('foo');
    }

    public function testAfterInitEventDecorator(): void
    {
        $this->container = new ProjectServiceContainer();
        $eventManager = new ContainerAwareEventManager($this->container);
        $this->container->set('events', $eventManager);

        $class = new stdClass();
        $class->name = 'decorated';

        $this->container->get('events')->addListener(
            'Enlight_Bootstrap_AfterInitResource_bar',
            function (Enlight_Event_EventArgs $e) use ($class) {
                $container = $e->get('subject');
                self::assertInstanceOf(ProjectServiceContainer::class, $container);

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

        $class = new stdClass();
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
            function (Enlight_Event_EventArgs $e) {
                $container = $e->get('subject');
                self::assertInstanceOf(ProjectServiceContainer::class, $container);

                // Cause circular reference
                $container->get('parent');
            }
        );

        $this->container->get('events')->addListener(
            'Enlight_Bootstrap_AfterInitResource_parent',
            function (Enlight_Event_EventArgs $e) {
                $container = $e->get('subject');
                self::assertInstanceOf(ProjectServiceContainer::class, $container);

                $coreParent = $container->get('parent');

                $decoratedParent = new stdClass();
                $decoratedParent->name = 'decorated_parent';
                $decoratedParent->coreParent = $coreParent;

                $container->set('parent', $decoratedParent);
            }
        );

        $this->expectException(ServiceCircularReferenceException::class);

        $this->container->get('parent');
    }
}
