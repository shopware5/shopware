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

namespace Shopware\Tests\Unit\Components\Model;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Parameter;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Components\Model\QueryOperatorValidator;

class QueryBuilderTest extends TestCase
{
    /**
     * @var QueryBuilder
     */
    public $querybuilder;

    protected function setUp(): void
    {
        // Create a stub for the SomeClass class.
        $emMock = $this->createMock(EntityManager::class);

        $queryBuilder = new QueryBuilder($emMock, new QueryOperatorValidator(['foo', 'bar']));

        $this->querybuilder = $queryBuilder;
    }

    public function testAddFilterBehavior()
    {
        $this->querybuilder->setParameter('foo', 'far');
        $this->querybuilder->addFilter(['yoo' => 'yar', 'bar' => 'boo']);
        $this->querybuilder->addFilter(['yaa' => 'yaa', 'baa' => 'baa']);

        $expression = $this->querybuilder->getDQLPart('where');
        $parts = $expression->getParts();

        static::assertCount(4, $parts);
        static::assertSame(strpos($parts[0]->getRightExpr(), ':yoo'), 0);
        static::assertSame(strpos($parts[1]->getRightExpr(), ':bar'), 0);
        static::assertSame(strpos($parts[2]->getRightExpr(), ':yaa'), 0);
        static::assertSame(strpos($parts[3]->getRightExpr(), ':baa'), 0);

        $result = $this->querybuilder->getParameters()->toArray();

        $expectedResult = [
            new Parameter('foo', 'far'),
            new Parameter($parts[0]->getRightExpr(), 'yar'),
            new Parameter($parts[1]->getRightExpr(), 'boo'),
            new Parameter($parts[2]->getRightExpr(), 'yaa'),
            new Parameter($parts[3]->getRightExpr(), 'baa'),
        ];

        static::assertEquals($expectedResult, $result);
    }

    public function testAddParameterProvidesOldDoctrineSetParametersBehavior()
    {
        $this->querybuilder->setParameter('foo', 'bar');
        $this->querybuilder->setParameter('bar', 'foo');

        $result = $this->querybuilder->getParameters()->toArray();

        $expectedResult = [
            new Parameter('foo', 'bar'),
            new Parameter('bar', 'foo'),
        ];

        static::assertEquals($expectedResult, $result);
    }

    public function testSimpleFilter()
    {
        $filter = [
            'name' => 'myname',
        ];

        $this->querybuilder->addFilter($filter);

        /** @var Andx $expression */
        $expression = $this->querybuilder->getDQLPart('where');
        $parts = $expression->getParts();

        static::assertCount(1, $parts);
        static::assertSame(strpos($parts[0]->getRightExpr(), ':name'), 0);

        $expectedResult = [
            new Comparison('name', 'LIKE', $parts[0]->getRightExpr()),
        ];

        static::assertEquals($expectedResult, $parts);

        $params = $this->querybuilder->getParameters()->toArray();
        $expectedResult = [
            new Parameter($parts[0]->getRightExpr(), 'myname'),
        ];
        static::assertEquals($expectedResult, $params);
    }

    public function testMultipleSimpleFilter()
    {
        $filter = [
            'name' => 'myname',
            'foo' => 'fao',
        ];

        $this->querybuilder->addFilter($filter);

        /** @var Andx $expression */
        $expression = $this->querybuilder->getDQLPart('where');
        $parts = $expression->getParts();

        static::assertCount(2, $parts);
        static::assertSame(strpos($parts[0]->getRightExpr(), ':name'), 0);
        static::assertSame(strpos($parts[1]->getRightExpr(), ':foo'), 0);

        $expectedResult = [
            new Comparison('name', 'LIKE', $parts[0]->getRightExpr()),
            new Comparison('foo', 'LIKE', $parts[1]->getRightExpr()),
        ];

        static::assertEquals($expectedResult, $parts);

        $params = $this->querybuilder->getParameters()->toArray();
        $expectedResult = [
            new Parameter($parts[0]->getRightExpr(), 'myname'),
            new Parameter($parts[1]->getRightExpr(), 'fao'),
        ];
        static::assertEquals($expectedResult, $params);
    }

    /**
     * Test that multiple filters on the same property stack
     */
    public function testOverwriteFilter()
    {
        $filter = [
            [
                'property' => 'number',
                'expression' => '!=',
                'value' => '500',
            ],
            [
                'property' => 'number',
                'expression' => '!=',
                'value' => '100',
            ],
            [
                'property' => 'number',
                'expression' => 'foo',
                'value' => '100',
            ],
        ];

        $this->querybuilder->addFilter($filter);

        /** @var Andx $expression */
        $expression = $this->querybuilder->getDQLPart('where');
        $parts = $expression->getParts();

        static::assertCount(3, $parts);
        static::assertSame(strpos($parts[0]->getRightExpr(), ':number'), 0);
        static::assertSame(strpos($parts[1]->getRightExpr(), ':number'), 0);
        static::assertNotEquals($parts[0]->getRightExpr(), $parts[1]->getRightExpr());

        $expectedResult = [
            new Comparison('number', '!=', $parts[0]->getRightExpr()),
            new Comparison('number', '!=', $parts[1]->getRightExpr()),
            new Comparison('number', 'foo', $parts[2]->getRightExpr()),
        ];

        static::assertEquals($parts, $expectedResult);

        $params = $this->querybuilder->getParameters()->toArray();
        $expectedResult = [
            new Parameter($parts[0]->getRightExpr(), '500'),
            new Parameter($parts[1]->getRightExpr(), '100'),
            new Parameter($parts[2]->getRightExpr(), '100'),
        ];
        static::assertEquals($expectedResult, $params);
    }

