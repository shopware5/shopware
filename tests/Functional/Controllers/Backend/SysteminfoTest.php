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

class SysteminfoTest extends \Enlight_Components_Test_Controller_TestCase
{
    /**
     * Standard set up for every test - just disable auth
     */
    public function setUp(): void
    {
        parent::setUp();

        // Disable auth and acl
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Backend()->Auth()->setNoAcl();
    }

    public function testGetConfigList()
    {
        $response = $this->dispatch('backend/systeminfo/getConfigList');

        static::assertTrue($this->View()->success);

        $body = $response->getBody();
        $jsonBody = \json_decode($body, true);

        static::assertArrayHasKey('data', $jsonBody);
        static::assertArrayHasKey('success', $jsonBody);
        static::assertArrayHasKey('name', $jsonBody['data'][0]);
        static::assertArrayHasKey('group', $jsonBody['data'][0]);
        static::assertArrayHasKey('required', $jsonBody['data'][0]);
        static::assertArrayHasKey('version', $jsonBody['data'][0]);
        static::assertArrayHasKey('status', $jsonBody['data'][0]);
    }

    public function testGetPathList()
    {
        $response = $this->dispatch('backend/systeminfo/getPathList');

        static::assertTrue($this->View()->success);

        $body = $response->getBody();
        $jsonBody = \json_decode($body, true);

        static::assertArrayHasKey('data', $jsonBody);
        static::assertArrayHasKey('success', $jsonBody);
        static::assertArrayHasKey('name', $jsonBody['data'][0]);
        static::assertArrayHasKey('version', $jsonBody['data'][0]);
        static::assertArrayHasKey('result', $jsonBody['data'][0]);
    }

    public function testGetFileList()
    {
        $response = $this->dispatch('backend/systeminfo/getFileList');

        static::assertTrue($this->View()->success);

        $body = $response->getBody();
        $jsonBody = \json_decode($body, true);

        static::assertArrayHasKey('data', $jsonBody);
        static::assertArrayHasKey('success', $jsonBody);
    }

    public function testGetVersionList()
    {
        $response = $this->dispatch('backend/systeminfo/getVersionList');

        static::assertTrue($this->View()->success);

        $body = $response->getBody();
        $jsonBody = \json_decode($body, true);

        static::assertArrayHasKey('data', $jsonBody);
        static::assertArrayHasKey('success', $jsonBody);
        static::assertArrayHasKey('name', $jsonBody['data'][0]);
        static::assertArrayHasKey('version', $jsonBody['data'][0]);
    }
}
