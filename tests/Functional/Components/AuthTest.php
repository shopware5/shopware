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

use Shopware\Components\Password\Encoder\PasswordEncoderInterface;

class Shopware_Tests_Components_AuthTest extends Enlight_Components_Test_TestCase
{
    /**
     * @var Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    private $db;

    /**
     * @var Shopware_Components_Auth
     */
    private $auth;

    /**
     * @var PasswordEncoderInterface
     */
    private $encoder;

    protected function setUp()
    {
        parent::setUp();

        $this->db = Shopware()->Db();
        $this->db->beginTransaction();

        $this->auth = Shopware_Components_Auth::getInstance();

        /** @var \Shopware\Components\Password\Manager $passworEncoderRegistry */
        $passworEncoderRegistry = Shopware()->Container()->get('PasswordEncoder');
        $defaultEncoderName = $passworEncoderRegistry->getDefaultPasswordEncoderName();
        $this->encoder = $passworEncoderRegistry->getEncoderByName($defaultEncoderName);
    }

    protected function tearDown()
    {
        $this->db->rollBack();

        parent::tearDown();
    }

    public function testAuthenticateWithPassedAdapter()
    {
        // Create adapter
        $adapter = new Shopware_Components_Auth_Adapter_Default();

        // Prepare backend user
        $username = 'testUser' . uniqid(rand());
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

    public function testAuthenticateWithSetAdapter()
    {
        // Create adapter
        $adapter = new Shopware_Components_Auth_Adapter_Default();

        // Set adapter, so we don't have to pass it to authenticate method
        $this->auth->setBaseAdapter($adapter);
        $this->auth->addAdapter($adapter);

        // Prepare backend user
        $username = 'testUser' . uniqid(rand());
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

    protected function createAdminUser($username, $password)
    {
        $name = uniqid(rand());
        $email = $name . '@shopware.com';
        $password = $this->encoder->encodePassword($password);

        $this->db->insert('s_core_auth', [
            'roleID' => 1,
            'username' => $username,
            'password' => $password,
            'encoder' => $this->encoder->getName(),
            'localeID' => 1,
            'name' => $name,
            'email' => $email,
            'active' => true,
            'failedlogins' => 0,
            'lockedUntil' => (new DateTime('1970-01-01'))->format('Y-m-d H:i:s'),
        ]);
    }
}
