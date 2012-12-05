<?php
/**
 * Shopware
 *
 * LICENSE
 *
 * Available through the world-wide-web at this URL:
 * http://shopware.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Shopware
 * @package    Shopware_Tests
 * @subpackage StringCompiler
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @version    $Id$
 * @author     Daniel Nögel
 * @author     $Author$
 */

/**
 * Test Class for \Shopware\Components\Api\Resource\Category
 *
 * @category   Shopware
 * @package    Shopware_Tests
 * @subpackage Api
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @covers \Shopware\Components\Api\Resource\Category
 */
class Shopware_Tests_Components_Api_CategoryTest extends Enlight_Components_Test_TestCase
{
    /**
     * @var \Shopware\Components\Api\Resource\Category
     */
    private $resource;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        Shopware()->Models()->clear();

        $this->resource = new \Shopware\Components\Api\Resource\Category();
        $this->resource->setManager(Shopware()->Models());
    }

    protected function getAclMock()
    {
        $aclMock = $this->getMockBuilder('\Shopware_Components_Acl')
                ->disableOriginalConstructor()
                ->getMock();

        $aclMock->expects($this->any())
                ->method('has')
                ->will($this->returnValue(true));

        $aclMock->expects($this->any())
                ->method('isAllowed')
                ->will($this->returnValue(false));

        return $aclMock;
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\PrivilegeException
     */
    public function testGetOneWithMissinPrivilegeShouldThrowPrivilegeException()
    {
        $this->resource->setRole('dummy');
        $this->resource->setAcl($this->getAclMock());

        $this->resource->getOne(1);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\NotFoundException
     */
    public function testGetOneWithInvalidIdShouldThrowNotFoundException()
    {
        $this->resource->getOne(9999999);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function testGetOneWithMissingIdShouldThrowParameterMissingException()
    {
        $this->resource->getOne('');
    }

    public function testCreateShouldBeSuccessful()
    {
        $date = new DateTime();
        $date->modify('-10 days');
        $added = $date->format(DateTime::ISO8601);

        $date->modify('-3 day');
        $changed = $date->format(DateTime::ISO8601);

        $testData = array(
            "name" => "fooobar",
            "parent"   => 1,

            "position" => 3,

            "metaKeywords" => "test, test",
            "metaDescription" => "Description Test",
            "cmsHeadline" => "cms headline",
            "cmsText" => "cmsTest",

            "active" => true,
            "blog" => false,

            "showFilterGroups" => false,
            "external" => false,
            "hidefilter" => false,
            "hideTop" => true,
            "noViewSelect" => true,

            "changed" => $changed,
            "added" => $added,

            "attribute" => array(
                1 => "test1",
                2 => "test2",
                6 => "test6"
            )
        );

        $category = $this->resource->create($testData);

        $this->assertInstanceOf('\Shopware\Models\Category\Category', $category);
        $this->assertGreaterThan(0, $category->getId());

        $this->assertEquals($category->getActive(), $testData['active']);
        $this->assertEquals($category->getMetaDescription(), $testData['metaDescription']);
        $this->assertEquals($category->getShowFilterGroups(), $testData['showFilterGroups']);
        $this->assertEquals($category->getAttribute()->getAttribute1(), $testData['attribute'][1]);
        $this->assertEquals($category->getAttribute()->getAttribute2(), $testData['attribute'][2]);
        $this->assertEquals($category->getAttribute()->getAttribute6(), $testData['attribute'][6]);

        return $category->getId();
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneShouldBeSuccessful($id)
    {
        $category = $this->resource->getOne($id);
        $this->assertGreaterThan(0, $category['id']);
    }

//    /**
//     * @depends testCreateShouldBeSuccessful
//     */
//    public function testGetOneShouldBeAbleToReturnObject($id)
//    {
//        $this->resource->setResultMode(1);
//        $category = $this->resource->getOne($id);
//
//        $this->assertInstanceOf('\Shopware\Models\Category\Category', $category);
//        $this->assertGreaterThan(0, $category->getId());
//    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetListShouldBeSuccessful()
    {
        $result = $this->resource->getList();

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('total', $result);

        $this->assertGreaterThanOrEqual(1, $result['total']);
        $this->assertGreaterThanOrEqual(1, $result['data']);
    }

//    /**
//     * @depends testCreateShouldBeSuccessful
//     */
//    public function testGetListShouldBeAbleToReturnObjects()
//    {
//        $this->resource->setResultMode(\Shopware\Components\Api\Resource\Resource::HYDRATE_OBJECT);
//        $result = $this->resource->getList();
//
//        $this->assertArrayHasKey('data', $result);
//        $this->assertArrayHasKey('total', $result);
//
//        $this->assertGreaterThanOrEqual(1, $result['total']);
//        $this->assertGreaterThanOrEqual(1, $result['data']);
//
//        $this->assertInstanceOf('\Shopware\Models\Category\Category', $result['data'][0]);
//    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testUpdateShouldBeSuccessful($id)
    {
        $testData = array(
            'active'  => true,
            'name'   => uniqid() . 'testkategorie',
            "attribute" => array(1 => "nase")
        );

        $category = $this->resource->update($id, $testData);

        $this->assertInstanceOf('\Shopware\Models\Category\Category', $category);
        $this->assertEquals($id, $category->getId());

        $this->assertEquals($category->getActive(), $testData['active']);
        $this->assertEquals($category->getName(), $testData['name']);
        $this->assertEquals($category->getAttribute()->getAttribute1(), $testData['attribute'][1]);

        return $id;
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\NotFoundException
     */
    public function testUpdateWithInvalidIdShouldThrowNotFoundException()
    {
        $this->resource->update(9999999, array());
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function testUpdateWithMissingIdShouldThrowParameterMissingException()
    {
        $this->resource->update('', array());
    }

    /**
     * @depends testUpdateShouldBeSuccessful
     */
    public function testDeleteShouldBeSuccessful($id)
    {
        $category = $this->resource->delete($id);

        $this->assertInstanceOf('\Shopware\Models\Category\Category', $category);
        $this->assertEquals(null, $category->getId());
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\NotFoundException
     */
    public function testDeleteWithInvalidIdShouldThrowNotFoundException()
    {
        $this->resource->delete(9999999);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function testDeleteWithMissingIdShouldThrowParameterMissingException()
    {
        $this->resource->delete('');
    }
}
