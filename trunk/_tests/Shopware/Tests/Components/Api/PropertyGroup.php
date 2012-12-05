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
 * @covers \Shopware\Components\Api\Resource\PropertyGroup
 */
class Shopware_Tests_Components_Api_PropertyGroupTest extends Enlight_Components_Test_TestCase
{
    /**
     * @var \Shopware\Components\Api\Resource\PropertyGroup
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

        $this->resource = new \Shopware\Components\Api\Resource\PropertyGroup();
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

    /**
     * @expectedException \Shopware\Components\Api\Exception\CustomValidationException
     */
    public function testCreateShouldThrowCustomValidationException()
    {
        $testData = array(
            'position' => 1,
            'comparable' => 1,
            'sortmode' => 2

        );

        $group = $this->resource->create($testData);
    }


    public function testCreateShouldBeSuccessful()
    {
        $testData = array(
            "name" => "Eigenschaft1",
            'position' => 1,
            'comparable' => 1,
            'sortmode' => 2

        );

        $group = $this->resource->create($testData);

        $this->assertInstanceOf('\Shopware\Models\Property\Group', $group);
        $this->assertGreaterThan(0, $group->getId());

        $this->assertEquals($group->getPosition(), $testData['position']);
        $this->assertEquals($group->getComparable(), $testData['comparable']);
        $this->assertEquals($group->getSortMode(), $testData['sortmode']);

        return $group->getId();
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneShouldBeSuccessful($id)
    {
        $group = $this->resource->getOne($id);
        $this->assertGreaterThan(0, $group['id']);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneShouldBeAbleToReturnObject($id)
    {
        $this->resource->setResultMode(1);
        $group = $this->resource->getOne($id);

        $this->assertInstanceOf('\Shopware\Models\Property\Group', $group);
        $this->assertGreaterThan(0, $group->getId());
    }

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

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetListShouldBeAbleToReturnObjects()
    {
        $this->resource->setResultMode(\Shopware\Components\Api\Resource\Resource::HYDRATE_OBJECT);
        $result = $this->resource->getList();

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('total', $result);

        $this->assertGreaterThanOrEqual(1, $result['total']);
        $this->assertGreaterThanOrEqual(1, $result['data']);

        $this->assertInstanceOf('\Shopware\Models\Property\Group', $result['data'][0]);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testUpdateShouldBeSuccessful($id)
    {
        $testData = array(
            'name'   => uniqid() . 'testProperty',
            'sortmode'   => 99,
        );

        $group = $this->resource->update($id, $testData);

        $this->assertInstanceOf('\Shopware\Models\Property\Group', $group);
        $this->assertEquals($id, $group->getId());

        $this->assertEquals($group->getName(), $testData['name']);
        $this->assertEquals($group->getSortMode(), $testData['sortmode']);

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
        $group = $this->resource->delete($id);

        $this->assertInstanceOf('\Shopware\Models\Property\Group', $group);
        $this->assertEquals(null, $group->getId());
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
