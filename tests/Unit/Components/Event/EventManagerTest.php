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

namespace Shopware\Tests\Unit\Components\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use Enlight_Event_EventHandler;
use Enlight_Event_EventManager;
use Enlight_Event_Exception;
use Enlight_Event_Handler_Default;
use PHPUnit\Framework\TestCase;
use stdClass;

class EventManagerTest extends TestCase
{
    private $eventManager;

    public function setUp(): void
    {
        $this->eventManager = new Enlight_Event_EventManager();
    }

    public function testCanCreateInstance()
    {
        static::assertInstanceOf(Enlight_Event_EventManager::class, $this->eventManager);
    }

    public function testAppendEventWithCallback()
    {
        $callback = function (Enlight_Event_EventArgs $args) {
            return 'foo';
        };

        $event = new Enlight_Event_Handler_Default(
            'Example',
            $callback
        );

        $this->eventManager->registerListener($event);

        $result = $this->eventManager->collect(
            'Example',
            new ArrayCollection(['foo', 'bar'])
        );

        static::assertCount(3, $result->getValues());
        static::assertEquals('foo', $result->get(0));
        static::assertEquals('bar', $result->get(1));
        static::assertEquals('foo', $result->get(2));
    }

    public function testEventHandlerWithHighPosition()
    {
        $handler0 = new Enlight_Event_Handler_Default(
            'Example',
            function ($args) {
                return 'foo';
            },
            200
        );
        $this->eventManager->registerListener($handler0);

        $handler1 = new Enlight_Event_Handler_Default(
            'Example',
            function ($args) {
                return 'bar';
            },
            100
        );
        $this->eventManager->registerListener($handler1);

        $result = $this->eventManager->collect(
            'Example',
            new ArrayCollection()
        );

        static::assertCount(2, $result->getValues());
        static::assertEquals('bar', $result->get(0));
        static::assertEquals('foo', $result->get(1));
    }

    public function testEventHandlerWithEqualPosition()
    {
        $handler0 = new Enlight_Event_Handler_Default(
            'Example',
            function ($args) {
                return 'foo';
            },
            1
        );
        $this->eventManager->registerListener($handler0);

        $handler1 = new Enlight_Event_Handler_Default(
            'Example',
            function ($args) {
                return 'bar';
            },
            1
        );
        $this->eventManager->registerListener($handler1);

        $handler2 = new Enlight_Event_Handler_Default(
            'Example',
            function ($args) {
                return 'baz';
            },
            2
        );
        $this->eventManager->registerListener($handler2);

        $result = $this->eventManager->collect(
            'Example',
            new ArrayCollection()
        );

        static::assertCount(3, $result->getValues());
        static::assertEquals('foo', $result->get(0));
        static::assertEquals('bar', $result->get(1));
        static::assertEquals('baz', $result->get(2));
    }

    public function testExceptionIsThrownOnInvalidEventArgs()
    {
        $event = new Enlight_Event_Handler_Default(
            'Example',
            function ($args) {
            }
        );

        $this->eventManager->registerListener($event);

        $this->expectException(Enlight_Event_Exception::class);

        $this->eventManager->collect(
            'Example',
            new ArrayCollection(),
            new stdClass()
        );
    }

    public function testAppendEventWithArray()
    {
        $event = new Enlight_Event_EventHandler(
            'Shopware_Tests_Components_Event_ManagerTest_Append_testAppendEventWithArray',
            [
                $this,
                'appendEventWithArrayListener',
            ]
        );

        $this->eventManager->registerListener($event);

        $values = new ArrayCollection(['foo', 'bar']);
        $values = $this->eventManager->collect(
            'Shopware_Tests_Components_Event_ManagerTest_Append_testAppendEventWithArray',
            $values
        );

        static::assertCount(4, $values->getValues());
        static::assertEquals('foo', $values->get(0));
        static::assertEquals('bar', $values->get(1));
        static::assertEquals(['foo2'], $values->get(2));
        static::assertEquals('bar2', $values->get(3));
    }

    public function appendEventWithArrayListener(Enlight_Event_EventArgs $args)
    {
        return new ArrayCollection([
            ['foo2'],
            'bar2',
        ]);
    }

