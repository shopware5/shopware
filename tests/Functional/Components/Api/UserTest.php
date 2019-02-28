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

namespace Shopware\Tests\Functional\Components\Api;

use Shopware\Components\Api\Resource\Resource;
use Shopware\Components\Api\Resource\User;


class UserTest extends TestCase
{
    /**
     * @var User
     */
    protected $resource;

    protected function setUp()
    {
        parent::setUp();
        $this->resource->setAcl($this->getAclMockAllowEverything());
    }

    /**
     * @return User
     */
    public function createResource()
    {
        return new User();
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ValidationException
     */
    public function testCreateWithNonUniqueEmailShouldThrowException()
    {
        $this->resource->setRole('create');

        $testData = [
            'email' => 'demo@example.com',
            'username' => 'username' . uniqid(),
            'name' => 'Max Mustermann',
            'role' => 'local_admins',
            'password' => 'fooobar',
        ];

        $this->resource->create($testData);
    }

    public function testCreateShouldBeSuccessful()
    {
        $this->resource->setRole('create');

        $date = new \DateTime();

        $date->modify('-10 days');
        $lastLogin = $date->format(\DateTime::ISO8601);

        $date->modify('+14 days');
        $lockedUntil = $date->format(\DateTime::ISO8601);

        $testData = [
            'email' => uniqid(rand()) . '@example.com',
            'username' => 'username' . uniqid(rand()),
            'name' => 'Max Mustermann',
            'roleId' => 1,
            'localeId' => 1,
            'password' => 'fooobar',
            'encoder' => 'Bcrypt',
            'apiKey' => uniqid(rand()),
            'lastLogin' => $lastLogin,
            'active' => false,
            'failedLogins' => 1,
            'lockedUntil' => $lockedUntil,
            'extendedEditor' => true,
            'disabledCache' => true,
        ];

        /** @var \Shopware\Models\User\User $user */
        $user = $this->resource->create($testData);

        $this->assertInstanceOf('\Shopware\Models\User\User', $user);
        $this->assertGreaterThan(0, $user->getId());

        // Test default values
        $this->assertEquals($user->getActive(), $testData['active']);

        $this->assertEquals($user->getEmail(), $testData['email']);
        $this->assertEquals($user->getUsername(), $testData['username']);
        $this->assertEquals($user->getName(), $testData['name']);

        $this->assertEquals($user->getRoleId(), $testData['roleId']);
        $this->assertEquals($user->getLocaleId(), $testData['localeId']);

        $this->assertEquals($user->getEncoder(), $testData['encoder']);
        $this->assertEquals($user->getApiKey(), $testData['apiKey']);
        $this->assertEquals($user->getLastLogin(), new \DateTime((string) $testData['lastLogin']));
        $this->assertEquals($user->getFailedLogins(), $testData['failedLogins']);
        $this->assertEquals($user->getLockedUntil(), new \DateTime((string) $testData['lockedUntil']));
        $this->assertEquals($user->getExtendedEditor(), $testData['extendedEditor']);
        $this->assertEquals($user->getDisabledCache(), $testData['disabledCache']);

        return $user->getId();
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneShouldBeSuccessful($id)
    {
        $this->resource->setRole('read');

        $user = $this->resource->getOne($id);
        $this->assertGreaterThan(0, $user['id']);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneShouldBeAbleToReturnObject($id)
    {
        $this->resource->setRole('read');

        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $user = $this->resource->getOne($id);

        $this->assertInstanceOf('\Shopware\Models\User\User', $user);
        $this->assertGreaterThan(0, $user->getId());
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetListShouldBeSuccessful()
    {
        $this->resource->setRole('read');

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
        $this->resource->setRole('read');

        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $result = $this->resource->getList();

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('total', $result);

        $this->assertGreaterThanOrEqual(1, $result['total']);
        $this->assertGreaterThanOrEqual(1, $result['data']);

        $this->assertInstanceOf('\Shopware\Models\User\User', $result['data'][0]);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ValidationException
     */
    public function testCreateWithInvalidDataShouldThrowValidationException()
    {
        $this->resource->setRole('create');

        $testData = [
            'email' => 'invalid',
            'username' => 'username' . uniqid(rand()),
            'name' => 'Max Mustermann',
            'role' => 'local_admins',
        ];

        $this->resource->create($testData);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testUpdateShouldBeSuccessful($id)
    {
        $this->resource->setRole('update');

        $testData = [
            'username' => 'updated' . uniqid(rand()),
            'name' => 'Max Mustermann Update',
        ];

        $user = $this->resource->update($id, $testData);

        $this->assertInstanceOf('\Shopware\Models\User\User', $user);
        $this->assertEquals($id, $user->getId());

        $this->assertEquals($user->getUsername(), $testData['username']);
        $this->assertEquals($user->getName(), $testData['name']);

        return $id;
    }

    /**
     * @depends testCreateShouldBeSuccessful
     * @expectedException \Shopware\Components\Api\Exception\ValidationException
     */
    public function testUpdateWithInvalidDataShouldThrowValidationException($id)
    {
        $this->resource->setRole('update');

        $testData = [
            'email' => 'invalid',
        ];

        $this->resource->update($id, $testData);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\NotFoundException
     */
    public function testUpdateWithInvalidIdShouldThrowNotFoundException()
    {
        $this->resource->setRole('update');

        $this->resource->update(9999999, []);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function testUpdateWithMissingIdShouldThrowParameterMissingException()
    {
        $this->resource->setRole('update');

        $this->resource->update('', []);
    }

    /**
     * @depends testUpdateShouldBeSuccessful
     */
    public function testDeleteShouldBeSuccessful($id)
    {
        $this->resource->setRole('delete');

        $user = $this->resource->delete($id);

        $this->assertInstanceOf('\Shopware\Models\User\User', $user);
        $this->assertEquals(null, $user->getId());
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\NotFoundException
     */
    public function testDeleteWithInvalidIdShouldThrowNotFoundException()
    {
        // TODO!!!
        $this->resource->setRole('delete');

        $this->resource->delete(9999999);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function testDeleteWithMissingIdShouldThrowParameterMissingException()
    {
        // TODO!!!

        $this->resource->setRole('delete');

        $this->resource->delete('');
    }

    public function testCreateWithUserRoleId()
    {
        $this->resource->setRole('create');

        $data = [
            'email' => __FUNCTION__ . uniqid(rand()) . '@example.com',
            'username' => 'user' . uniqid(rand()),
            'name' => 'Max Mustermann',
            'password' => 'fooobar',
            'roleId' => 1,
        ];

        $user = $this->resource->create($data);
        $this->assertEquals(1, $user->getRole()->getId());
    }

    public function testCreateWithUserRoleName()
    {
        $this->resource->setRole('create');

        $data = [
            'email' => __FUNCTION__ . uniqid(rand()) . '@example.com',
            'username' => 'user' . uniqid(rand()),
            'name' => 'Max Mustermann',
            'password' => 'fooobar',
            'role' => 'local_admins',
        ];

        $user = $this->resource->create($data);
        $this->assertEquals('local_admins', $user->getRole()->getName());
    }

    public function testCreateWithLocaleId()
    {
        $this->resource->setRole('create');

        $data = [
            'email' => __FUNCTION__ . uniqid(rand()) . '@example.com',
            'username' => 'user' . uniqid(rand()),
            'name' => 'Max Mustermann',
            'password' => 'fooobar',
            'role' => 'local_admins',
            'localeId' => 2,
        ];

        $user = $this->resource->create($data);
        $this->assertEquals(2, $user->getLocaleId());
    }

    public function testCreateWithLocaleName()
    {
        $this->resource->setRole('create');

        $data = [
            'email' => __FUNCTION__ . uniqid(rand()) . '@example.com',
            'username' => 'user' . uniqid(rand()),
            'name' => 'Max Mustermann',
            'password' => 'fooobar',
            'role' => 'local_admins',
            'locale' => 'en_GB',
        ];

        $user = $this->resource->create($data);
        $this->assertEquals(2, $user->getLocaleId());
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function testGetOneWithMissingIdShouldThrowParameterMissingException()
    {
        $this->resource->setRole('read');
        $this->resource->getOne('');
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\NotFoundException
     */
    public function testGetOneWithInvalidIdShouldThrowNotFoundException()
    {
        $this->resource->setRole('read');
        $this->resource->getOne(9999999);
    }

    protected function getAclMockAllowEverything()
    {
        $aclMock = $this->createMock(\Shopware_Components_Acl::class);

        $aclMock->expects($this->any())
            ->method('has')
            ->willReturn(true);

        $aclMock->expects($this->any())
            ->method('isAllowed')
            ->willReturn(true);

        return $aclMock;
    }
}
