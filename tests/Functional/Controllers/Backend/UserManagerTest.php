<?php
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

namespace Shopware\Tests\Functional\Controllers\Backend;

class UserManagerTest extends \Enlight_Components_Test_Controller_TestCase
{
    /**
     * Temporary user data
     *
     * @var array
     */
    protected $temporaryUserData = [
        'username' => 'UserManagerTemporaryUser',
        'password' => 'test',
        'localeId' => 1,
        'roleId' => 1,
        'name' => 'PHPUnit Testuser',
        'email' => 'test@example.com',
        'active' => 1,
    ];

    /**
     * Temporary admin data
     *
     * @var array
     */
    protected $temporaryAdminUserData = [
        'id' => null,
        'localeId' => 1,
        'roleId' => 1,
        'active' => 1,
        'username' => 'testUserManagerAdmin',
        'name' => 'User Manager Admin Test generated user',
        'email' => 'test@usermanager.php',
        'password' => 'testUserManagerAdmin',
        'admin' => true,
        'encoder' => 'Bcrypt',
        'disabledCache' => false,
        'lockedUntil' => '1999-01-01 00:00:00',
    ];

    public function setUp(): void
    {
        // Parent will not be called since parent::setUp destroys the session and we need it.

        // Clear entitymanager to prevent weird 'model shop not persisted' errors.
        Shopware()->Models()->clear();

        $this->disableAuth();
    }

    /**
     * Verify that we can not login with a user that doesn't exists (yet)
     */
    public function testWrongAdminLogin()
    {
        $this->Request()->setMethod('POST');

        $this->Request()->setParams([
            'username' => $this->temporaryAdminUserData['username'],
            'password' => uniqid('t', false),
            'locale' => $this->temporaryAdminUserData['localeId'],
        ]);

        $this->dispatch('backend/Login/login');
        static::assertFalse($this->View()->success);
    }

    /**
     * Creates an admin user that will be used to verify other tests that require authentication to be enabled.
     */
    public function testCreateAdminUser()
    {
        //Delete the user in case the username is duplicated
        $this->deleteUserByUsername($this->temporaryAdminUserData['username']);

        $this->Request()->setParams($this->temporaryAdminUserData);

        $this->dispatch('/backend/UserManager/updateUser');

        //Verify that the admin user creation was successful
        static::assertTrue($this->View()->success);
    }

    /**
     * Verify that the previously created admin user can login with a correct password
     *
     * @depends testCreateAdminUser
     */
    public function testAdminLogin()
    {
        $this->enableAuth();

        $this->resetRequest();
        $this->Request()->setParams([
           'username' => $this->temporaryAdminUserData['username'],
           'password' => $this->temporaryAdminUserData['password'],
        ]);

        $this->dispatch('/backend/Login/login');
        static::assertTrue($this->View()->success);

        /*
         * Fill in session data into s_core_sessions_backend
         */
        $this->dispatch('/backend');
        static::assertNotEmpty(Shopware()->Session()->Auth->sessionID);

        $this->resetRequest()
        ->resetResponse();

        $this->Request()->setParam('password', $this->temporaryAdminUserData['password']);
        $this->dispatch('backend/Login/validatePassword');

        static::assertTrue($this->View()->success);
        static::assertEquals(1, Shopware()->BackendSession()->offsetGet('passwordVerified'));
    }

    /**
     * Test user creation, note that this test requires testAdminLogin to pass since it's an action protected
     * by double password verification.
     */
    public function testUserAdd()
    {
        // Delete the user in case the username is duplicated
        $this->deleteUserByUsername($this->temporaryUserData['username']);

        $this->Request()->setParams($this->temporaryUserData);

        $this->dispatch('backend/UserManager/updateUser');

        static::assertTrue($this->View()->success);
        static::assertEquals(
            $this->temporaryUserData['username'],
            $this->View()->data['username']
        );

        return $this->temporaryUserData['username'];
    }

    /**
     * Test edit of users
     *
     * @depends testUserAdd
     */
    public function testUserEdit($username)
    {
        $this->resetRequest()
        ->resetResponse();

        $user = $this->getUserByUsername($username);

        $randomName = sprintf('RandomName_%s', md5(rand(0, time())));

        static::assertGreaterThan(0, $user['id']);

        // Update the username
        $this->Request()->setParam('id', $user['id']);
        $this->Request()->setParam('name', $randomName);

        $this->dispatch('backend/UserManager/updateUser');
        static::assertTrue($this->View()->success);

        // Verify that the username has effectively changed in the database
        $user = $this->getUserByUsername($username);

        static::assertEquals($randomName, $user['name']);

        return $username;
    }

