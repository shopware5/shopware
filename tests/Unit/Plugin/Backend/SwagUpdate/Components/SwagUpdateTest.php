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

namespace Shopware\Tests\Unit\Plugin\Backend\Auth;

use PHPUnit\Framework\TestCase;
use ShopwarePlugins\SwagUpdate\Components\Exception\ApiLimitExceededException;
use ShopwarePlugins\SwagUpdate\Components\Struct\Version;
use ShopwarePlugins\SwagUpdate\Components\UpdateCheck;
use Zend_Http_Client;
use Zend_Http_Response;

class SwagUpdateTest extends TestCase
{
    public function testCheckUpdateThrowsExceptionOnApiLimit(): void
    {
        $response = new Zend_Http_Response(403, []);

        $clientMock = $this->getMockBuilder(Zend_Http_Client::class)->disableOriginalConstructor()->getMock();
        $clientMock->method('request')->willReturn($response);

        $updateChecker = new UpdateCheck(
            $clientMock,
            false,
            false
        );

        $this->expectException(ApiLimitExceededException::class);
        $updateChecker->checkUpdate('v5.7.13');
    }

    public function testCheckUpdateReturnsNoVersionOnRouteNotFound(): void
    {
        $response = new Zend_Http_Response(404, [], 'foo');

        $clientMock = $this->getMockBuilder(Zend_Http_Client::class)->disableOriginalConstructor()->getMock();
        $clientMock->method('request')->willReturn($response);

        $updateChecker = new UpdateCheck(
            $clientMock,
            false,
            false
        );

        static::assertNull($updateChecker->checkUpdate('v5.7.13'));
    }

    public function testCheckUpdateReturnsNoVersionOnInvalidRequestBody(): void
    {
        $response = new Zend_Http_Response(200, [], '');

        $clientMock = $this->getMockBuilder(Zend_Http_Client::class)->disableOriginalConstructor()->getMock();
        $clientMock->method('request')->willReturn($response);

        $updateChecker = new UpdateCheck(
            $clientMock,
            false,
            false
        );

        static::assertNull($updateChecker->checkUpdate('v5.7.13'));
    }

    public function testCheckUpdateReturnsVersionNullWithoutOldRelease(): void
    {
        $response = new Zend_Http_Response(200, [], $this->getSingleVersionJson('v5.7.12'));

        $clientMock = $this->getMockBuilder(Zend_Http_Client::class)->disableOriginalConstructor()->getMock();
        $clientMock->method('request')->willReturn($response);

        $updateChecker = new UpdateCheck(
            $clientMock,
            false,
            false
        );

        static::assertNull($updateChecker->checkUpdate('v5.7.13'));
    }

    public function testCheckUpdateReturnsVersionNullWithoutAssets(): void
    {
        $response = new Zend_Http_Response(200, [], $this->getSingleVersionWithoutAssets('v5.8.12'));

        $clientMock = $this->getMockBuilder(Zend_Http_Client::class)->disableOriginalConstructor()->getMock();
        $clientMock->method('request')->willReturn($response);

        $updateChecker = new UpdateCheck(
            $clientMock,
            false,
            false
        );

        static::assertNull($updateChecker->checkUpdate('v5.7.13'));
    }

    public function testCheckUpdateReturnsVersionWithNewRelease(): void
    {
        $response = new Zend_Http_Response(200, [], $this->getSingleVersionJson('v5.8.12'));

        $clientMock = $this->getMockBuilder(Zend_Http_Client::class)->disableOriginalConstructor()->getMock();
        $clientMock->method('request')->willReturn($response);

        $updateChecker = new UpdateCheck(
            $clientMock,
            false,
            false
        );

        $version = $updateChecker->checkUpdate('v5.7.13');
        static::assertInstanceOf(Version::class, $version);
        static::assertSame('v5.8.12', $version->version);
    }

    public function testCheckUpdateReturnsVersionWithNewPreRelease(): void
    {
        $response = new Zend_Http_Response(200, [], $this->getSingleVersionJson('v5.8.12', true));

        $clientMock = $this->getMockBuilder(Zend_Http_Client::class)->disableOriginalConstructor()->getMock();
        $clientMock->method('request')->willReturn($response);

        $updateChecker = new UpdateCheck(
            $clientMock,
            false,
            true
        );

        $version = $updateChecker->checkUpdate('v5.7.13');
        static::assertInstanceOf(Version::class, $version);
        static::assertSame('v5.8.12', $version->version);
    }

