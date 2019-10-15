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

namespace Shopware\Tests\Functional\Components\DependencyInjection\Bridge;

use PHPUnit\Framework\TestCase;
use Shopware\Components\DependencyInjection\Bridge\Cache;
use Shopware\Components\ShopwareReleaseStruct;
use Shopware\Tests\Functional\Traits\DirectoryDeletionTrait;

class CacheTest extends TestCase
{
    use DirectoryDeletionTrait;

    private $release;

    public function setUp(): void
    {
        $this->release = new ShopwareReleaseStruct('5.5.0', '', '4711');
    }

    public function testWorldReadWriteExecutable()
    {
        $testDir = sys_get_temp_dir() . '/umask-test';
        $options = [
            'hashed_directory_perm' => 0777 & ~umask(),
            'cache_file_perm' => 0666 & ~umask(),
            'hashed_directory_level' => 1,
            'cache_dir' => $testDir,
            'file_name_prefix' => 'shopware_test',
        ];

        mkdir($testDir);

        $cache = (new Cache())->factory('file', [], $options, $this->release);
        $cache->save('foo', 'bar');

        $cacheDirectory = $testDir . '/shopware_test--0';
        $cacheFile = $cacheDirectory . '/shopware_test---bar';

        $dirPermissions = (fileperms($cacheDirectory) & 0777);
        $filePermissions = (fileperms($cacheFile) & 0777);

        static::assertFileExists($cacheDirectory);
        static::assertFileExists($cacheFile);

        static::assertEquals($options['hashed_directory_perm'], $dirPermissions);
        static::assertEquals($options['cache_file_perm'], $filePermissions);

        $this->deleteDirectory($testDir);
    }

    public function testUserReadWriteExecutable()
    {
        $testDir = sys_get_temp_dir() . '/umask-test';
        $options = [
            'hashed_directory_perm' => 0700 & ~umask(),
            'cache_file_perm' => 0600 & ~umask(),
            'hashed_directory_level' => 1,
            'cache_dir' => $testDir,
            'file_name_prefix' => 'shopware_test',
        ];

        mkdir($testDir);

        $cache = (new Cache())->factory('file', [], $options, $this->release);
        $cache->save('foo', 'bar');

        $cacheDirectory = $testDir . '/shopware_test--0';
        $cacheFile = $cacheDirectory . '/shopware_test---bar';

        $dirPermissions = (fileperms($cacheDirectory) & 0777);
        $filePermissions = (fileperms($cacheFile) & 0777);

        static::assertFileExists($cacheDirectory);
        static::assertFileExists($cacheFile);

        static::assertEquals($options['hashed_directory_perm'], $dirPermissions);
        static::assertEquals($options['cache_file_perm'], $filePermissions);

        $this->deleteDirectory($testDir);
    }

    public function testMixedReadWriteExecutable()
    {
        $testDir = sys_get_temp_dir() . '/umask-test';
        $options = [
            'hashed_directory_perm' => 0755 & ~umask(),
            'cache_file_perm' => 0600 & ~umask(),
            'hashed_directory_level' => 1,
            'cache_dir' => $testDir,
            'file_name_prefix' => 'shopware_test',
        ];

        mkdir($testDir);

        $cache = (new Cache())->factory('file', [], $options, $this->release);
        $cache->save('foo', 'bar');

        $cacheDirectory = $testDir . '/shopware_test--0';
        $cacheFile = $cacheDirectory . '/shopware_test---bar';

        $dirPermissions = (fileperms($cacheDirectory) & 0777);
        $filePermissions = (fileperms($cacheFile) & 0777);

        static::assertFileExists($cacheDirectory);
        static::assertFileExists($cacheFile);

        static::assertEquals($options['hashed_directory_perm'], $dirPermissions);
        static::assertEquals($options['cache_file_perm'], $filePermissions);

        $this->deleteDirectory($testDir);
    }

    public function testWorldReadWriteExecutableAsString()
    {
        $testDir = sys_get_temp_dir() . '/umask-test';
        $options = [
            'hashed_directory_perm' => '0777',
            'cache_file_perm' => '0666',
            'hashed_directory_level' => 1,
            'cache_dir' => $testDir,
            'file_name_prefix' => 'shopware_test',
        ];

        mkdir($testDir);

        $cache = (new Cache())->factory('file', [], $options, $this->release);
        $cache->save('foo', 'bar');

        $cacheDirectory = $testDir . '/shopware_test--0';
        $cacheFile = $cacheDirectory . '/shopware_test---bar';

        $dirPermissions = (fileperms($cacheDirectory) & 0777);
        $filePermissions = (fileperms($cacheFile) & 0777);

        static::assertFileExists($cacheDirectory);
        static::assertFileExists($cacheFile);

        static::assertEquals(octdec($options['hashed_directory_perm']), $dirPermissions);
        static::assertEquals(octdec($options['cache_file_perm']), $filePermissions);

        $this->deleteDirectory($testDir);
    }
}