    public function testAppendEventWithSingleValue()
    {
        $values = new ArrayCollection(['foo', 'bar']);

        $event = new Enlight_Event_EventHandler(
            'Shopware_Tests_Components_Event_ManagerTest_Append_testAppendEventWithSingleValue',
            [
                $this,
                'appendEventWithSingleValueListener',
            ]
        );
        $this->eventManager->registerListener($event);

        $values = $this->eventManager->collect(
            'Shopware_Tests_Components_Event_ManagerTest_Append_testAppendEventWithSingleValue',
            $values,
            []
        );

        static::assertCount(3, $values);
        static::assertEquals('foo', $values->get(0));
        static::assertEquals('bar', $values->get(1));
        static::assertEquals('foo2', $values->get(2));
    }

    public function appendEventWithSingleValueListener(Enlight_Event_EventArgs $args)
    {
        return 'foo2';
    }

    public function testAppendEventWithNullValue()
    {
        $values = new ArrayCollection(['foo', 'bar']);

        $event = new Enlight_Event_EventHandler(
            'Shopware_Tests_Components_Event_ManagerTest_Append_testAppendEventWithNullValue',
            [
                $this,
                'appendEventWithNullValueListener',
            ]
        );
        $this->eventManager->registerListener($event);

        $values = $this->eventManager->collect(
            'Shopware_Tests_Components_Event_ManagerTest_Append_testAppendEventWithNullValue',
            $values,
            []
        );
        static::assertCount(2, $values->getValues());
        static::assertEquals('foo', $values->get(0));
        static::assertEquals('bar', $values->get(1));
    }

    public function appendEventWithNullValueListener(Enlight_Event_EventArgs $args)
    {
        return null;
    }

    public function testAppendEventWithBooleanValue()
    {
        $values = new ArrayCollection(['foo', 'bar']);

        $event = new Enlight_Event_EventHandler(
            'Shopware_Tests_Components_Event_ManagerTest_Append_testAppendEventWithBooleanValue',
            [
                $this,
                'appendEventWithBooleanValueListener',
            ]
        );
        $this->eventManager->registerListener($event);

        $values = $this->eventManager->collect(
            'Shopware_Tests_Components_Event_ManagerTest_Append_testAppendEventWithBooleanValue',
            $values
        );

        static::assertCount(3, $values->getValues());
        static::assertEquals('foo', $values->get(0));
        static::assertEquals('bar', $values->get(1));
        static::assertTrue($values->get(2));
    }

    public function appendEventWithBooleanValueListener(Enlight_Event_EventArgs $args)
    {
        return new ArrayCollection([
            true,
        ]);
    }

    public function testAppendEventWithNoListener()
    {
        $values = new ArrayCollection(['foo', 'bar']);
        $values = $this->eventManager->collect(
            'Shopware_Tests_Components_Event_ManagerTest_Append_testAppendEventWithNoListener',
            $values
        );
        static::assertCount(2, $values->getValues());
        static::assertEquals('foo', $values->get(0));
        static::assertEquals('bar', $values->get(1));
    }

    public function testAddSubscriber()
    {
        $eventSubscriber = new EventSubscriberTest();
        $this->eventManager->addSubscriber($eventSubscriber);

        static::assertCount(1, $this->eventManager->getListeners('eventName0'));
        static::assertCount(1, $this->eventManager->getListeners('eventName1'));
        static::assertCount(1, $this->eventManager->getListeners('eventName2'));
        static::assertCount(3, $this->eventManager->getListeners('eventName3'));

        $listeners = $this->eventManager->getListeners('eventName3');
        $listener = $listeners[5];
        static::assertEquals(5, $listener->getPosition());
    }

    public function testRemoveSubscriber()
    {
        $handler0 = new Enlight_Event_Handler_Default(
            'Example',
            function ($args) {
                return 'foo';
            }
        );
        $this->eventManager->registerListener($handler0);

        $handler1 = new Enlight_Event_Handler_Default(
            'Example',
            function ($args) {
                return 'bar';
            }
        );
        $this->eventManager->registerListener($handler1);

        // Remove first subscriber
        $this->eventManager->removeListener($handler0);

        $result = $this->eventManager->collect(
            'Example',
            new ArrayCollection()
        );

        // Only the second one should be left
        static::assertCount(1, $result->getValues());
        static::assertEquals('bar', $result->get(0));
    }
}

class EventSubscriberTest implements SubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'eventName0' => 'callback0',
            'eventName1' => ['callback1'],
            'eventName2' => ['callback2', 10],
            'eventName3' => [
                ['callback3_0', 5],
                ['callback3_1'],
                ['callback3_2'],
            ],
        ];
    }
}