    public function testCheckUpdateReturnsVersionOfDraftWithDraftRelease(): void
    {
        $response = new Zend_Http_Response(200, [], $this->getMultiVersionJson('v5.8.13', true, 'v5.8.12', false));

        $clientMock = $this->getMockBuilder(Zend_Http_Client::class)->disableOriginalConstructor()->getMock();
        $clientMock->method('request')->willReturn($response);

        $updateChecker = new UpdateCheck(
            $clientMock,
            true,
            false
        );

        $version = $updateChecker->checkUpdate('v5.7.13');
        static::assertInstanceOf(Version::class, $version);
        static::assertSame('v5.8.13', $version->version);
    }

    public function testCheckUpdateReturnsVersionOfWithoutDraftRelease(): void
    {
        $response = new Zend_Http_Response(200, [], $this->getMultiVersionJson('v5.8.13', true, 'v5.8.12', false));

        $clientMock = $this->getMockBuilder(Zend_Http_Client::class)->disableOriginalConstructor()->getMock();
        $clientMock->method('request')->willReturn($response);

        $updateChecker = new UpdateCheck(
            $clientMock,
            false,
            false
        );

        $version = $updateChecker->checkUpdate('v5.7.13');
        static::assertInstanceOf(Version::class, $version);
        static::assertSame('v5.8.12', $version->version);
    }

    private function getSingleVersionJson(string $version, bool $prerelease = false): string
    {
        return json_encode([
                [
                    'html_url' => 'https://github.com/shopware/shopware/releases/tag/v5.7.17',
                    'body' => '',
                    'id' => 97288305,
                    'node_id' => 'RE_kwDOAFa3Gs4FzIBx',
                    'tag_name' => $version,
                    'name' => 'Release 5.7.17',
                    'draft' => false,
                    'prerelease' => $prerelease,
                    'created_at' => '2023-03-21T12:58:25Z',
                    'published_at' => '2023-03-29T09:06:24Z',
                    'assets' => [
                                [
                                    'url' => 'https://api.github.com/repos/shopware/shopware/releases/assets/102259279',
                                    'id' => 102259279,
                                    'node_id' => 'RA_kwDOAFa3Gs4GGFpP',
                                    'name' => 'install_31b8b37fe396c41da6dfe6c5354e968ca82d890c.zip',
                                    'label' => null,
                                    'content_type' => 'application/zip',
                                    'state' => 'uploaded',
                                    'size' => 45264378,
                                    'download_count' => 132,
                                    'created_at' => '2023-04-04T12:14:50Z',
                                    'updated_at' => '2023-05-30T07:14:09Z',
                                    'browser_download_url' => 'https://github.com/shopware/shopware/releases/download/v5.7.17/install_31b8b37fe396c41da6dfe6c5354e968ca82d890c.zip',
                                ],
                                [
                                    'url' => 'https://api.github.com/repos/shopware/shopware/releases/assets/102259331',
                                    'id' => 102259331,
                                    'node_id' => 'RA_kwDOAFa3Gs4GGFqD',
                                    'name' => 'update_e0a7813fcbae9ecf1dd566899b02e820a0c0b3e5.zip',
                                    'label' => null,
                                    'content_type' => 'application/zip',
                                    'state' => 'uploaded',
                                    'size' => 46082425,
                                    'download_count' => 12,
                                    'created_at' => '2023-04-04T12:15:18Z',
                                    'updated_at' => '2023-05-30T07:14:37Z',
                                    'browser_download_url' => 'https://github.com/shopware/shopware/releases/download/v5.7.17/update_e0a7813fcbae9ecf1dd566899b02e820a0c0b3e5.zip',
                                ],
                        ],
                ],
        ], JSON_THROW_ON_ERROR);
    }

    private function getSingleVersionWithoutAssets(string $version): string
    {
        return json_encode([
            [
                'html_url' => 'https://github.com/shopware/shopware/releases/tag/v5.7.17',
                'body' => '',
                'id' => 97288305,
                'node_id' => 'RE_kwDOAFa3Gs4FzIBx',
                'tag_name' => $version,
                'name' => 'Release 5.7.17',
                'draft' => false,
                'prerelease' => false,
                'created_at' => '2023-03-21T12:58:25Z',
                'published_at' => '2023-03-29T09:06:24Z',
                'assets' => [
                    ],
                'tarball_url' => 'https://api.github.com/repos/shopware/shopware/tarball/v5.7.17',
                'zipball_url' => 'https://api.github.com/repos/shopware/shopware/zipball/v5.7.17',
            ],
        ], JSON_THROW_ON_ERROR);
    }

