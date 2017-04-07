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

namespace Shopware\Tests\Unit\Bundle\CartBundle\Infrastructure\Payment;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\Validator\Collector\RuleDataCollectorRegistry;
use Shopware\Bundle\CartBundle\Domain\Validator\Data\RuleDataCollection;
use Shopware\Bundle\CartBundle\Domain\Validator\Rule\Rule;
use Shopware\Bundle\CartBundle\Domain\Validator\ValidatableFilter;
use Shopware\Bundle\StoreFrontBundle\Struct\PaymentMethod;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class RiskManagementPaymentFilterTest extends TestCase
{
    public function testPaymentsWithoutRules()
    {
        $payments = [
            new PaymentMethod(1, 'cash', 'Cash', 'payment/cash'),
        ];

        $filter = new ValidatableFilter(
            new RuleDataCollectorRegistry([])
        );

        $context = $this->createMock(ShopContext::class);

        $cart = $this->createMock(CalculatedCart::class);

        $this->assertSame($payments, $filter->filter($payments, $cart, $context));
    }

    public function testPaymentsWithRule()
    {
        $withRule = new PaymentMethod(2, 'debit', 'Debit', 'debit');

        $withRule->setRule(new TrueRule());

        $payments = [
            new PaymentMethod(1, 'cash', 'Cash', 'payment/cash'),
            $withRule,
        ];

        $filter = new ValidatableFilter(
            new RuleDataCollectorRegistry([])
        );

        $context = $this->createMock(ShopContext::class);

        $cart = $this->createMock(CalculatedCart::class);

        $this->assertEquals(
            [new PaymentMethod(1, 'cash', 'Cash', 'payment/cash')],
            $filter->filter($payments, $cart, $context)
        );
    }
}

class TrueRule extends Rule
{
    public function match(
        CalculatedCart $calculatedCart,
        ShopContextInterface $context,
        RuleDataCollection $collection
    ): bool {
        return true;
    }
}
