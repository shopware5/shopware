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

namespace Shopware\Tests\Unit\Bundle\CartBundle\Domain\Validatori;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\Validator\Collector\RuleDataCollectorRegistry;
use Shopware\Bundle\CartBundle\Domain\Validator\Data\RuleDataCollection;
use Shopware\Bundle\CartBundle\Domain\Validator\Rule\Rule;
use Shopware\Bundle\CartBundle\Domain\Validator\Validatable;
use Shopware\Bundle\CartBundle\Domain\Validator\ValidatableFilter;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContext;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;
use Shopware\Bundle\StoreFrontBundle\PaymentMethod\PaymentMethod;

class ValidatableFilterTest extends TestCase
{
    public function testClassWithoutRules()
    {
        $classes = [
            new ValidatableClass(1, null),
        ];

        $filter = new ValidatableFilter(
            new RuleDataCollectorRegistry([])
        );

        $context = $this->createMock(ShopContext::class);

        $cart = $this->createMock(CalculatedCart::class);

        $this->assertSame($classes, $filter->filter($classes, $cart, $context));
    }

    public function testClassWithRule()
    {
        $classes = [
            new ValidatableClass(2, null),
            new ValidatableClass(1, new TrueRule()),
        ];

        $filter = new ValidatableFilter(
            new RuleDataCollectorRegistry([])
        );

        $context = $this->createMock(ShopContext::class);

        $cart = $this->createMock(CalculatedCart::class);

        $this->assertEquals(
            [new ValidatableClass(2, null)],
            $filter->filter($classes, $cart, $context)
        );
    }
}

class ValidatableClass implements Validatable
{
    /**
     * @var Rule|null
     */
    private $rule;

    /**
     * @var int
     */
    private $id;

    public function __construct(int $id, ?Rule $rule)
    {
        $this->rule = $rule;
        $this->id = $id;
    }

    public function getRule(): ? Rule
    {
        return $this->rule;
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