    private function getMultiVersionJson(string $versionOne, bool $draftOne, string $versionTwo, bool $draftTwo): string
    {
        return json_encode([
            [
                'html_url' => 'https://github.com/shopware/shopware/releases/tag/v5.7.17',
                'body' => '',
                'id' => 97288305,
                'node_id' => 'RE_kwDOAFa3Gs4FzIBx',
                'tag_name' => $versionOne,
                'name' => 'Release 5.7.17',
                'draft' => $draftOne,
                'prerelease' => false,
                'created_at' => '2023-03-21T12:58:25Z',
                'published_at' => '2023-03-29T09:06:24Z',
                'assets' => [
                        [
                            'url' => 'https://api.github.com/repos/shopware/shopware/releases/assets/102259279',
                            'id' => 102259279,
                            'node_id' => 'RA_kwDOAFa3Gs4GGFpP',
                            'name' => 'install_31b8b37fe396c41da6dfe6c5354e968ca82d890c.zip',
                            'label' => null,
                            'content_type' => 'application/zip',
                            'state' => 'uploaded',
                            'size' => 45264378,
                            'download_count' => 132,
                            'created_at' => '2023-04-04T12:14:50Z',
                            'updated_at' => '2023-05-30T07:14:09Z',
                            'browser_download_url' => 'https://github.com/shopware/shopware/releases/download/v5.7.17/install_31b8b37fe396c41da6dfe6c5354e968ca82d890c.zip',
                        ],
                        [
                            'url' => 'https://api.github.com/repos/shopware/shopware/releases/assets/102259331',
                            'id' => 102259331,
                            'node_id' => 'RA_kwDOAFa3Gs4GGFqD',
                            'name' => 'update_e0a7813fcbae9ecf1dd566899b02e820a0c0b3e5.zip',
                            'label' => null,
                            'content_type' => 'application/zip',
                            'state' => 'uploaded',
                            'size' => 46082425,
                            'download_count' => 12,
                            'created_at' => '2023-04-04T12:15:18Z',
                            'updated_at' => '2023-05-30T07:14:37Z',
                            'browser_download_url' => 'https://github.com/shopware/shopware/releases/download/v5.7.17/update_e0a7813fcbae9ecf1dd566899b02e820a0c0b3e5.zip',
                        ],
                    ],
            ],
            [
                'html_url' => 'https://github.com/shopware/shopware/releases/tag/v5.7.17',
                'body' => '',
                'id' => 97288305,
                'node_id' => 'RE_kwDOAFa3Gs4FzIBx',
                'tag_name' => $versionTwo,
                'name' => 'Release 5.7.17',
                'draft' => $draftTwo,
                'prerelease' => false,
                'created_at' => '2023-03-21T12:58:25Z',
                'published_at' => '2023-03-29T09:06:24Z',
                'assets' => [
                        [
                            'url' => 'https://api.github.com/repos/shopware/shopware/releases/assets/102259279',
                            'id' => 102259279,
                            'node_id' => 'RA_kwDOAFa3Gs4GGFpP',
                            'name' => 'install_31b8b37fe396c41da6dfe6c5354e968ca82d890c.zip',
                            'label' => null,
                            'content_type' => 'application/zip',
                            'state' => 'uploaded',
                            'size' => 45264378,
                            'download_count' => 132,
                            'created_at' => '2023-04-04T12:14:50Z',
                            'updated_at' => '2023-05-30T07:14:09Z',
                            'browser_download_url' => 'https://github.com/shopware/shopware/releases/download/v5.7.17/install_31b8b37fe396c41da6dfe6c5354e968ca82d890c.zip',
                        ],
                        [
                            'url' => 'https://api.github.com/repos/shopware/shopware/releases/assets/102259331',
                            'id' => 102259331,
                            'node_id' => 'RA_kwDOAFa3Gs4GGFqD',
                            'name' => 'update_e0a7813fcbae9ecf1dd566899b02e820a0c0b3e5.zip',
                            'label' => null,
                            'content_type' => 'application/zip',
                            'state' => 'uploaded',
                            'size' => 46082425,
                            'download_count' => 12,
                            'created_at' => '2023-04-04T12:15:18Z',
                            'updated_at' => '2023-05-30T07:14:37Z',
                            'browser_download_url' => 'https://github.com/shopware/shopware/releases/download/v5.7.17/update_e0a7813fcbae9ecf1dd566899b02e820a0c0b3e5.zip',
                        ],
                    ],
            ],
        ], JSON_THROW_ON_ERROR);
    }
}
