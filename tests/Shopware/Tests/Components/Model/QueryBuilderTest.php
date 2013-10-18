<?php
class Shopware_Tests_Components_Model_QueryBuilderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Shopware\Components\Model\QueryBuilder
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

        $result = $this->querybuilder->getParameters()->toArray();

        $expectedResult = array(
            new \Doctrine\ORM\Query\Parameter('foo', 'far'),
            new \Doctrine\ORM\Query\Parameter('yoo', 'yar'),
            new \Doctrine\ORM\Query\Parameter('bar', 'boo'),
            new \Doctrine\ORM\Query\Parameter('yaa', 'yaa'),
            new \Doctrine\ORM\Query\Parameter('baa', 'baa'),
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

        $expectedResult = array(
            new Doctrine\ORM\Query\Expr\Comparison('name', 'LIKE', ':name'),
        );

        $this->assertEquals($expectedResult, $expression->getParts());


        $params = $this->querybuilder->getParameters()->toArray();
        $expectedResult = array(
            new \Doctrine\ORM\Query\Parameter(':name', 'myname'),
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

        $expectedResult = array(
            new Doctrine\ORM\Query\Expr\Comparison('name', 'LIKE', ':name'),
            new Doctrine\ORM\Query\Expr\Comparison('foo', 'LIKE', ':foo')
        );

        $this->assertEquals($expectedResult, $expression->getParts());


        $params = $this->querybuilder->getParameters()->toArray();
        $expectedResult = array(
            new \Doctrine\ORM\Query\Parameter(':name', 'myname'),
            new \Doctrine\ORM\Query\Parameter(':foo', 'fao'),
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

        $expectedResult = array(
            new Doctrine\ORM\Query\Expr\Comparison('number', '>', ':number')
        );

        $this->assertEquals($expectedResult, $parts);

        $params = $this->querybuilder->getParameters()->toArray();
        $expectedResult = array(
            new \Doctrine\ORM\Query\Parameter(':number', '500'),
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

        $expectedResult = array(
            new Doctrine\ORM\Query\Expr\Comparison('number', '>', ':number'),
            new Doctrine\ORM\Query\Expr\Comparison('name', 'LIKE', ':name')
        );
        $this->assertEquals($expectedResult, $parts);


        $params = $this->querybuilder->getParameters()->toArray();
        $expectedResult = array(
            new \Doctrine\ORM\Query\Parameter(':number', '500'),
            new \Doctrine\ORM\Query\Parameter(':name', 'myname'),
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

        $expectedResult = array(
            new Doctrine\ORM\Query\Expr\Comparison('examplekey', 'LIKE', ':examplekey')
        );
        $this->assertEquals($expectedResult, $parts);

        $params = $this->querybuilder->getParameters()->toArray();
        $expectedResult = array(
            new \Doctrine\ORM\Query\Parameter('name', 'myname'),
            new \Doctrine\ORM\Query\Parameter(':examplekey', 'examplevalue'),
        );
        $this->assertEquals($expectedResult, $params);
    }
}
