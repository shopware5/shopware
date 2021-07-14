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

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

require_once __DIR__ . '/../../../../../engine/Shopware/Controllers/Backend/UserManager.php';

class DefaultTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    public function testLogInIntoBackend(): void
    {
        $sql = "
            INSERT INTO s_core_auth (roleID, username, password, encoder, apiKey, localeID, sessionID, lastlogin, name, email, active, failedlogins, lockeduntil, extended_editor, disabled_cache)
            VALUES (1, 'unitTest', '$2y$10$0ltx3bbXf06IRw4kvwXn/.A4ZkRhVNLKC9vWuM3UOOC2YXZx48KXS', 'bcrypt', NULL, 1, NULL, NULL, 'FooBar', 'foo@bar.test', 1, 0, NULL, 0, 0);
        ";

        $connection = $this->getContainer()->get('dbal_connection');
        static::assertInstanceOf(Connection::class, $connection);

        $insertResult = (bool) $connection->exec($sql);
        static::assertTrue($insertResult);

        $session = $this->getContainer()->get('session');
        static::assertInstanceOf(\Enlight_Components_Session_Namespace::class, $session);

        $authDefault = new \Shopware_Components_Auth_Adapter_Default($session);
        $authDefault->setIdentity('unitTest');
        $authDefault->setCredential('testtest');

        $zendAuthResult = $authDefault->authenticate();
        $result = $zendAuthResult->getMessages()[0];

        static::assertSame('Authentication successful.', $result);
    }
}
