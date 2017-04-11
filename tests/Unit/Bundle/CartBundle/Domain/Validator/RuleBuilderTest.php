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
use Shopware\Bundle\CartBundle\Domain\Validator\Container\AndRule;
use Shopware\Bundle\CartBundle\Domain\Validator\Container\OrRule;
use Shopware\Bundle\CartBundle\Domain\Validator\Rule\RuleCollection;
use Shopware\Bundle\CartBundle\Domain\Validator\RuleBuilder;
use Shopware\Bundle\CartBundle\Infrastructure\Validator\Rule\OrderClearedStateRule;
use Shopware\Bundle\CartBundle\Infrastructure\Validator\Rule\ProductOfCategoriesRule;
use Shopware\Tests\Unit\Bundle\CartBundle\Common\FalseRule;
use Shopware\Tests\Unit\Bundle\CartBundle\Common\TrueRule;

class RuleBuilderTest extends TestCase
{
    public function testSerialize()
    {
        $collection = new RuleCollection([
            new TrueRule(),
            new AndRule([
                new TrueRule(),
                new OrRule([
                    new TrueRule(),
                    new FalseRule(),
                ]),
            ]),
        ]);

        $this->assertEquals(
            [
                ['_class' => 'Shopware\\Tests\\Unit\\Bundle\\CartBundle\\Common\\TrueRule'],
                [
                    '_class' => 'Shopware\\Bundle\\CartBundle\\Domain\\Validator\\Container\\AndRule',
                    'rules' => [
                        ['_class' => 'Shopware\\Tests\\Unit\\Bundle\\CartBundle\\Common\\TrueRule'],
                        [
                            '_class' => 'Shopware\\Bundle\\CartBundle\\Domain\\Validator\\Container\\OrRule',
                            'rules' => [
                                ['_class' => 'Shopware\\Tests\\Unit\\Bundle\\CartBundle\\Common\\TrueRule'],
                                ['_class' => 'Shopware\\Tests\\Unit\\Bundle\\CartBundle\\Common\\FalseRule'],
                            ],
                        ],
                    ],
                ],
            ],
            json_decode(json_encode($collection), true)
        );
    }

    public function testUnserialize()
    {
        $rule = new AndRule([
            new TrueRule(),
            new OrRule([
                new TrueRule(),
                new FalseRule(),
            ]),
        ]);

        $builder = new RuleBuilder();

        $this->assertEquals(
            new AndRule([
                new TrueRule(),
                new OrRule([
                    new TrueRule(),
                    new FalseRule(),
                ]),
            ]),
            $builder->build(json_decode(json_encode($rule), true))
        );
    }

    public function testUnserializeWithParameter()
    {
        $rule = new OrRule([
            new OrderClearedStateRule([1, 2, 3]),
        ]);

        $builder = new RuleBuilder();

        $this->assertEquals(
            new OrRule([
                new OrderClearedStateRule([1, 2, 3]),
            ]),
            $builder->build(json_decode(json_encode($rule), true))
        );
    }

    public function testUnserializeArray()
    {
        $rule = [
            new OrRule([
                new AndRule([
                    new TrueRule(),
                ]),
            ]),
            new AndRule([
                new TrueRule(),
                new OrRule([
                    new TrueRule(),
                    new FalseRule(),
                ]),
            ]),
        ];

        $builder = new RuleBuilder();

        $this->assertEquals(
            [
                new OrRule([
                    new AndRule([
                        new TrueRule(),
                    ]),
                ]),
                new AndRule([
                    new TrueRule(),
                    new OrRule([
                        new TrueRule(),
                        new FalseRule(),
                    ]),
                ]),
            ],
            $builder->build(json_decode(json_encode($rule), true))
        );
    }

    public function testUnserializeArrayWithParams()
    {
        $rule = [
            new OrRule([
                new AndRule([
                    new OrderClearedStateRule([1, 2, 3]),
                ]),
            ]),
            new AndRule([
                new TrueRule(),
                new OrRule([
                    new ProductOfCategoriesRule([1, 3, 4]),
                ]),
            ]),
        ];

        $builder = new RuleBuilder();

        $this->assertEquals(
            [
                new OrRule([
                    new AndRule([
                        new OrderClearedStateRule([1, 2, 3]),
                    ]),
                ]),
                new AndRule([
                    new TrueRule(),
                    new OrRule([
                        new ProductOfCategoriesRule([1, 3, 4]),
                    ]),
                ]),
            ],
            $builder->build(json_decode(json_encode($rule), true))
        );
    }
}
