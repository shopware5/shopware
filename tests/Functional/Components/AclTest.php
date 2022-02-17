<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Components;

use Enlight_Components_Test_TestCase;
use Shopware_Components_Acl;

class AclTest extends Enlight_Components_Test_TestCase
{
    private Shopware_Components_Acl $acl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->acl = Shopware()->Acl();
    }

    public function testAclShouldContainRoles(): void
    {
        $roles = $this->acl->getRoles();
        static::assertGreaterThan(0, \count($roles));
    }

    public function testAclShouldContainResources(): void
    {
        $resources = $this->acl->getResources();
        static::assertGreaterThan(0, \count($resources));
    }

    public function testTestNotExistingRoleShouldThrowException(): void
    {
        $this->expectException('Zend_Acl_Exception');
        $role = 'this_is_a_not_existing_role';
        $privilege = 'create';
        $resource = 'debug_test';

        $this->acl->isAllowed($role, $resource, $privilege);
    }

    public function testTestNotExistingResourceShouldThrowException(): void
    {
        $this->expectException('Zend_Acl_Exception');
        $role = 'Test-Group1';
        $privilege = 'create';
        $resource = 'this_is_a_not_existing_resource';

        $this->acl->isAllowed($role, $resource, $privilege);
    }

    public function testTestNotExistingPrivilegeShouldNotThrowException(): void
    {
        $role = 'local_admins';
        $privilege = 'this_is_a_not_existing_privilege';
        $resource = 'debug_test';

        static::assertTrue($this->acl->isAllowed($role, $resource, $privilege));
    }

    public function testTestLocalAdminsShouldHaveAllPrivileges(): void
    {
        $role = 'local_admins';
        $resource = 'debug_test';

        static::assertTrue($this->acl->isAllowed($role, $resource, 'create'));
        static::assertTrue($this->acl->isAllowed($role, $resource, 'read'));
        static::assertTrue($this->acl->isAllowed($role, $resource, 'update'));
        static::assertTrue($this->acl->isAllowed($role, $resource, 'delete'));
    }
}
