<?php
use Shopware\Models\Article\Esd;

/**
 *
 */
class Shopware_Tests_Models_EsdTest extends Enlight_Components_Test_TestCase
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
        'file'          => '../foobar.pdf',
        'hasSerials'       => true,
        'notification'  => true,
        'maxdownloads'  => 55,
    );

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->em = Shopware()->Models();
        $this->repo = Shopware()->Models()->getRepository('Shopware\Models\Article\Esd');
    }

    /**
     * Tear down
     */
    protected function tearDown()
    {
        $esd = $this->repo->findOneBy(array('file' => '../foobar.pdf'));

        if (!empty($esd)) {
            $this->em->remove($esd);
            $this->em->flush();
        }
        parent::tearDown();
    }

    /**
     * Test case
     */
    public function testGetterAndSetter()
    {
        $esd = new Esd();

        foreach ($this->testData as $field => $value) {
            $setMethod = 'set' . ucfirst($field);
            $getMethod = 'get' . ucfirst($field);

            $esd->$setMethod($value);

            $this->assertEquals($esd->$getMethod(), $value);
        }
    }

    /**
     * Test case
     */
    public function testFromArrayWorks()
    {
        $esd = new Esd();
        $esd->fromArray($this->testData);

        foreach ($this->testData as $fieldname => $value) {
            $getMethod = 'get' . ucfirst($fieldname);
            $this->assertEquals($esd->$getMethod(), $value);
        }
    }

    /**detail
     * Test case
     */
    public function testEsdShouldBePersisted()
    {
        $esd = new Esd();

        $articleDetail = Shopware()->Models()->getRepository('Shopware\Models\Article\Detail')->findOneBy(array('active' => true));
        $esd->setArticleDetail($articleDetail);

        $esd->fromArray($this->testData);

        $this->em->persist($esd);
        $this->em->flush();

        $esdId = $esd->getId();

        // remove esd from entity manager
        $this->em->detach($esd);
        unset($esd);

        $esd = $this->repo->find($esdId);

        foreach ($this->testData as $fieldname => $value) {
            $getMethod = 'get' . ucfirst($fieldname);
            $this->assertEquals($esd->$getMethod(), $value);
        }

        $this->assertInstanceOf('\DateTime', $esd->getDate());
    }
}

