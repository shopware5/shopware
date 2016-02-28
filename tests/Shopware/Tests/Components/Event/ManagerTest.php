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

use Doctrine\Common\Collections\ArrayCollection;
use Enlight\Event\SubscriberInterface;

/**
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Tests_Components_Event_ManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Enlight_Event_EventManager
     */
    protected $eventManager;

    public function setUp()
    {
        $this->eventManager = new Enlight_Event_EventManager();
    }

    public function testCanCreateInstance()
    {
        $this->assertInstanceOf('Enlight_Event_EventManager', $this->eventManager);
    }

    public function testAppendEventWithCallback()
    {
        $callback = function (\Enlight_Event_EventArgs $args) {
            return 'foo';
        };

        $event = new Enlight_Event_Handler_Default(
            'Example',
            $callback
        );

        $this->eventManager->registerListener($event);

        $result = $this->eventManager->collect(
            'Example',
            new ArrayCollection(array('foo', 'bar'))
        );

        $this->assertCount(3, $result->getValues());
        $this->assertEquals('foo', $result->get(0));
        $this->assertEquals('bar', $result->get(1));
        $this->assertEquals('foo', $result->get(2));
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

        $this->assertCount(2, $result->getValues());
        $this->assertEquals('bar', $result->get(0));
        $this->assertEquals('foo', $result->get(1));
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

        $this->assertCount(3, $result->getValues());
        $this->assertEquals('foo', $result->get(0));
        $this->assertEquals('bar', $result->get(1));
        $this->assertEquals('baz', $result->get(2));
    }

    /**
     * @expectedException \Enlight_Event_Exception
     */
    public function testExceptionIsThrownOnInvalidEventArgs()
    {
        $event = new Enlight_Event_Handler_Default(
            'Example',
            function ($args) {
            }
        );

        $this->eventManager->registerListener($event);

        $this->eventManager->collect(
            'Example',
            new ArrayCollection(),
            new \stdClass()
        );
    }


    public function testAppendEventWithArray()
    {
        $event = new Enlight_Event_EventHandler(
            'Shopware_Tests_Components_Event_ManagerTest_Append_testAppendEventWithArray',
            array(
                $this,
                'appendEventWithArrayListener'
            )
        );

        $this->eventManager->registerListener($event);

        $values = new ArrayCollection(array('foo', 'bar'));
        $values = $this->eventManager->collect(
            'Shopware_Tests_Components_Event_ManagerTest_Append_testAppendEventWithArray',
            $values
        );

        $this->assertCount(4, $values->getValues());
        $this->assertEquals('foo', $values->get(0));
        $this->assertEquals('bar', $values->get(1));
        $this->assertEquals(array('foo2'), $values->get(2));
        $this->assertEquals('bar2', $values->get(3));
    }

    public function appendEventWithArrayListener(Enlight_Event_EventArgs $args)
    {
        return new ArrayCollection(array(
            array('foo2'),
            'bar2'
        ));
    }

    public function testAppendEventWithSingleValue()
    {
        $values = new ArrayCollection(array('foo', 'bar'));

        $event = new Enlight_Event_EventHandler(
            'Shopware_Tests_Components_Event_ManagerTest_Append_testAppendEventWithSingleValue',
            array(
                $this,
                'appendEventWithSingleValueListener'
            )
        );
        $this->eventManager->registerListener($event);

        $values = $this->eventManager->collect(
            'Shopware_Tests_Components_Event_ManagerTest_Append_testAppendEventWithSingleValue',
            $values,
            array()
        );

        $this->assertCount(3, $values);
        $this->assertEquals('foo', $values->get(0));
        $this->assertEquals('bar', $values->get(1));
        $this->assertEquals('foo2', $values->get(2));
    }

    public function appendEventWithSingleValueListener(Enlight_Event_EventArgs $args)
    {
        return 'foo2';
    }

    public function testAppendEventWithNullValue()
    {
        $values = new ArrayCollection(array('foo', 'bar'));

        $event = new Enlight_Event_EventHandler(
            'Shopware_Tests_Components_Event_ManagerTest_Append_testAppendEventWithNullValue',
            array(
                $this,
                'appendEventWithNullValueListener'
            )
        );
        $this->eventManager->registerListener($event);

        $values = $this->eventManager->collect(
            'Shopware_Tests_Components_Event_ManagerTest_Append_testAppendEventWithNullValue',
            $values,
            array()
        );
        $this->assertCount(2, $values->getValues());
        $this->assertEquals('foo', $values->get(0));
        $this->assertEquals('bar', $values->get(1));
    }

    public function appendEventWithNullValueListener(Enlight_Event_EventArgs $args)
    {
        return null;
    }


    public function testAppendEventWithBooleanValue()
    {
        $values = new ArrayCollection(array('foo', 'bar'));

        $event = new Enlight_Event_EventHandler(
            'Shopware_Tests_Components_Event_ManagerTest_Append_testAppendEventWithBooleanValue',
            array(
                $this,
                'appendEventWithBooleanValueListener'
            )
        );
        $this->eventManager->registerListener($event);

        $values = $this->eventManager->collect(
            'Shopware_Tests_Components_Event_ManagerTest_Append_testAppendEventWithBooleanValue',
            $values
        );

        $this->assertCount(3, $values->getValues());
        $this->assertEquals('foo', $values->get(0));
        $this->assertEquals('bar', $values->get(1));
        $this->assertEquals(true, $values->get(2));
    }

    public function appendEventWithBooleanValueListener(Enlight_Event_EventArgs $args)
    {
        return new ArrayCollection(array(
            true
        ));
    }

    public function testAppendEventWithNoListener()
    {
        $values = new ArrayCollection(array('foo', 'bar'));
        $values = $this->eventManager->collect(
            'Shopware_Tests_Components_Event_ManagerTest_Append_testAppendEventWithNoListener',
            $values
        );
        $this->assertCount(2, $values->getValues());
        $this->assertEquals('foo', $values->get(0));
        $this->assertEquals('bar', $values->get(1));
    }

    public function testAddSubscriber()
    {
        $eventSubscriber = new EventSubsciberTest();
        $this->eventManager->addSubscriber($eventSubscriber);

        $this->assertCount(1, $this->eventManager->getListeners('eventName0'));
        $this->assertCount(1, $this->eventManager->getListeners('eventName1'));
        $this->assertCount(1, $this->eventManager->getListeners('eventName2'));
        $this->assertCount(3, $this->eventManager->getListeners('eventName3'));

        $listeners = $this->eventManager->getListeners('eventName3');
        $listener = $listeners[5];
        $this->assertEquals(5, $listener->getPosition());
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
        $this->assertCount(1, $result->getValues());
        $this->assertEquals('bar', $result->get(0));
    }
}



class EventSubsciberTest implements SubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'eventName0' => 'callback0',
            'eventName1' => array('callback1'),
            'eventName2' => array('callback2', 10),
            'eventName3' => array(
                array('callback3_0', 5),
                array('callback3_1'),
                array('callback3_2')
            )
        );
    }
}
