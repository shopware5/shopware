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

namespace Shopware\Tests\Unit\Bundle\CartBundle\Domain\Validator;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\Validator\Collector\RuleDataCollectorRegistry;
use Shopware\Bundle\CartBundle\Domain\Validator\Rule\RuleCollection;
use Shopware\Bundle\CartBundle\Infrastructure\Validator\Collector\OrderClearedStateRuleCollector;
use Shopware\Bundle\CartBundle\Infrastructure\Validator\Collector\OrderCountRuleCollector;
use Shopware\Bundle\CartBundle\Infrastructure\Validator\Collector\ProductOfCategoriesRuleCollector;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;

class RuleDataCollectorRegistryTest extends TestCase
{
    public function testCollectorIteration()
    {
        $productCollector = $this->createMock(ProductOfCategoriesRuleCollector::class);
        $productCollector->expects($this->once())->method('collect');

        $orderCountCollector = $this->createMock(OrderCountRuleCollector::class);
        $orderCountCollector->expects($this->once())->method('collect');

        $orderStateCollector = $this->createMock(OrderClearedStateRuleCollector::class);
        $orderStateCollector->expects($this->once())->method('collect');

        $registry = new RuleDataCollectorRegistry([$productCollector, $orderCountCollector, $orderStateCollector]);

        $registry->collect(
            $this->createMock(CalculatedCart::class),
            $this->createMock(ShopContext::class),
            new RuleCollection([])
        );
    }
}
