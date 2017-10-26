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

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class UserTest extends TestCase
{
    /**
     * @var User
     */
    protected $resource;

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
        $user = $this->resource->getOne($id);
        $this->assertGreaterThan(0, $user['id']);
    }

    /**
     * @depends testCreateShouldBeSuccessful
     */
    public function testGetOneShouldBeAbleToReturnObject($id)
    {
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
        $this->resource->update(9999999, []);
    }

    /**
     * @expectedException \Shopware\Components\Api\Exception\ParameterMissingException
     */
    public function testUpdateWithMissingIdShouldThrowParameterMissingException()
    {
        $this->resource->update('', []);
    }

    /**
     * @depends testUpdateShouldBeSuccessful
     */
    public function testDeleteShouldBeSuccessful($id)
    {
        $user = $this->resource->delete($id);

        $this->assertInstanceOf('\Shopware\Models\User\User', $user);
        $this->assertEquals(null, $user->getId());
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

    public function testCreateWithUserRoleId()
    {
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
}
