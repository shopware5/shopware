<?php
use Shopware\Components\Model\QueryBuilder;

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
class Shopware_Tests_Components_Model_QueryBuilderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var QueryBuilder
     */
    public $querybuilder;

    public function setUp()
    {
        // Create a stub for the SomeClass class.
        $emMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                        ->disableOriginalConstructor()
                        ->getMock();

        $queryBuilder = new Shopware\Components\Model\QueryBuilder($emMock);

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
        $this->assertTrue(strpos($parts[0]->getRightExpr(), ':yoo') === 0);
        $this->assertTrue(strpos($parts[1]->getRightExpr(), ':bar') === 0);
        $this->assertTrue(strpos($parts[2]->getRightExpr(), ':yaa') === 0);
        $this->assertTrue(strpos($parts[3]->getRightExpr(), ':baa') === 0);

        $result = $this->querybuilder->getParameters()->toArray();

        $expectedResult = array(
            new \Doctrine\ORM\Query\Parameter('foo', 'far'),
            new \Doctrine\ORM\Query\Parameter($parts[0]->getRightExpr(), 'yar'),
            new \Doctrine\ORM\Query\Parameter($parts[1]->getRightExpr(), 'boo'),
            new \Doctrine\ORM\Query\Parameter($parts[2]->getRightExpr(), 'yaa'),
            new \Doctrine\ORM\Query\Parameter($parts[3]->getRightExpr(), 'baa'),
        );

        $this->assertEquals($expectedResult, $result);
    }

    public function testEnsureOldDoctrineSetParametersBehavior()
    {
        $this->querybuilder->setParameters(array('foo' => 'bar'));
        $this->querybuilder->setParameters(array('bar' => 'foo'));

        $result = $this->querybuilder->getParameters()->toArray();

        $expectedResult = array(
            new \Doctrine\ORM\Query\Parameter('foo', 'bar'),
            new \Doctrine\ORM\Query\Parameter('bar', 'foo')
        );

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddParameterProvidesOldDoctrineSetParametersBehavior()
    {
        $this->querybuilder->setParameters(array('foo' => 'bar'));
        $this->querybuilder->setParameters(array('bar' => 'foo'));

        $result = $this->querybuilder->getParameters()->toArray();

        $expectedResult = array(
            new \Doctrine\ORM\Query\Parameter('foo', 'bar'),
            new \Doctrine\ORM\Query\Parameter('bar', 'foo')
        );

        $this->assertEquals($expectedResult, $result);
    }

    public function testSimpleFilter()
    {
        $filter = array(
            'name' => 'myname',
        );

        $this->querybuilder->addFilter($filter);

        /** @var $expression \Doctrine\ORM\Query\Expr\Andx */
        $expression = $this->querybuilder->getDQLPart('where');
        $parts = $expression->getParts();

        $this->assertCount(1, $parts);
        $this->assertTrue(strpos($parts[0]->getRightExpr(), ':name') === 0);

        $expectedResult = array(
            new Doctrine\ORM\Query\Expr\Comparison('name', 'LIKE', $parts[0]->getRightExpr()),
        );

        $this->assertEquals($expectedResult, $parts);


        $params = $this->querybuilder->getParameters()->toArray();
        $expectedResult = array(
            new \Doctrine\ORM\Query\Parameter($parts[0]->getRightExpr(), 'myname'),
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

        /** @var $expression \Doctrine\ORM\Query\Expr\Andx */
        $expression = $this->querybuilder->getDQLPart('where');
        $parts =$expression->getParts();

        $this->assertCount(2, $parts);
        $this->assertTrue(strpos($parts[0]->getRightExpr(), ':name') === 0);
        $this->assertTrue(strpos($parts[1]->getRightExpr(), ':foo') === 0);

        $expectedResult = array(
            new Doctrine\ORM\Query\Expr\Comparison('name', 'LIKE', $parts[0]->getRightExpr()),
            new Doctrine\ORM\Query\Expr\Comparison('foo', 'LIKE', $parts[1]->getRightExpr())
        );

        $this->assertEquals($expectedResult, $parts);


        $params = $this->querybuilder->getParameters()->toArray();
        $expectedResult = array(
            new \Doctrine\ORM\Query\Parameter($parts[0]->getRightExpr(), 'myname'),
            new \Doctrine\ORM\Query\Parameter($parts[1]->getRightExpr(), 'fao'),
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

        /** @var $expression \Doctrine\ORM\Query\Expr\Andx */
        $expression = $this->querybuilder->getDQLPart('where');
        $parts = $expression->getParts();

        $this->assertCount(2, $parts);
        $this->assertTrue(strpos($parts[0]->getRightExpr(), ':number') === 0);
        $this->assertTrue(strpos($parts[1]->getRightExpr(), ':number') === 0);
        $this->assertNotEquals($parts[0]->getRightExpr(), $parts[1]->getRightExpr());

        $expectedResult = array(
            new Doctrine\ORM\Query\Expr\Comparison('number', '!=', $parts[0]->getRightExpr()),
            new Doctrine\ORM\Query\Expr\Comparison('number', '!=', $parts[1]->getRightExpr())
        );

        $this->assertEquals($parts, $expectedResult);


        $params = $this->querybuilder->getParameters()->toArray();
        $expectedResult = array(
            new \Doctrine\ORM\Query\Parameter($parts[0]->getRightExpr(), '500'),
            new \Doctrine\ORM\Query\Parameter($parts[1]->getRightExpr(), '100'),
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

        /** @var $expression \Doctrine\ORM\Query\Expr\Andx */
        $expression = $this->querybuilder->getDQLPart('where');
        $parts = $expression->getParts();

        $this->assertCount(1, $parts);
        $this->assertTrue(strpos($parts[0]->getRightExpr(), ':number') === 0);

        $expectedResult = array(
            new Doctrine\ORM\Query\Expr\Comparison('number', '>', $parts[0]->getRightExpr())
        );

        $this->assertEquals($expectedResult, $parts);


        $params = $this->querybuilder->getParameters()->toArray();
        $expectedResult = array(
            new \Doctrine\ORM\Query\Parameter($parts[0]->getRightExpr(), '500'),
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

        /** @var $expression \Doctrine\ORM\Query\Expr\Andx */
        $expression = $this->querybuilder->getDQLPart('where');
        $parts = $expression->getParts();

        $this->assertCount(2, $parts);
        $this->assertTrue(strpos($parts[0]->getRightExpr(), ':number') === 0);
        $this->assertTrue(strpos($parts[1]->getRightExpr(), ':name') === 0);

        $expectedResult = array(
            new Doctrine\ORM\Query\Expr\Comparison('number', '>', $parts[0]->getRightExpr()),
            new Doctrine\ORM\Query\Expr\Comparison('name', 'LIKE', $parts[1]->getRightExpr())
        );
        $this->assertEquals($expectedResult, $parts);


        $params = $this->querybuilder->getParameters()->toArray();
        $expectedResult = array(
            new \Doctrine\ORM\Query\Parameter($parts[0]->getRightExpr(), '500'),
            new \Doctrine\ORM\Query\Parameter($parts[1]->getRightExpr(), 'myname'),
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

        /** @var $expression \Doctrine\ORM\Query\Expr\Andx */
        $expression = $this->querybuilder->getDQLPart('where');
        $parts = $expression->getParts();

        $this->assertCount(1, $parts);
        $this->assertTrue(strpos($parts[0]->getRightExpr(), ':examplekey') === 0);

        $expectedResult = array(
            new Doctrine\ORM\Query\Expr\Comparison('examplekey', 'LIKE', $parts[0]->getRightExpr())
        );
        $this->assertEquals($expectedResult, $parts);


        $params = $this->querybuilder->getParameters()->toArray();
        $expectedResult = array(
            new \Doctrine\ORM\Query\Parameter('name', 'myname'),
            new \Doctrine\ORM\Query\Parameter($parts[0]->getRightExpr(), 'examplevalue'),
        );
        $this->assertEquals($expectedResult, $params);
    }

    public function testAddFilterArrayOfValues()
    {
        $testValues = array(
            'testArrayOfNumbers' => array(
                'type' => Doctrine\DBAL\Connection::PARAM_INT_ARRAY,
                'parameterName' => 'numbers',
                'values' => array(1, 2, 3)
            ),
            'testArrayOfStrings' => array(
                'type' => Doctrine\DBAL\Connection::PARAM_STR_ARRAY,
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

        /** @var $expression \Doctrine\ORM\Query\Expr\Andx */
        $expression = $this->querybuilder->getDQLPart('where');
        $parts = $expression->getParts();

        $this->assertCount(2, $parts);
        $this->assertTrue(strpos($parts[0]->getRightExpr(), '(:number') === 0);
        $this->assertTrue(strpos($parts[1]->getRightExpr(), '(:strings') === 0);

        $expectedResult = array();
        $counter = 0;
        foreach ($testValues as $testValue) {
            $expectedResult[] = new Doctrine\ORM\Query\Expr\Comparison($testValue['parameterName'], 'IN', $parts[$counter]->getRightExpr());
            $counter++;
        }

        $this->assertEquals($expectedResult, $parts);


        $params = $this->querybuilder->getParameters()->toArray();
        $expectedResult = array();
        $counter = 0;
        foreach ($testValues as $testValue) {
            $expectedResult[] = new \Doctrine\ORM\Query\Parameter(trim($parts[$counter]->getRightExpr(), '()'), $testValue['values'], $testValue['type']);
            $counter++;
        }

        $this->assertEquals($expectedResult, $params);
    }
}
