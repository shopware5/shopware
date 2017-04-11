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

namespace Shopware\Tests\Unit\Bundle\CartBundle\Domain\Validator\Container;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\Validator\Container\XorRule;
use Shopware\Bundle\CartBundle\Domain\Validator\Data\RuleDataCollection;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContext;
use Shopware\Tests\Unit\Bundle\CartBundle\Common\FalseRule;
use Shopware\Tests\Unit\Bundle\CartBundle\Common\TrueRule;

class XorRuleTest extends TestCase
{
    public function testSingleTrueRule()
    {
        $rule = new XorRule([
            new FalseRule(),
            new TrueRule(),
            new FalseRule(),
        ]);

        $this->assertTrue(
            $rule->match(
                $this->createMock(CalculatedCart::class),
                $this->createMock(ShopContext::class),
                new RuleDataCollection()
            )
        );
    }

    public function testWithMultipleFalse()
    {
        $rule = new XorRule([
            new FalseRule(),
            new FalseRule(),
        ]);

        $this->assertFalse(
            $rule->match(
                $this->createMock(CalculatedCart::class),
                $this->createMock(ShopContext::class),
                new RuleDataCollection()
            )
        );
    }

    public function testWithMultipleTrue()
    {
        $rule = new XorRule([
            new TrueRule(),
            new TrueRule(),
            new FalseRule(),
        ]);

        $this->assertFalse(
            $rule->match(
                $this->createMock(CalculatedCart::class),
                $this->createMock(ShopContext::class),
                new RuleDataCollection()
            )
        );
    }
}
