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

use DateTime;
use Doctrine\DBAL\Connection;
use Enlight_Components_Session_Namespace;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Password\Encoder\PasswordEncoderInterface;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Shopware_Components_Auth;
use Shopware_Components_Auth_Adapter_Default;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Zend_Auth_Storage_Session;

class AuthTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    private Connection $connection;

    private Shopware_Components_Auth $auth;

    private PasswordEncoderInterface $encoder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->getContainer()->get(Connection::class);

        $this->auth = Shopware_Components_Auth::getInstance();
        $this->auth->setStorage(new Zend_Auth_Storage_Session(new Enlight_Components_Session_Namespace(new MockArraySessionStorage())));

        $passwordEncoderRegistry = $this->getContainer()->get('passwordencoder');
        $defaultEncoderName = $passwordEncoderRegistry->getDefaultPasswordEncoderName();
        $this->encoder = $passwordEncoderRegistry->getEncoderByName($defaultEncoderName);
    }

    public function testAuthenticateWithPassedAdapter(): void
    {
        // Create adapter
        $adapter = new Shopware_Components_Auth_Adapter_Default(new Session(new MockArraySessionStorage()));

        // Prepare backend user
        $username = 'testUser' . uniqid((string) rand());
        $password = 'correctPassword';
        $this->createAdminUser($username, $password);

        // Authenticate with wrong password; should fail
        $adapter->setIdentity($username);
        $adapter->setCredential($password . 'GoneWrong');
        static::assertFalse($this->auth->authenticate($adapter)->isValid());

        // Authenticate with correct password; should succeed
        $adapter->setIdentity($username);
        $adapter->setCredential($password);
        static::assertTrue($this->auth->authenticate($adapter)->isValid());
        $user = $this->auth->getStorage()->read();
        static::assertInstanceOf('stdClass', $user);
        static::assertEquals($username, $user->username);
    }

    public function testAuthenticateWithSetAdapter(): void
    {
        // Create adapter
        $adapter = new Shopware_Components_Auth_Adapter_Default(new Session(new MockArraySessionStorage()));

        // Set adapter, so we don't have to pass it to authenticate method
        $this->auth->setBaseAdapter($adapter);
        $this->auth->addAdapter($adapter);

        // Prepare backend user
        $username = 'testUser' . uniqid((string) rand());
        $password = 'correctPassword';
        $this->createAdminUser($username, $password);

        // Authenticate with wrong password; should fail
        $adapter->setIdentity($username);
        $adapter->setCredential($password . 'GoneWrong');
        static::assertFalse($this->auth->authenticate()->isValid());

        // Authenticate with correct password; should succeed
        $adapter->setIdentity($username);
        $adapter->setCredential($password);
        static::assertTrue($this->auth->authenticate()->isValid());
        $user = $this->auth->getStorage()->read();
        static::assertInstanceOf('stdClass', $user);
        static::assertEquals($username, $user->username);
    }

    protected function createAdminUser(string $username, string $password): void
    {
        $name = uniqid((string) rand());
        $email = $name . '@shopware.com';
        $password = $this->encoder->encodePassword($password);

        $this->connection->insert('s_core_auth', [
            'roleID' => 1,
            'username' => $username,
            'password' => $password,
            'encoder' => $this->encoder->getName(),
            'localeID' => 1,
            'name' => $name,
            'email' => $email,
            'active' => 1,
            'failedlogins' => 0,
            'lockeduntil' => (new DateTime('1970-01-01'))->format('Y-m-d H:i:s'),
        ]);
    }
}
