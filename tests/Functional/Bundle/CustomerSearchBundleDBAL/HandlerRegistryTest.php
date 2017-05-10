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

namespace Shopware\Tests\Functional\Bundle\CustomerSearchBundleDBAL;

use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\AgeConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\CustomerAttributeConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\HasAddressWithCountryConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\HasCanceledOrdersConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\HasNewsletterRegistrationConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\HasOrderCountConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\HasTotalOrderAmountConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\IsCustomerSinceConditionHandler;
use Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\IsInCustomerGroupConditionHandler;
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
        $registry = Shopware()->Container()->get('customer_search.dbal.handler_registry');

        $handlers = $registry->getConditionHandlers();
        $classes = [];
        foreach ($handlers as $handler) {
            $classes[] = get_class($handler);
        }

        $this->assertContains(AgeConditionHandler::class, $classes);
        $this->assertContains(CustomerAttributeConditionHandler::class, $classes);
        $this->assertContains(HasAddressWithCountryConditionHandler::class, $classes);
        $this->assertContains(HasCanceledOrdersConditionHandler::class, $classes);
        $this->assertContains(HasNewsletterRegistrationConditionHandler::class, $classes);
        $this->assertContains(HasOrderCountConditionHandler::class, $classes);
        $this->assertContains(HasTotalOrderAmountConditionHandler::class, $classes);
        $this->assertContains(IsCustomerSinceConditionHandler::class, $classes);
        $this->assertContains(IsInCustomerGroupConditionHandler::class, $classes);
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
        $this->assertContains(SearchTermConditionHandler::class, $classes);
        $this->assertContains(SalutationConditionHandler::class, $classes);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testNoneSupportConditionThrowsException()
    {
        /** @var HandlerRegistry $registry */
        $registry = Shopware()->Container()->get('customer_search.dbal.handler_registry');
        $registry->getConditionHandler(new SimpleCondition('test'));
    }
}
