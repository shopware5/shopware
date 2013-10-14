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

    /**
     *
     */
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

    /**
     *
     */
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

    /**
     *
     */
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
}
