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

namespace Shopware\Tests\Functional\Components\Api;

use DateTime;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Components\Api\Resource\Resource;
use Shopware\Components\Api\Resource\User as UserResource;
use Shopware\Models\User\User;
use Shopware_Components_Acl;

class UserTest extends TestCase
{
    /**
     * @var UserResource
     */
    protected $resource;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resource->setAcl($this->getAclMockAllowEverything());
    }

    /**
     * @return UserResource
     */
    public function createResource()
    {
        return new UserResource();
    }

    public function testCreateWithNonUniqueEmailShouldThrowException(): void
    {
        $this->expectException(ValidationException::class);
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

    public function testCreateShouldBeSuccessful(): int
    {
        $this->resource->setRole('create');

        $date = new DateTime();

        $date->modify('-10 days');
        $lastLogin = $date->format(DateTime::ISO8601);

        $date->modify('+14 days');
        $lockedUntil = $date->format(DateTime::ISO8601);

        $testData = [
            'email' => uniqid((string) rand()) . '@example.com',
            'username' => 'username' . uniqid((string) rand()),
            'name' => 'Max Mustermann',
            'roleId' => 1,
            'localeId' => 1,
            'password' => 'fooobar',
            'encoder' => 'Bcrypt',
            'apiKey' => uniqid((string) rand()),
            'lastLogin' => $lastLogin,
            'active' => false,
            'failedLogins' => 1,
            'lockedUntil' => $lockedUntil,
            'extendedEditor' => true,
            'disabledCache' => true,
        ];

        $user = $this->resource->create($testData);

        static::assertInstanceOf(User::class, $user);
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
        static::assertEquals($user->getLastLogin(), new DateTime($testData['lastLogin']));
        static::assertEquals($user->getFailedLogins(), $testData['failedLogins']);
        static::assertEquals($user->getLockedUntil(), new DateTime($testData['lockedUntil']));
        static::assertEquals($user->getExtendedEditor(), $testData['extendedEditor']);
        static::assertEquals($user->getDisabledCache(), $testData['disabledCache']);

        return $user->getId();
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneShouldBeSuccessful(int $id): void
    {
        $this->resource->setRole('read');

        $user = $this->resource->getOne($id);
        static::assertIsArray($user);
        static::assertGreaterThan(0, $user['id']);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneShouldBeAbleToReturnObject(int $id): void
    {
        $this->resource->setRole('read');

        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $user = $this->resource->getOne($id);

        static::assertInstanceOf(User::class, $user);
        static::assertGreaterThan(0, $user->getId());
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetListShouldBeSuccessful(): void
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
    public function testGetListShouldBeAbleToReturnObjects(): void
    {
        $this->resource->setRole('read');

        $this->resource->setResultMode(Resource::HYDRATE_OBJECT);
        $result = $this->resource->getList();

        static::assertArrayHasKey('data', $result);
        static::assertArrayHasKey('total', $result);

        static::assertGreaterThanOrEqual(1, $result['total']);
        static::assertGreaterThanOrEqual(1, $result['data']);

        static::assertInstanceOf(User::class, $result['data'][0]);
    }

    public function testCreateWithInvalidDataShouldThrowValidationException(): void
    {
        $this->expectException(ValidationException::class);
        $this->resource->setRole('create');

        $testData = [
            'email' => 'invalid',
            'username' => 'username' . uniqid((string) rand()),
            'name' => 'Max Mustermann',
            'role' => 'local_admins',
        ];

        $this->resource->create($testData);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testUpdateShouldBeSuccessful(int $id): int
    {
        $this->resource->setRole('update');

        $testData = [
            'username' => 'updated' . uniqid((string) rand()),
            'name' => 'Max Mustermann Update',
        ];

        $user = $this->resource->update($id, $testData);

        static::assertInstanceOf(User::class, $user);
        static::assertEquals($id, $user->getId());

        static::assertEquals($user->getUsername(), $testData['username']);
        static::assertEquals($user->getName(), $testData['name']);

        return $id;
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testUpdateWithInvalidDataShouldThrowValidationException(int $id): void
    {
        $this->expectException(ValidationException::class);
        $this->resource->setRole('update');

        $testData = [
            'email' => 'invalid',
        ];

        $this->resource->update($id, $testData);
    }

    public function testUpdateWithInvalidIdShouldThrowNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->resource->setRole('update');

        $this->resource->update(9999999, []);
    }

    public function testUpdateWithMissingIdShouldThrowParameterMissingException(): void
    {
        $this->expectException(ParameterMissingException::class);
        $this->resource->setRole('update');

        $this->resource->update(0, []);
    }

    /**
     * @depends testUpdateShouldBeSuccessful
     */
    public function testDeleteShouldBeSuccessful(int $id): void
    {
        $this->resource->setRole('delete');

        $user = $this->resource->delete($id);

        static::assertInstanceOf(User::class, $user);
        static::assertSame(0, (int) $user->getId());
    }

    public function testDeleteWithInvalidIdShouldThrowNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->resource->setRole('delete');

        $this->resource->delete(9999999);
    }

    public function testDeleteWithMissingIdShouldThrowParameterMissingException(): void
    {
        $this->expectException(ParameterMissingException::class);

        $this->resource->setRole('delete');

        $this->resource->delete(0);
    }

    public function testCreateWithUserRoleId(): void
    {
        $this->resource->setRole('create');

        $data = [
            'email' => __FUNCTION__ . uniqid((string) rand()) . '@example.com',
            'username' => 'user' . uniqid((string) rand()),
            'name' => 'Max Mustermann',
            'password' => 'fooobar',
            'roleId' => 1,
        ];

        $user = $this->resource->create($data);
        static::assertEquals(1, $user->getRole()->getId());
    }

    public function testCreateWithUserRoleName(): void
    {
        $this->resource->setRole('create');

        $data = [
            'email' => __FUNCTION__ . uniqid((string) rand()) . '@example.com',
            'username' => 'user' . uniqid((string) rand()),
            'name' => 'Max Mustermann',
            'password' => 'fooobar',
            'role' => 'local_admins',
        ];

        $user = $this->resource->create($data);
        static::assertEquals('local_admins', $user->getRole()->getName());
    }

    public function testCreateWithLocaleId(): void
    {
        $this->resource->setRole('create');

        $data = [
            'email' => __FUNCTION__ . uniqid((string) rand()) . '@example.com',
            'username' => 'user' . uniqid((string) rand()),
            'name' => 'Max Mustermann',
            'password' => 'fooobar',
            'role' => 'local_admins',
            'localeId' => 2,
        ];

        $user = $this->resource->create($data);
        static::assertEquals(2, $user->getLocaleId());
    }

    public function testCreateWithLocaleName(): void
    {
        $this->resource->setRole('create');

        $data = [
            'email' => __FUNCTION__ . uniqid((string) rand()) . '@example.com',
            'username' => 'user' . uniqid((string) rand()),
            'name' => 'Max Mustermann',
            'password' => 'fooobar',
            'role' => 'local_admins',
            'locale' => 'en_GB',
        ];

        $user = $this->resource->create($data);
        static::assertEquals(2, $user->getLocaleId());
    }

    public function testGetOneWithMissingIdShouldThrowParameterMissingException(): void
    {
        $this->expectException(ParameterMissingException::class);
        $this->resource->setRole('read');
        $this->resource->getOne(0);
    }

    public function testGetOneWithInvalidIdShouldThrowNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->resource->setRole('read');
        $this->resource->getOne(9999999);
    }

    protected function getAclMockAllowEverything(): Shopware_Components_Acl
    {
        $aclMock = $this->createMock(Shopware_Components_Acl::class);

        $aclMock->method('has')
            ->willReturn(true);

        $aclMock->method('isAllowed')
            ->willReturn(true);

        return $aclMock;
    }
}
