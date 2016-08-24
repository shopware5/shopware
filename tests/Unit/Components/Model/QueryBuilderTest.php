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

class QueryBuilderTest extends TestCase
{
    /**
     * @var QueryBuilder
     */
    public $querybuilder;

    public function setUp()
    {
        // Create a stub for the SomeClass class.
        $emMock = $this->createMock(EntityManager::class);

        $queryBuilder = new QueryBuilder($emMock);

        $this->querybuilder = $queryBuilder;
    }

    public function testAddFilterBehavior()
    {
        $this->querybuilder->setParameters(array('foo' => 'far'));
        $this->querybuilder->addFilter(array('yoo' => 'yar', 'bar' => 'boo'));
        $this->querybuilder->addFilter(array('yaa' => 'yaa', 'baa' => 'baa'));

        $expression = $this->querybuilder->getDQLPart('where');
        $parts = $expression->getParts();

        $this->assertCount(4, $parts);
        $this->assertSame(strpos($parts[0]->getRightExpr(), ':yoo'), 0);
        $this->assertSame(strpos($parts[1]->getRightExpr(), ':bar'), 0);
        $this->assertSame(strpos($parts[2]->getRightExpr(), ':yaa'), 0);
        $this->assertSame(strpos($parts[3]->getRightExpr(), ':baa'), 0);

        $result = $this->querybuilder->getParameters()->toArray();

        $expectedResult = array(
            new Parameter('foo', 'far'),
            new Parameter($parts[0]->getRightExpr(), 'yar'),
            new Parameter($parts[1]->getRightExpr(), 'boo'),
            new Parameter($parts[2]->getRightExpr(), 'yaa'),
            new Parameter($parts[3]->getRightExpr(), 'baa'),
        );

        $this->assertEquals($expectedResult, $result);
    }

