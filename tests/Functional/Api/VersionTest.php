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

namespace Shopware\Tests\Functional\Api;

use Shopware\Kernel;

/**
 * @covers \Shopware_Controllers_Api_Version
 */
class VersionTest extends AbstractApiTestCase
{
    public function testGetVersionShouldBeSuccessful(): void
    {
        $kernel = new Kernel('testing', true);
        $release = $kernel->getRelease();

        $this->authenticatedApiRequest('GET', '/api/version');

        $result = $this->client->getResponse();

        static::assertEquals('application/json', $result->headers->get('Content-Type'));
        static::assertEquals(null, $result->headers->get('Set-Cookie'));
        static::assertEquals(200, $result->getStatusCode());

        $result = $result->getContent();
        $result = json_decode($result, true);

        static::assertArrayHasKey('success', $result);
        static::assertTrue($result['success']);

        static::assertArrayHasKey('data', $result);
        $data = $result['data'];
        static::assertIsArray($data);

        static::assertEquals($release['version'], $data['version']);
        static::assertEquals($release['revision'], $data['revision']);
    }
}
