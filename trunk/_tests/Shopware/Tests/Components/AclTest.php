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
 * @subpackage Acl
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @version    $Id$
 * @author     Benjamin Cremer
 * @author     $Author$
 */

/**
 * Test Class for Shopware_Components_Acl
 *
 * @category   Shopware
 * @package    Shopware_Tests
 * @subpackage Acl
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @group      sth
 */
class Shopware_Tests_Components_AclTest extends Enlight_Components_Test_TestCase
{
    /**
     * @var \Shopware_Components_Acl $acl
     */
    private $acl;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->acl = Shopware()->Acl();
    }

    /**
     * Test case
     */
    public function testAclShouldContainRoles()
    {
        $roles = $this->acl->getRoles();
        $this->assertGreaterThan(0, count($roles));
    }

    /**
     * Test case
     */
    public function testAclShouldContainResources()
    {
        $resources = $this->acl->getResources();
        $this->assertGreaterThan(0, count($resources));
    }

    /**
     * Test case
     * @expectedException Zend_Acl_Exception
     */
    public function testTestNotExistingRoleShouldThrowException()
    {
        $role = 'this_is_a_not_existing_role';
        $privilege = 'create';
        $resource = 'debug_test';

        $this->acl->isAllowed($role, $resource, $privilege);
    }

    /**
     * Test case
     * @expectedException Zend_Acl_Exception
     */
    public function testTestNotExistingResourceShouldThrowException()
    {
        $role = 'Test-Group1';
        $privilege = 'create';
        $resource = 'this_is_a_not_existing_resource';

        $this->acl->isAllowed($role, $resource, $privilege);
    }

    /**
     * Test case
     */
    public function testTestNotExistingPrivilegeShouldNotThrowException()
    {
        $role = 'local_admins';
        $privilege = 'this_is_a_not_existing_privilege';
        $resource = 'debug_test';

        $this->assertTrue($this->acl->isAllowed($role, $resource, $privilege));
    }

    /**
     * Test case
     */
    public function testTestLocalAdminsShouldHaveAllPrivileges()
    {
        $role = 'local_admins';
        $resource = 'debug_test';

        $this->assertTrue($this->acl->isAllowed($role, $resource, 'create'));
        $this->assertTrue($this->acl->isAllowed($role, $resource, 'read'));
        $this->assertTrue($this->acl->isAllowed($role, $resource, 'update'));
        $this->assertTrue($this->acl->isAllowed($role, $resource, 'delete'));
    }
 }