    public function testComplexFilter()
    {
        $filter = [[
            'property' => 'number',
            'expression' => '>',
            'value' => '500',
        ]];

        $this->querybuilder->addFilter($filter);

        /** @var Andx $expression */
        $expression = $this->querybuilder->getDQLPart('where');
        $parts = $expression->getParts();

        static::assertCount(1, $parts);
        static::assertSame(strpos($parts[0]->getRightExpr(), ':number'), 0);

        $expectedResult = [
            new Comparison('number', '>', $parts[0]->getRightExpr()),
        ];

        static::assertEquals($expectedResult, $parts);

        $params = $this->querybuilder->getParameters()->toArray();
        $expectedResult = [
            new Parameter($parts[0]->getRightExpr(), '500'),
        ];
        static::assertEquals($expectedResult, $params);
    }

    public function testMixedFilter()
    {
        $filter = [
            [
                'property' => 'number',
                'expression' => '>',
                'value' => '500',
            ],
            'name' => 'myname',
        ];

        $this->querybuilder->addFilter($filter);

        /** @var Andx $expression */
        $expression = $this->querybuilder->getDQLPart('where');
        $parts = $expression->getParts();

        static::assertCount(2, $parts);
        static::assertSame(strpos($parts[0]->getRightExpr(), ':number'), 0);
        static::assertSame(strpos($parts[1]->getRightExpr(), ':name'), 0);

        $expectedResult = [
            new Comparison('number', '>', $parts[0]->getRightExpr()),
            new Comparison('name', 'LIKE', $parts[1]->getRightExpr()),
        ];
        static::assertEquals($expectedResult, $parts);

        $params = $this->querybuilder->getParameters()->toArray();
        $expectedResult = [
            new Parameter($parts[0]->getRightExpr(), '500'),
            new Parameter($parts[1]->getRightExpr(), 'myname'),
        ];
        static::assertEquals($expectedResult, $params);
    }

    public function testAddFilterAfterSetParameter()
    {
        $this->querybuilder->setParameter('name', 'myname');

        $filter = [
            'examplekey' => 'examplevalue',
        ];

        $this->querybuilder->addFilter($filter);

        /** @var Andx $expression */
        $expression = $this->querybuilder->getDQLPart('where');
        $parts = $expression->getParts();

        static::assertCount(1, $parts);
        static::assertSame(strpos($parts[0]->getRightExpr(), ':examplekey'), 0);

        $expectedResult = [
            new Comparison('examplekey', 'LIKE', $parts[0]->getRightExpr()),
        ];
        static::assertEquals($expectedResult, $parts);

        $params = $this->querybuilder->getParameters()->toArray();
        $expectedResult = [
            new Parameter('name', 'myname'),
            new Parameter($parts[0]->getRightExpr(), 'examplevalue'),
        ];
        static::assertEquals($expectedResult, $params);
    }

    public function testAddFilterArrayOfValues()
    {
        $testValues = [
            'testArrayOfNumbers' => [
                'type' => Connection::PARAM_INT_ARRAY,
                'parameterName' => 'numbers',
                'values' => [1, 2, 3],
            ],
            'testArrayOfStrings' => [
                'type' => Connection::PARAM_STR_ARRAY,
                'parameterName' => 'strings',
                'values' => ['A', 'B', 'C'],
            ],
        ];

        $filter = [];
        foreach ($testValues as $testValue) {
            $filter[] = [
                'property' => $testValue['parameterName'],
                'value' => $testValue['values'],
            ];
        }

        $this->querybuilder->addFilter($filter);

        /** @var Andx $expression */
        $expression = $this->querybuilder->getDQLPart('where');
        $parts = $expression->getParts();

        static::assertCount(2, $parts);
        static::assertSame(strpos($parts[0]->getRightExpr(), '(:number'), 0);
        static::assertSame(strpos($parts[1]->getRightExpr(), '(:strings'), 0);

        $expectedResult = [];
        $counter = 0;
        foreach ($testValues as $testValue) {
            $expectedResult[] = new Comparison($testValue['parameterName'], 'IN', $parts[$counter]->getRightExpr());
            ++$counter;
        }

        static::assertEquals($expectedResult, $parts);

        $params = $this->querybuilder->getParameters()->toArray();
        $expectedResult = [];
        $counter = 0;
        foreach ($testValues as $testValue) {
            $expectedResult[] = new Parameter(trim($parts[$counter]->getRightExpr(), '()'), $testValue['values']);
            ++$counter;
        }

        static::assertEquals($expectedResult, $params);
    }

    /**
     * @dataProvider getInvalidFilterDataProvider
     */
    public function testInvalidOperatorsThrowException(array $filter)
    {
        $this->querybuilder->resetDQLParts();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('\'%s\' is no valid operator', $filter['expression']));
        $this->querybuilder->addFilter([$filter]);
    }

    public function getInvalidFilterDataProvider()
    {
        return [
            [[
                'property' => 'id',
                'expression' => '=article.id',
                'value' => 10,
            ]],
            [[
                'property' => 'name',
                'expression' => 'select',
                'value' => 10,
            ]],
            [[
                'property' => 'id',
                'expression' => 'FOOBAR',
                'value' => 10,
            ]],
            [[
                'property' => 'name',
                'expression' => 'LOIKE',
                'value' => 10,
            ]],
        ];
    }
}
