<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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

    public function testAppendEventWithArray()
    {
        $event = new Enlight_Event_EventHandler(
            'Shopware_Tests_Components_Event_ManagerTest_Append_testAppendEventWithArray',
            array($this, 'appendEventWithArrayListener')
        );

        $this->eventManager->registerListener($event);

        /** @var $values \Doctrine\Common\Collections\ArrayCollection */
        $values = new \Doctrine\Common\Collections\ArrayCollection(array('foo', 'bar'));
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
        return new \Doctrine\Common\Collections\ArrayCollection(array(
            array('foo2'),
            'bar2'
        ));
    }

    public function testAppendEventWithSingleValue()
    {
        $values = new \Doctrine\Common\Collections\ArrayCollection(array('foo', 'bar'));

        $event = new Enlight_Event_EventHandler(
            'Shopware_Tests_Components_Event_ManagerTest_Append_testAppendEventWithSingleValue',
            array($this, 'appendEventWithSingleValueListener')
        );
        $this->eventManager->registerListener($event);

        $values = $this->eventManager->collect(
            'Shopware_Tests_Components_Event_ManagerTest_Append_testAppendEventWithSingleValue',
            $values,
            array()
        );
        $this->assertCount(3, $values);
    }

    public function appendEventWithSingleValueListener(Enlight_Event_EventArgs $args)
    {
        return 'foo2';
    }


    public function testAppendEventWithNullValue()
    {
        $values = new \Doctrine\Common\Collections\ArrayCollection(array('foo', 'bar'));

        $event = new Enlight_Event_EventHandler(
            'Shopware_Tests_Components_Event_ManagerTest_Append_testAppendEventWithNullValue',
            array($this, 'appendEventWithNullValueListener')
        );
        $this->eventManager->registerListener($event);

        $values = $this->eventManager->collect(
            'Shopware_Tests_Components_Event_ManagerTest_Append_testAppendEventWithNullValue',
            $values,
            array()
        );
        $this->assertCount(2, $values->getValues());
    }

    public function appendEventWithNullValueListener(Enlight_Event_EventArgs $args)
    {
        return null;
    }


    public function testAppendEventWithBooleanValue()
    {
        $values = new \Doctrine\Common\Collections\ArrayCollection(array('foo', 'bar'));

        $event = new Enlight_Event_EventHandler(
            'Shopware_Tests_Components_Event_ManagerTest_Append_testAppendEventWithBooleanValue',
            array($this, 'appendEventWithBooleanValueListener')
        );
        $this->eventManager->registerListener($event);

        $values = $this->eventManager->collect(
            'Shopware_Tests_Components_Event_ManagerTest_Append_testAppendEventWithBooleanValue',
            $values
        );
        $this->assertCount(3, $values->getValues());
        $this->assertEquals(true, $values->get(2));
    }

    public function appendEventWithBooleanValueListener(Enlight_Event_EventArgs $args)
    {
        return new \Doctrine\Common\Collections\ArrayCollection(array(
            true
        ));
    }


    public function testAppendEventWithNoListener()
    {
        $values = new \Doctrine\Common\Collections\ArrayCollection(array('foo', 'bar'));
        $values = $this->eventManager->collect(
            'Shopware_Tests_Components_Event_ManagerTest_Append_testAppendEventWithNoListener',
            $values
        );
        $this->assertCount(2, $values->getValues());
    }
}
