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
 * @author     Benjamin Cremer
 * @author     $Author$
 */

/**
 * Test Class for \Shopware\Components\Api\Resource\Resource
 *
 * @category   Shopware
 * @package    Shopware_Tests
 * @subpackage Api
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @covers \Shopware\Components\Api\Resource\Resource
 */
class Shopware_Tests_Components_Api_ResourceTest extends Enlight_Components_Test_TestCase
{
    /**
     * @var \Shopware\Components\Api\Resource\Resource
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

        $this->resource = $this->getMockForAbstractClass('\Shopware\Components\Api\Resource\Resource');

        $this->resource->setManager(Shopware()->Models());
    }

    public function testResultModeShouldDefaultToArray()
    {
        $this->assertEquals($this->resource->getResultMode(), \Shopware\Components\Api\Resource\Resource::HYDRATE_ARRAY);
    }

    public function testSetResultModeShouldShouldWork()
    {
        $this->resource->setResultMode(\Shopware\Components\Api\Resource\Resource::HYDRATE_OBJECT);

        $this->assertEquals($this->resource->getResultMode(), \Shopware\Components\Api\Resource\Resource::HYDRATE_OBJECT);
    }

    public function testAutoFlushShouldDefaultToTrue()
    {
        $this->assertEquals($this->resource->getAutoFlush(), true);
    }

    public function testSetAutoFlushShouldWork()
    {
        $this->resource->setAutoFlush(false);

        $this->assertEquals($this->resource->getAutoFlush(), false);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\PrivilegeException
     */
    public function testCheckPrivilegeShouldThrowException()
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

        $this->resource->setRole('dummy');
        $this->resource->setAcl($aclMock);

        $this->resource->checkPrivilege('test');
    }

    public function testFooFlushShouldWork()
    {
        $aclMock = $this->getMockBuilder('\Shopware_Components_Acl')
                ->disableOriginalConstructor()
                ->getMock();

        $aclMock->expects($this->any())
                ->method('isAllowed')
                ->will($this->returnValue(true));

        $this->resource->setRole('dummy');
        $this->resource->setAcl($aclMock);
        $this->assertNull($this->resource->checkPrivilege('test'));
    }


}