    /**
     * Test deleting of users
     *
     * @depends testUserEdit
     */
    public function testUserDelete($username)
    {
        $user = $this->getUserByUsername($username);

        static::assertGreaterThan(0, $user['id']);

        $this->Request()->setParam('id', $user['id']);
        $this->dispatch('backend/UserManager/deleteUser');

        static::assertTrue(
            $this->View()->success,
            sprintf(
                'User %s with id %s not found',
                $user['id'],
                $user['username']
            )
        );
    }

    /**
     * Test loading of backend users
     */
    public function testUserList()
    {
        $this->dispatch('backend/UserManager/getUsers');
        static::assertTrue($this->View()->success);
        static::assertGreaterThan(0, count($this->View()->data));
        static::assertEquals($this->View()->total, count($this->View()->data));
    }

    /**
     * Test load details for a random user
     */
    public function testUserDetails()
    {
        $user = $this->getRandomUser();

        $this->Request()->setParam('id', $user['id']);
        $this->dispatch('backend/UserManager/getUserDetails');

        // Check if request was successful
        static::assertTrue($this->View()->success);
        static::assertEquals($this->View()->total, 1);

        // Check that returning data is an array
        static::assertTrue(is_array($this->View()->data));

        // Check that data matches the requested one
        static::assertEquals($user['id'], $this->View()->data['id']);

        // Check that result does not contain passwords
        static::assertNull($this->View()->data['password']);
    }

    /**
     * Test that roles could read from model
     */
    public function testListRoles()
    {
        $this->dispatch('backend/UserManager/getRoles');

        static::assertTrue($this->View()->success);
        static::assertGreaterThan(0, count($this->View()->data));
        static::assertEquals($this->View()->total, count($this->View()->data));
    }

    /**
     * Test creating of roles, note that this test requires testAdminLogin to pass since it's an action protected
     * by double password verification.
     *
     * @return string
     */
    public function testCreateRole()
    {
        $randomRoleName = md5(uniqid(rand()));
        $this->Request()->setParam('parentID', null);
        $this->Request()->setParam('name', $randomRoleName);
        $this->Request()->setParam('description', 'Test');
        $this->Request()->setParam('source', 'Test');
        $this->Request()->setParam('enabled', 1);
        $this->Request()->setParam('admin', 1);
        $this->dispatch('backend/UserManager/updateRole');
        static::assertTrue($this->View()->success);

        return $randomRoleName;
    }

    /**
     * Test editing of roles
     *
     * @depends testCreateRole
     */
    public function testEditRole($randomRoleName)
    {
        $randomRole = Shopware()->Container()
            ->get('dbal_connection')
            ->createQueryBuilder()
            ->select('r.id')
            ->from('s_core_auth_roles r')
            ->where('name = :name')
            ->setParameter('name', $randomRoleName)
            ->setMaxResults(1)
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);

        static::assertGreaterThan(0, $randomRole['id']);

        $this->Request()->setParam('id', $randomRole['id']);
        $this->Request()->setParam('enabled', false);
        $this->dispatch('backend/UserManager/updateRole');
        static::assertTrue($this->View()->success);

        return $randomRole['id'];
    }

    /**
     * Test deleting of roles
     *
     * @depends testEditRole
     */
    public function testDeleteRole($randomRoleId)
    {
        $this->Request()->setParam('id', $randomRoleId);
        $this->dispatch('backend/UserManager/deleteRole');

        static::assertTrue($this->View()->success);
    }

    /**
     * Gets a random user from the database
     */
    public function getRandomUser()
    {
        return $this->getBaseUserQuery()
            ->setMaxResults(1)
            ->orderBy('RAND(id)')
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Helper method to enable authentication
     */
    private function enableAuth()
    {
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth(false);
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl(false);
    }

    /**
     * Helper method to disable authentication
     */
    private function disableAuth()
    {
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth(true);
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl(true);
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getBaseUserQuery()
    {
        $dbal = Shopware()->Container()->get('dbal_connection');

        return $dbal->createQueryBuilder()
            ->select('a.id, a.name, a.username')
            ->from('s_core_auth a');
    }

    /**
     * Helper method to retrieve username data by username
     *
     * @param string $username
     */
    private function getUserByUsername($username)
    {
        return $this->getBaseUserQuery()
            ->where('username = :username')
            ->setParameter('username', $username)
            ->setMaxResults(1)
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Deletes a user by username
     *
     * @param string $name
     */
    private function deleteUserByUsername($name)
    {
        //Delete the admin user if it exists before attempting to create it (else we will have a duplicate user error)
        Shopware()->Container()
            ->get('dbal_connection')
            ->executeQuery(
                'DELETE FROM s_core_auth WHERE username=:username',
                ['username' => $name]
            );
    }
}
