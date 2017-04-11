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

namespace Shopware\Tests\Unit\Bundle\CartBundle\Infrastructure\Validator\Rule;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\Validator\Data\RuleDataCollection;
use Shopware\Bundle\CartBundle\Infrastructure\Validator\Data\LastOrderRuleData;
use Shopware\Bundle\CartBundle\Infrastructure\Validator\Rule\LastOrderRule;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContext;

class LastOrderRuleTest extends TestCase
{
    public function testRuleWithExactDate()
    {
        $rule = new LastOrderRule(10);

        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $date = (new \DateTime())->sub(
            new \DateInterval('P' . (int) 10 . 'D')
        );

        $this->assertTrue(
            $rule->match($cart, $context, new RuleDataCollection([
                new LastOrderRuleData($date),
            ]))
        );
    }

    public function testRuleNotMatch()
    {
        $rule = new LastOrderRule(10);

        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $date = (new \DateTime())->sub(
            new \DateInterval('P' . (int) 9 . 'D')
        );

        $this->assertFalse(
            $rule->match($cart, $context, new RuleDataCollection([
                new LastOrderRuleData($date),
            ]))
        );
    }

    public function testRuleWithDateBefore()
    {
        $rule = new LastOrderRule(10);

        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $date = (new \DateTime())->sub(
            new \DateInterval('P' . (int) 50 . 'D')
        );

        $this->assertTrue(
            $rule->match($cart, $context, new RuleDataCollection([
                new LastOrderRuleData($date),
            ]))
        );
    }

    public function testWithoutDataObject()
    {
        $rule = new LastOrderRule(10);

        $cart = $this->createMock(CalculatedCart::class);

        $context = $this->createMock(ShopContext::class);

        $this->assertFalse(
            $rule->match($cart, $context, new RuleDataCollection())
        );
    }
}
