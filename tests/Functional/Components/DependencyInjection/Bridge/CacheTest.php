<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Functional\Components\DependencyInjection\Bridge;

use PHPUnit\Framework\TestCase;
use Shopware\Components\DependencyInjection\Bridge\Cache;
use Shopware\Components\ShopwareReleaseStruct;
use Shopware\Tests\Functional\Traits\DirectoryDeletionTrait;

class CacheTest extends TestCase
{
    use DirectoryDeletionTrait;

    private ShopwareReleaseStruct $release;

    private string $testDir;

    private string $cacheDirectory;

    private string $cacheFile;

    protected function setUp(): void
    {
        $this->release = new ShopwareReleaseStruct('5.5.0', '', '4711');
        $this->testDir = $this->createTestDir();
        $this->cacheDirectory = $this->testDir . '/shopware_test--0';
        $this->cacheFile = $this->cacheDirectory . '/shopware_test---bar';
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->testDir);
    }

    public function testWorldReadWriteExecutable(): void
    {
        $options = [
            'hashed_directory_perm' => 0777 & ~umask(),
            'cache_file_perm' => 0666 & ~umask(),
            'hashed_directory_level' => 1,
            'cache_dir' => $this->testDir,
            'file_name_prefix' => 'shopware_test',
        ];

        $this->createCache($options);

        $this->assertCacheDirectory($options);
    }

    public function testUserReadWriteExecutable(): void
    {
        $options = [
            'hashed_directory_perm' => 0700 & ~umask(),
            'cache_file_perm' => 0600 & ~umask(),
            'hashed_directory_level' => 1,
            'cache_dir' => $this->testDir,
            'file_name_prefix' => 'shopware_test',
        ];

        $this->createCache($options);

        $this->assertCacheDirectory($options);
    }

    public function testMixedReadWriteExecutable(): void
    {
        $options = [
            'hashed_directory_perm' => 0755 & ~umask(),
            'cache_file_perm' => 0600 & ~umask(),
            'hashed_directory_level' => 1,
            'cache_dir' => $this->testDir,
            'file_name_prefix' => 'shopware_test',
        ];

        $this->createCache($options);

        $this->assertCacheDirectory($options);
    }

    public function testWorldReadWriteExecutableAsString(): void
    {
        $options = [
            'hashed_directory_perm' => '0777',
            'cache_file_perm' => '0666',
            'hashed_directory_level' => 1,
            'cache_dir' => $this->testDir,
            'file_name_prefix' => 'shopware_test',
        ];

        $this->createCache($options);

        $dirPermissions = fileperms($this->cacheDirectory) & 0777;
        $filePermissions = fileperms($this->cacheFile) & 0777;

        static::assertFileExists($this->cacheDirectory);
        static::assertFileExists($this->cacheFile);

        static::assertSame(octdec($options['hashed_directory_perm']), $dirPermissions);
        static::assertSame(octdec($options['cache_file_perm']), $filePermissions);
    }

    private function createTestDir(): string
    {
        $testDir = sys_get_temp_dir() . '/umask-test';
        if (!is_dir($testDir)) {
            mkdir($testDir);
        }

        return $testDir;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function createCache(array $options): void
    {
        $cache = (new Cache())->factory('file', [
            'automatic_serialization' => true,
        ], $options, $this->release);
        $cache->save('foo', 'bar');
    }

    /**
     * @param array<string, mixed> $options
     */
    private function assertCacheDirectory(array $options): void
    {
        $dirPermissions = fileperms($this->cacheDirectory) & 0777;
        $filePermissions = fileperms($this->cacheFile) & 0777;

        static::assertFileExists($this->cacheDirectory);
        static::assertFileExists($this->cacheFile);

        static::assertSame($options['hashed_directory_perm'], $dirPermissions);
        static::assertSame($options['cache_file_perm'], $filePermissions);
    }
}
