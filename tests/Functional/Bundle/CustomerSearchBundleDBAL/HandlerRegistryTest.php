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

namespace Shopware\Tests\Functional\Bundle\CustomerSearchBundleDBAL;

use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\AgeConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\CustomerAttributeConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\HasAddressWithCountryConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\HasCanceledOrdersConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\HasNewsletterRegistrationConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\HasNoAddressWithCountryConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\HasNoNewsletterRegistrationConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\HasOrderCountConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\HasTotalOrderAmountConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\IsCustomerSinceConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\IsInCustomerGroupConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\IsNotInCustomerGroupConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\NotOrderedInLastDaysConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\NotOrderedInShopConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\NotOrderedProductConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\NotOrderedWithDeliveryConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\NotOrderedWithPaymentConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\NotRegisteredInShopConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\NotSalutationConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\OrderedAtWeekdayConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\OrderedInLastDaysConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\OrderedInShopConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\OrderedOnDeviceConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\OrderedProductConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\OrderedProductOfCategoryConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\OrderedProductOfManufacturerConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\OrderedWithDeliveryConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\OrderedWithPaymentConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\RegisteredInShopConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\SalutationConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\SearchTermConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\HandlerRegistry;
use Shopware\Bundle\SearchBundle\Condition\SimpleCondition;

class HandlerRegistryTest extends TestCase
{
    public function testCoreHandlersAreRegistered()
    {
        /** @var HandlerRegistry $registry */
        $registry = Shopware()->Container()->get(\Shopware\Bundle\CustomerSearchBundleDBAL\HandlerRegistry::class);

        $handlers = $registry->getConditionHandlers();
        $classes = [];
        foreach ($handlers as $handler) {
            $classes[] = \get_class($handler);
        }

        static::assertContains(AgeConditionHandler::class, $classes);
        static::assertContains(CustomerAttributeConditionHandler::class, $classes);
        static::assertContains(HasAddressWithCountryConditionHandler::class, $classes);
        static::assertContains(HasNoAddressWithCountryConditionHandler::class, $classes);
        static::assertContains(HasCanceledOrdersConditionHandler::class, $classes);
        static::assertContains(HasNewsletterRegistrationConditionHandler::class, $classes);
        static::assertContains(HasNoNewsletterRegistrationConditionHandler::class, $classes);
        static::assertContains(HasOrderCountConditionHandler::class, $classes);
        static::assertContains(HasTotalOrderAmountConditionHandler::class, $classes);
        static::assertContains(IsCustomerSinceConditionHandler::class, $classes);
        static::assertContains(IsInCustomerGroupConditionHandler::class, $classes);
        static::assertContains(IsNotInCustomerGroupConditionHandler::class, $classes);
        static::assertContains(OrderedAtWeekdayConditionHandler::class, $classes);
        static::assertContains(OrderedInLastDaysConditionHandler::class, $classes);
        static::assertContains(NotOrderedInLastDaysConditionHandler::class, $classes);
        static::assertContains(OrderedInShopConditionHandler::class, $classes);
        static::assertContains(NotOrderedInShopConditionHandler::class, $classes);
        static::assertContains(OrderedOnDeviceConditionHandler::class, $classes);
        static::assertContains(OrderedProductConditionHandler::class, $classes);
        static::assertContains(NotOrderedProductConditionHandler::class, $classes);
        static::assertContains(OrderedProductOfCategoryConditionHandler::class, $classes);
        static::assertContains(OrderedProductOfManufacturerConditionHandler::class, $classes);
        static::assertContains(OrderedWithDeliveryConditionHandler::class, $classes);
        static::assertContains(NotOrderedWithDeliveryConditionHandler::class, $classes);
        static::assertContains(OrderedWithPaymentConditionHandler::class, $classes);
        static::assertContains(NotOrderedWithPaymentConditionHandler::class, $classes);
        static::assertContains(RegisteredInShopConditionHandler::class, $classes);
        static::assertContains(NotRegisteredInShopConditionHandler::class, $classes);
        static::assertContains(SearchTermConditionHandler::class, $classes);
        static::assertContains(SalutationConditionHandler::class, $classes);
        static::assertContains(NotSalutationConditionHandler::class, $classes);
    }

    public function testNoneSupportConditionThrowsException()
    {
        $this->expectException('RuntimeException');
        /** @var HandlerRegistry $registry */
        $registry = Shopware()->Container()->get(\Shopware\Bundle\CustomerSearchBundleDBAL\HandlerRegistry::class);
        $registry->getConditionHandler(new SimpleCondition('test'));
    }
}
