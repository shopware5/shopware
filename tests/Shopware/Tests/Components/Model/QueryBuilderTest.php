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

    /*
     *
     */
    public function testAddFilterBehavior()
    {
        $this->querybuilder->setParameters(array('foo' => 'far'));
        $this->querybuilder->addFilter(array('yoo' => 'yar', 'bar' => 'boo'));
        $this->querybuilder->addFilter(array('yaa' => 'yaa', 'baa' => 'baa'));

        $result = $this->querybuilder->getParameters();

        $expectedResult = array(
            'foo' => 'far',
            'yoo' => 'yar',
            'bar' => 'boo',
            'yaa' => 'yaa',
            'baa' => 'baa',
        );

        $this->assertEquals($expectedResult, $result);
    }

}
