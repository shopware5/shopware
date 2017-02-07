<?php

namespace Shopware\Tests\Unit\Bundle\CartBundle\Domain\Delivery;

use Shopware\Bundle\CartBundle\Domain\Delivery\Delivery;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryCollection;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryDate;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryPositionCollection;
use Shopware\Bundle\CartBundle\Domain\Delivery\DeliveryService;
use Shopware\Bundle\CartBundle\Domain\Customer\Address;

class DeliveryCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testCollectionIsCountable()
    {
        $collection = new DeliveryCollection();
        static::assertCount(0, $collection);
        static::assertSame(0, $collection->count());
    }

    public function testAddFunctionAddsANewDelivery()
    {
        $collection = new DeliveryCollection();
        $collection->add(
            new Delivery(
                new DeliveryPositionCollection(),
                new DeliveryDate(
                    new \DateTime(),
                    new \DateTime()
                ),
                new DeliveryService(),
                new Address()
            )
        );
        static::assertCount(1, $collection);
    }

    public function testCollectionCanBeFilledByConstructor()
    {
        $collection = new DeliveryCollection([
            new Delivery(
                new DeliveryPositionCollection(),
                new DeliveryDate(
                    new \DateTime(),
                    new \DateTime()
                ),
                new DeliveryService(),
                new Address()
            ),
            new Delivery(
                new DeliveryPositionCollection(),
                new DeliveryDate(
                    new \DateTime(),
                    new \DateTime()
                ),
                new DeliveryService(),
                new Address()
            )
        ]);
        static::assertCount(2, $collection);
    }

    public function testCollectionCanBeCleared()
    {
        $collection = new DeliveryCollection([
            new Delivery(
                new DeliveryPositionCollection(),
                new DeliveryDate(
                    new \DateTime(),
                    new \DateTime()
                ),
                new DeliveryService(),
                new Address()
            ),
            new Delivery(
                new DeliveryPositionCollection(),
                new DeliveryDate(
                    new \DateTime(),
                    new \DateTime()
                ),
                new DeliveryService(),
                new Address()
            )
        ]);
        $collection->clear();
        static::assertCount(0, $collection);
    }
}
