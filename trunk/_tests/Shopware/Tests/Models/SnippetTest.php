<?php
use Shopware\Models\Snippet\Snippet;

/**
 * Test case
 *
 * @link http://www.shopware.de
 * @copyright Copyright (c) 2012, shopware AG
 * @author Benjamin Cremer
 * @package Shopware
 * @subpackage Snippet
 */
class Shopware_Tests_Models_SnippetTest extends Enlight_Components_Test_TestCase
{
    /**
     * @var Shopware\Components\Model\ModelManager
     */
    protected $em;

    /**
     * @var Shopware\Models\User\Repository
     */
    protected $repo;

    /**
     * @var array
     */
    public $testData = array(
        'namespace' => 'unit/test/snippettestcase',
        'name'      => 'ErrorIndexTitle',
        'value'     => 'Fehler',
        'shopid'    => '1',
        'localeId'  => '1',
    );

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->em = Shopware()->Models();
        $this->repo = Shopware()->Models()->getRepository('Shopware\Models\Snippet\Snippet');
    }

    /**
     * Tear down
     */
    protected function tearDown()
    {
        $snippet = $this->repo->findOneBy(array('namespace' => 'unit/test/snippettestcase'));

        if (!empty($snippet)) {
            $this->em->remove($snippet);
            $this->em->flush();
        }
        parent::tearDown();
    }

    /**
     * Test case
     */
    public function testGetterAndSetter()
    {
        $snippet = new Snippet();

        foreach ($this->testData as $field => $value) {
            $setMethod = 'set' . ucfirst($field);
            $getMethod = 'get' . ucfirst($field);

            $snippet->$setMethod($value);

            $this->assertEquals($snippet->$getMethod(), $value);
        }
    }

    /**
     * Test case
     */
    public function testFromArrayWorks()
    {
        $snippet = new Snippet();
        $snippet->fromArray($this->testData);

        foreach ($this->testData as $fieldname => $value) {
            $getMethod = 'get' . ucfirst($fieldname);
            $this->assertEquals($snippet->$getMethod(), $value);
        }
    }

    /**
     * Test case
     */
    public function testShouldBePersisted()
    {
        $snippet = new Snippet();
        $snippet->fromArray($this->testData);

        $this->em->persist($snippet);
        $this->em->flush();

        $snippetId = $snippet->getId();

        // remove from entity manager
        $this->em->detach($snippet);
        unset($snippet);

        $snippet = $this->repo->find($snippetId);

        foreach ($this->testData as $fieldname => $value) {
            $getMethod = 'get' . ucfirst($fieldname);
            $this->assertEquals($snippet->$getMethod(), $value);
        }

        $this->assertInstanceOf('\DateTime', $snippet->getCreated());
        $this->assertInstanceOf('\DateTime', $snippet->getUpdated());
    }

}

