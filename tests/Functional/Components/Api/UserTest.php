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

    protected function setUp(): void
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

    public function testCreateWithNonUniqueEmailShouldThrowException()
    {
        $this->expectException('Shopware\Components\Api\Exception\ValidationException');
        $this->resource->setRole('create');

        $testData = [
            'email' => 'demo@example.com',
            'username' => 'username' . uniqid(),
            'name' => 'Max Mustermann',
            'role' => 'local_admins',
            'password' => 'fooobar',
        ];

        $this->resource->create($testData);
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

        static::assertInstanceOf('\Shopware\Models\User\User', $user);
        static::assertGreaterThan(0, $user->getId());

        // Test default values
        static::assertEquals($user->getActive(), $testData['active']);

        static::assertEquals($user->getEmail(), $testData['email']);
        static::assertEquals($user->getUsername(), $testData['username']);
        static::assertEquals($user->getName(), $testData['name']);

        static::assertEquals($user->getRoleId(), $testData['roleId']);
        static::assertEquals($user->getLocaleId(), $testData['localeId']);

        static::assertEquals($user->getEncoder(), $testData['encoder']);
        static::assertEquals($user->getApiKey(), $testData['apiKey']);
        static::assertEquals($user->getLastLogin(), new \DateTime((string) $testData['lastLogin']));
        static::assertEquals($user->getFailedLogins(), $testData['failedLogins']);
        static::assertEquals($user->getLockedUntil(), new \DateTime((string) $testData['lockedUntil']));
        static::assertEquals($user->getExtendedEditor(), $testData['extendedEditor']);
        static::assertEquals($user->getDisabledCache(), $testData['disabledCache']);

        return $user->getId();
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneShouldBeSuccessful($id)
    {
        $this->resource->setRole('read');

        $user = $this->resource->getOne($id);
        static::assertGreaterThan(0, $user['id']);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneShouldBeAbleToReturnObject($id)
    {
        $this->resource->setRole('read');

        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $user = $this->resource->getOne($id);

        static::assertInstanceOf('\Shopware\Models\User\User', $user);
        static::assertGreaterThan(0, $user->getId());
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetListShouldBeSuccessful()
    {
        $this->resource->setRole('read');

        $result = $this->resource->getList();

        static::assertArrayHasKey('data', $result);
        static::assertArrayHasKey('total', $result);

        static::assertGreaterThanOrEqual(1, $result['total']);
        static::assertGreaterThanOrEqual(1, $result['data']);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetListShouldBeAbleToReturnObjects()
    {
        $this->resource->setRole('read');

        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $result = $this->resource->getList();

        static::assertArrayHasKey('data', $result);
        static::assertArrayHasKey('total', $result);

        static::assertGreaterThanOrEqual(1, $result['total']);
        static::assertGreaterThanOrEqual(1, $result['data']);

        static::assertInstanceOf('\Shopware\Models\User\User', $result['data'][0]);
    }

    public function testCreateWithInvalidDataShouldThrowValidationException()
    {
        $this->expectException('Shopware\Components\Api\Exception\ValidationException');
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

        static::assertInstanceOf('\Shopware\Models\User\User', $user);
        static::assertEquals($id, $user->getId());

        static::assertEquals($user->getUsername(), $testData['username']);
        static::assertEquals($user->getName(), $testData['name']);

        return $id;
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testUpdateWithInvalidDataShouldThrowValidationException($id)
    {
        $this->expectException('Shopware\Components\Api\Exception\ValidationException');
        $this->resource->setRole('update');

        $testData = [
            'email' => 'invalid',
        ];

        $this->resource->update($id, $testData);
    }

    public function testUpdateWithInvalidIdShouldThrowNotFoundException()
    {
        $this->expectException('Shopware\Components\Api\Exception\NotFoundException');
        $this->resource->setRole('update');

        $this->resource->update(9999999, []);
    }

    public function testUpdateWithMissingIdShouldThrowParameterMissingException()
    {
        $this->expectException('Shopware\Components\Api\Exception\ParameterMissingException');
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

        static::assertInstanceOf('\Shopware\Models\User\User', $user);
        static::assertEquals(null, $user->getId());
    }

    public function testDeleteWithInvalidIdShouldThrowNotFoundException()
    {
        $this->expectException('Shopware\Components\Api\Exception\NotFoundException');
        // TODO!!!
        $this->resource->setRole('delete');

        $this->resource->delete(9999999);
    }

    public function testDeleteWithMissingIdShouldThrowParameterMissingException()
    {
        $this->expectException('Shopware\Components\Api\Exception\ParameterMissingException');
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
        static::assertEquals(1, $user->getRole()->getId());
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
        static::assertEquals('local_admins', $user->getRole()->getName());
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
        static::assertEquals(2, $user->getLocaleId());
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
        static::assertEquals(2, $user->getLocaleId());
    }

    public function testGetOneWithMissingIdShouldThrowParameterMissingException()
    {
        $this->expectException('Shopware\Components\Api\Exception\ParameterMissingException');
        $this->resource->setRole('read');
        $this->resource->getOne('');
    }

    public function testGetOneWithInvalidIdShouldThrowNotFoundException()
    {
        $this->expectException('Shopware\Components\Api\Exception\NotFoundException');
        $this->resource->setRole('read');
        $this->resource->getOne(9999999);
    }

    protected function getAclMockAllowEverything()
    {
        $aclMock = $this->createMock(\Shopware_Components_Acl::class);

        $aclMock->expects(static::any())
            ->method('has')
            ->willReturn(true);

        $aclMock->expects(static::any())
            ->method('isAllowed')
            ->willReturn(true);

        return $aclMock;
    }
}