    public function testEnsureOldDoctrineSetParametersBehavior()
    {
        $this->querybuilder->setParameters(array('foo' => 'bar'));
        $this->querybuilder->setParameters(array('bar' => 'foo'));

        $result = $this->querybuilder->getParameters()->toArray();

        $expectedResult = array(
            new Parameter('foo', 'bar'),
            new Parameter('bar', 'foo')
        );

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddParameterProvidesOldDoctrineSetParametersBehavior()
    {
        $this->querybuilder->setParameters(array('foo' => 'bar'));
        $this->querybuilder->setParameters(array('bar' => 'foo'));

        $result = $this->querybuilder->getParameters()->toArray();

        $expectedResult = array(
            new Parameter('foo', 'bar'),
            new Parameter('bar', 'foo')
        );

        $this->assertEquals($expectedResult, $result);
    }

    public function testSimpleFilter()
    {
        $filter = array(
            'name' => 'myname',
        );

        $this->querybuilder->addFilter($filter);

        /** @var $expression Andx */
        $expression = $this->querybuilder->getDQLPart('where');
        $parts = $expression->getParts();

        $this->assertCount(1, $parts);
        $this->assertSame(strpos($parts[0]->getRightExpr(), ':name'), 0);

        $expectedResult = array(
            new Comparison('name', 'LIKE', $parts[0]->getRightExpr()),
        );

        $this->assertEquals($expectedResult, $parts);


        $params = $this->querybuilder->getParameters()->toArray();
        $expectedResult = array(
            new Parameter($parts[0]->getRightExpr(), 'myname'),
        );
        $this->assertEquals($expectedResult, $params);
    }

    public function testMultipleSimpleFilter()
    {
        $filter = array(
            'name' => 'myname',
            'foo'  => 'fao'
        );

        $this->querybuilder->addFilter($filter);

        /** @var $expression Andx */
        $expression = $this->querybuilder->getDQLPart('where');
        $parts = $expression->getParts();

        $this->assertCount(2, $parts);
        $this->assertSame(strpos($parts[0]->getRightExpr(), ':name'), 0);
        $this->assertSame(strpos($parts[1]->getRightExpr(), ':foo'), 0);

        $expectedResult = array(
            new Comparison('name', 'LIKE', $parts[0]->getRightExpr()),
            new Comparison('foo', 'LIKE', $parts[1]->getRightExpr())
        );

        $this->assertEquals($expectedResult, $parts);


        $params = $this->querybuilder->getParameters()->toArray();
        $expectedResult = array(
            new Parameter($parts[0]->getRightExpr(), 'myname'),
            new Parameter($parts[1]->getRightExpr(), 'fao'),
        );
        $this->assertEquals($expectedResult, $params);
    }

    /**
     * Test that multiple filters on the same property stack
     */
    public function testOverwriteFilter()
    {
        $filter = array(
            array(
                'property' => 'number',
                'expression' => '!=',
                'value' => '500'
            ),
            array(
                'property' => 'number',
                'expression' => '!=',
                'value' => '100'
            )
        );

        $this->querybuilder->addFilter($filter);

        /** @var $expression Andx */
        $expression = $this->querybuilder->getDQLPart('where');
        $parts = $expression->getParts();

        $this->assertCount(2, $parts);
        $this->assertSame(strpos($parts[0]->getRightExpr(), ':number'), 0);
        $this->assertSame(strpos($parts[1]->getRightExpr(), ':number'), 0);
        $this->assertNotEquals($parts[0]->getRightExpr(), $parts[1]->getRightExpr());

        $expectedResult = array(
            new Comparison('number', '!=', $parts[0]->getRightExpr()),
            new Comparison('number', '!=', $parts[1]->getRightExpr())
        );

        $this->assertEquals($parts, $expectedResult);


        $params = $this->querybuilder->getParameters()->toArray();
        $expectedResult = array(
            new Parameter($parts[0]->getRightExpr(), '500'),
            new Parameter($parts[1]->getRightExpr(), '100'),
        );
        $this->assertEquals($expectedResult, $params);
    }

    public function testComplexFilter()
    {
        $filter = array(array(
            'property'   => 'number',
            'expression' => '>',
            'value'      => '500'
        ));

        $this->querybuilder->addFilter($filter);

        /** @var $expression Andx */
        $expression = $this->querybuilder->getDQLPart('where');
        $parts = $expression->getParts();

        $this->assertCount(1, $parts);
        $this->assertSame(strpos($parts[0]->getRightExpr(), ':number'), 0);

        $expectedResult = array(
            new Comparison('number', '>', $parts[0]->getRightExpr())
        );

        $this->assertEquals($expectedResult, $parts);


        $params = $this->querybuilder->getParameters()->toArray();
        $expectedResult = array(
            new Parameter($parts[0]->getRightExpr(), '500'),
        );
        $this->assertEquals($expectedResult, $params);
    }

    public function testMixedFilter()
    {
        $filter = array(
            array(
                'property'   => 'number',
                'expression' => '>',
                'value'      => '500'
            ),
            'name' => 'myname',
        );

        $this->querybuilder->addFilter($filter);

        /** @var $expression Andx */
        $expression = $this->querybuilder->getDQLPart('where');
        $parts = $expression->getParts();

        $this->assertCount(2, $parts);
        $this->assertSame(strpos($parts[0]->getRightExpr(), ':number'), 0);
        $this->assertSame(strpos($parts[1]->getRightExpr(), ':name'), 0);

        $expectedResult = array(
            new Comparison('number', '>', $parts[0]->getRightExpr()),
            new Comparison('name', 'LIKE', $parts[1]->getRightExpr())
        );
        $this->assertEquals($expectedResult, $parts);


        $params = $this->querybuilder->getParameters()->toArray();
        $expectedResult = array(
            new Parameter($parts[0]->getRightExpr(), '500'),
            new Parameter($parts[1]->getRightExpr(), 'myname'),
        );
        $this->assertEquals($expectedResult, $params);
    }

    public function testAddFilterAfterSetParameter()
    {
        $this->querybuilder->setParameter('name', 'myname');

        $filter = array(
            'examplekey' => 'examplevalue'
        );

        $this->querybuilder->addFilter($filter);

        /** @var $expression Andx */
        $expression = $this->querybuilder->getDQLPart('where');
        $parts = $expression->getParts();

        $this->assertCount(1, $parts);
        $this->assertSame(strpos($parts[0]->getRightExpr(), ':examplekey'), 0);

        $expectedResult = array(
            new Comparison('examplekey', 'LIKE', $parts[0]->getRightExpr())
        );
        $this->assertEquals($expectedResult, $parts);


        $params = $this->querybuilder->getParameters()->toArray();
        $expectedResult = array(
            new Parameter('name', 'myname'),
            new Parameter($parts[0]->getRightExpr(), 'examplevalue'),
        );
        $this->assertEquals($expectedResult, $params);
    }

    public function testAddFilterArrayOfValues()
    {
        $testValues = array(
            'testArrayOfNumbers' => array(
                'type' => Connection::PARAM_INT_ARRAY,
                'parameterName' => 'numbers',
                'values' => array(1, 2, 3)
            ),
            'testArrayOfStrings' => array(
                'type' => Connection::PARAM_STR_ARRAY,
                'parameterName' => 'strings',
                'values' => array('A', 'B', 'C')
            )
        );

        $filter = array();
        foreach ($testValues as $testValue) {
            $filter[] = array(
                'property'   => $testValue['parameterName'],
                'value'      => $testValue['values']
            );
        }

        $this->querybuilder->addFilter($filter);

        /** @var $expression Andx */
        $expression = $this->querybuilder->getDQLPart('where');
        $parts = $expression->getParts();

        $this->assertCount(2, $parts);
        $this->assertSame(strpos($parts[0]->getRightExpr(), '(:number'), 0);
        $this->assertSame(strpos($parts[1]->getRightExpr(), '(:strings'), 0);

        $expectedResult = array();
        $counter = 0;
        foreach ($testValues as $testValue) {
            $expectedResult[] = new Comparison($testValue['parameterName'], 'IN', $parts[$counter]->getRightExpr());
            $counter++;
        }

        $this->assertEquals($expectedResult, $parts);


        $params = $this->querybuilder->getParameters()->toArray();
        $expectedResult = array();
        $counter = 0;
        foreach ($testValues as $testValue) {
            $expectedResult[] = new Parameter(trim($parts[$counter]->getRightExpr(), '()'), $testValue['values'], $testValue['type']);
            $counter++;
        }

        $this->assertEquals($expectedResult, $params);
    }
}
