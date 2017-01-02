<?php

namespace Shopware\Tests\Functional\Bundle\CustomerSearchBundle;

use Shopware\Bundle\CustomerSearchBundle\ConditionHandler\HasAddressWithCountryConditionHandler;
use Shopware\Bundle\CustomerSearchBundle\ConditionHandler\HasCanceledOrdersConditionHandler;
use Shopware\Bundle\CustomerSearchBundle\ConditionHandler\HasOrderCountConditionHandler;
use Shopware\Bundle\CustomerSearchBundle\ConditionHandler\HasTotalOrderAmountConditionHandler;
use Shopware\Bundle\CustomerSearchBundle\ConditionHandler\IsCustomerSinceConditionHandler;
use Shopware\Bundle\CustomerSearchBundle\ConditionHandler\IsInCustomerGroupConditionHandler;
use Shopware\Bundle\CustomerSearchBundle\ConditionHandler\OrderedAtWeekdayConditionHandler;
use Shopware\Bundle\CustomerSearchBundle\ConditionHandler\OrderedInLastDaysConditionHandler;
use Shopware\Bundle\CustomerSearchBundle\ConditionHandler\OrderedInShopConditionHandler;
use Shopware\Bundle\CustomerSearchBundle\ConditionHandler\OrderedOnDeviceConditionHandler;
use Shopware\Bundle\CustomerSearchBundle\ConditionHandler\OrderedProductConditionHandler;
use Shopware\Bundle\CustomerSearchBundle\ConditionHandler\OrderedProductOfCategoryConditionHandler;
use Shopware\Bundle\CustomerSearchBundle\ConditionHandler\OrderedProductOfManufacturerConditionHandler;
use Shopware\Bundle\CustomerSearchBundle\ConditionHandler\OrderedWithDeliveryConditionHandler;
use Shopware\Bundle\CustomerSearchBundle\ConditionHandler\OrderedWithPaymentConditionHandler;
use Shopware\Bundle\CustomerSearchBundle\ConditionHandler\RegisteredInShopConditionHandler;
use Shopware\Bundle\CustomerSearchBundle\HandlerRegistry;
use Shopware\Bundle\SearchBundle\Condition\SimpleCondition;

class HandlerRegistryTest extends TestCase
{
    public function testCoreHandlersAreRegistered()
    {
        /** @var HandlerRegistry $registry */
        $registry = Shopware()->Container()->get('shopware_customer_search.handler_registry');

        $handlers = $registry->getConditionHandlers();
        $classes = [];
        foreach ($handlers as $handler) {
            $classes[] = get_class($handler);
        }

        $this->assertContains(HasAddressWithCountryConditionHandler::class, $classes);
        $this->assertContains(HasCanceledOrdersConditionHandler::class, $classes);
        $this->assertContains(HasOrderCountConditionHandler::class, $classes);
        $this->assertContains(HasTotalOrderAmountConditionHandler::class, $classes);
        $this->assertContains(IsInCustomerGroupConditionHandler::class, $classes);
        $this->assertContains(IsCustomerSinceConditionHandler::class, $classes);
        $this->assertContains(OrderedAtWeekdayConditionHandler::class, $classes);
        $this->assertContains(OrderedInLastDaysConditionHandler::class, $classes);
        $this->assertContains(OrderedInShopConditionHandler::class, $classes);
        $this->assertContains(OrderedOnDeviceConditionHandler::class, $classes);
        $this->assertContains(OrderedProductConditionHandler::class, $classes);
        $this->assertContains(OrderedProductOfCategoryConditionHandler::class, $classes);
        $this->assertContains(OrderedProductOfManufacturerConditionHandler::class, $classes);
        $this->assertContains(OrderedWithDeliveryConditionHandler::class, $classes);
        $this->assertContains(OrderedWithPaymentConditionHandler::class, $classes);
        $this->assertContains(RegisteredInShopConditionHandler::class, $classes);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testNoneSupportConditionThrowsException()
    {
        /** @var HandlerRegistry $registry */
        $registry = Shopware()->Container()->get('shopware_customer_search.handler_registry');
        $registry->getConditionHandler(new SimpleCondition('test'));
    }
}