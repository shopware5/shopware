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

namespace Shopware\Tests\Unit\Components\Filesystem;

use League\Flysystem\AdapterInterface;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\PluginInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Shopware\Components\Filesystem\PrefixFilesystem;

class PrefixFilesystemTest extends TestCase
{
    /**
     * Call protected/private method of a class.
     *
     * @param object $object     instantiated object that we will run method on
     * @param string $methodName Method name to call
     * @param array  $parameters array of parameters to pass into method
     *
     * @return mixed method return
     */
    public function invokeMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    public function testEmptyPrefix()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The prefix must not be empty.');

        $filesystem = $this->prophesize(FilesystemInterface::class)->reveal();
        $prefix = '';

        new PrefixFilesystem($filesystem, $prefix);
    }

    /**
     * @return array
     */
    public function getPrefixNormalizationData()
    {
        return [
            ['simplePrefix', 'simplePrefix/'],
            ['simplePrefix/', 'simplePrefix/'],
            ['/simplePrefix/', 'simplePrefix/'],
            ['//simplePrefix//', 'simplePrefix/'],
            ['subfolder/my_prefix', 'subfolder/my_prefix/'],
            ['subfolder/my_prefix/', 'subfolder/my_prefix/'],
            ['/subfolder/my_prefix', 'subfolder/my_prefix/'],
            ['/subfolder/my_prefix/', 'subfolder/my_prefix/'],
            ['//subfolder/my_prefix//', 'subfolder/my_prefix/'],
        ];
    }

    /**
     * @return array
     */
    public function getStripPrefixData()
    {
        return [
            ['simplePrefix', 'simplePrefix/swag/file.txt', 'swag/file.txt'],
            ['simplePrefix/', 'simplePrefix/swag/', 'swag/'],
            ['/simplePrefix/', 'simplePrefix/simplePrefix/foo.txt', 'simplePrefix/foo.txt'],
            ['//simplePrefix//', 'prefix/swag.txt', 'prefix/swag.txt'],
        ];
    }

    /**
     * @dataProvider getPrefixNormalizationData
     *
     * @param string $prefix
     * @param string $expectedPrefix
     */
    public function testPrefixNormalization($prefix, $expectedPrefix)
    {
        $filesystem = $this->prophesize(FilesystemInterface::class)->reveal();
        $prefixFilesystem = new PrefixFilesystem($filesystem, $prefix);

        static::assertSame(
            $expectedPrefix,
            $this->invokeMethod($prefixFilesystem, 'normalizePrefix', [$prefix])
        );
    }

    /**
     * @dataProvider getStripPrefixData
     *
     * @param string $prefix
     * @param string $path
     * @param string $expectedPath
     */
    public function testStripPrefix($prefix, $path, $expectedPath)
    {
        $filesystem = $this->prophesize(FilesystemInterface::class)->reveal();
        $prefixFilesystem = new PrefixFilesystem($filesystem, $prefix);

        static::assertSame(
            $expectedPath,
            $this->invokeMethod($prefixFilesystem, 'stripPrefix', [$path])
        );
    }

    public function testHasPrefixed()
    {
        $prefix = 'plugins/swag_simple_test/';
        $path = 'test/file.txt';

        $filesystem = $this->prophesize(FilesystemInterface::class);
        $filesystem->has(Argument::is($prefix . $path))->shouldBeCalled();
        $filesystem = $filesystem->reveal();

        $prefixFilesystem = new PrefixFilesystem($filesystem, $prefix);
        $prefixFilesystem->has($path);
    }

    public function testReadPrefixed()
    {
        $prefix = 'plugins/swag_simple_test/';
        $path = 'test/file.txt';

        $filesystem = $this->prophesize(FilesystemInterface::class);
        $filesystem->read(Argument::is($prefix . $path))->shouldBeCalled();
        $filesystem = $filesystem->reveal();

        $prefixFilesystem = new PrefixFilesystem($filesystem, $prefix);
        $prefixFilesystem->read($path);
    }

    public function testReadStreamPrefixed()
    {
        $prefix = 'plugins/swag_simple_test/';
        $path = 'test/file.txt';

        $filesystem = $this->prophesize(FilesystemInterface::class);
        $filesystem->readStream(Argument::is($prefix . $path))->shouldBeCalled();
        $filesystem = $filesystem->reveal();

        $prefixFilesystem = new PrefixFilesystem($filesystem, $prefix);
        $prefixFilesystem->readStream($path);
    }

    public function testListContentsPrefixed()
    {
        $prefix = 'plugins/swag_simple_test/';

        $returnListContent = [
            [
                'dirname' => '',
                'basename' => 'testDir',
                'filename' => 'testDir',
                'path' => 'plugins/swag_simple_test/testDir',
                'type' => 'dir',
            ],
            [
                'path' => 'plugins/swag_simple_test/my/file.txt',
                'timestamp' => 1488375339,
                'dirname' => 'plugins/swag_simple_test/my',
                'mimetype' => 'application/octet-stream',
                'size' => 14,
                'type' => 'file',
                'basename' => 'file.txt',
                'extension' => 'txt',
                'filename' => 'file',
            ],
        ];

        $expectedListContent = [
            [
                'dirname' => '',
                'basename' => 'testDir',
                'filename' => 'testDir',
                'path' => 'testDir',
                'type' => 'dir',
            ],
            [
                'path' => 'my/file.txt',
                'timestamp' => 1488375339,
                'dirname' => 'my',
                'mimetype' => 'application/octet-stream',
                'size' => 14,
                'type' => 'file',
                'basename' => 'file.txt',
                'extension' => 'txt',
                'filename' => 'file',
            ],
        ];

        $filesystem = $this->prophesize(FilesystemInterface::class);
        $filesystem->listContents(Argument::is($prefix), Argument::is(false))
            ->willReturn($returnListContent)
            ->shouldBeCalled();
        $filesystem = $filesystem->reveal();

        $prefixFilesystem = new PrefixFilesystem($filesystem, $prefix);
        $content = $prefixFilesystem->listContents('');

        static::assertSame($expectedListContent, $content);
    }

    public function testGetMetadata()
    {
        $prefix = 'plugins/swag_simple_test/';
        $path = 'myDir/file.txt';

        $returnMetadata = [
            'path' => 'plugins/swag_simple_test/myDir/file.txt',
            'timestamp' => 1488375339,
            'dirname' => 'plugins/swag_simple_test/myDir',
            'mimetype' => 'application/octet-stream',
            'size' => 14,
            'type' => 'file',
        ];

        $expectedMetadata = [
            'path' => 'myDir/file.txt',
            'timestamp' => 1488375339,
            'dirname' => 'myDir',
            'mimetype' => 'application/octet-stream',
            'size' => 14,
            'type' => 'file',
        ];

        $filesystem = $this->prophesize(FilesystemInterface::class);
        $filesystem->getMetadata(Argument::is($prefix . $path))
            ->willReturn($returnMetadata)
            ->shouldBeCalled();
        $filesystem = $filesystem->reveal();

        $prefixFilesystem = new PrefixFilesystem($filesystem, $prefix);
        $metadata = $prefixFilesystem->getMetadata($path);

        static::assertSame($expectedMetadata, $metadata);
    }

    public function testGetSize()
    {
        $prefix = 'plugins/swag_simple_test/';
        $path = 'test/file.txt';

        $filesystem = $this->prophesize(FilesystemInterface::class);
        $filesystem->getSize(Argument::is($prefix . $path))->shouldBeCalled();
        $filesystem = $filesystem->reveal();

        $prefixFilesystem = new PrefixFilesystem($filesystem, $prefix);
        $prefixFilesystem->getSize($path);
    }

    public function testGetMimetype()
    {
        $prefix = 'plugins/swag_simple_test/';
        $path = 'test/file.txt';

        $filesystem = $this->prophesize(FilesystemInterface::class);
        $filesystem->getMimetype(Argument::is($prefix . $path))->shouldBeCalled();
        $filesystem = $filesystem->reveal();

        $prefixFilesystem = new PrefixFilesystem($filesystem, $prefix);
        $prefixFilesystem->getMimetype($path);
    }

    public function testGetTimestamp()
    {
        $prefix = 'plugins/swag_simple_test/';
        $path = 'test/file.txt';

        $filesystem = $this->prophesize(FilesystemInterface::class);
        $filesystem->getTimestamp(Argument::is($prefix . $path))->shouldBeCalled();
        $filesystem = $filesystem->reveal();

        $prefixFilesystem = new PrefixFilesystem($filesystem, $prefix);
        $prefixFilesystem->getTimestamp($path);
    }

    public function testGetVisibility()
    {
        $prefix = 'plugins/swag_simple_test/';
        $path = 'test/file.txt';

        $filesystem = $this->prophesize(FilesystemInterface::class);
        $filesystem->getVisibility(Argument::is($prefix . $path))->shouldBeCalled();
        $filesystem = $filesystem->reveal();

        $prefixFilesystem = new PrefixFilesystem($filesystem, $prefix);
        $prefixFilesystem->getVisibility($path);
    }

    public function testWrite()
    {
        $prefix = 'plugins/swag_simple_test/';
        $path = 'test/file.txt';

        $filesystem = $this->prophesize(FilesystemInterface::class);
        $filesystem->write(Argument::is($prefix . $path), Argument::is('foobar'), Argument::is([]))->shouldBeCalled();
        $filesystem = $filesystem->reveal();

        $prefixFilesystem = new PrefixFilesystem($filesystem, $prefix);
        $prefixFilesystem->write($path, 'foobar');
    }

    public function testWriteStream()
    {
        $prefix = 'plugins/swag_simple_test/';
        $path = 'test/file.txt';

        $filesystem = $this->prophesize(FilesystemInterface::class);
        $filesystem->writeStream(Argument::is($prefix . $path), Argument::is('foobar'), Argument::is([]))->shouldBeCalled();
        $filesystem = $filesystem->reveal();

        $prefixFilesystem = new PrefixFilesystem($filesystem, $prefix);
        $prefixFilesystem->writeStream($path, 'foobar');
    }

    public function testUpdate()
    {
        $prefix = 'plugins/swag_simple_test/';
        $path = 'test/file.txt';

        $filesystem = $this->prophesize(FilesystemInterface::class);
        $filesystem->update(Argument::is($prefix . $path), Argument::is('foobar'), Argument::is([]))->shouldBeCalled();
        $filesystem = $filesystem->reveal();

        $prefixFilesystem = new PrefixFilesystem($filesystem, $prefix);
        $prefixFilesystem->update($path, 'foobar');
    }

    public function testUpdateStream()
    {
        $prefix = 'plugins/swag_simple_test/';
        $path = 'test/file.txt';

        $filesystem = $this->prophesize(FilesystemInterface::class);
        $filesystem->updateStream(Argument::is($prefix . $path), Argument::is('foobar'), Argument::is([]))->shouldBeCalled();
        $filesystem = $filesystem->reveal();

        $prefixFilesystem = new PrefixFilesystem($filesystem, $prefix);
        $prefixFilesystem->updateStream($path, 'foobar');
    }

    public function testRename()
    {
        $prefix = 'plugins/swag_simple_test/';
        $path = 'test/file.txt';
        $newpath = 'test/renamed_file.txt';

        $filesystem = $this->prophesize(FilesystemInterface::class);
        $filesystem->rename(Argument::is($prefix . $path), Argument::is($prefix . $newpath))->shouldBeCalled();
        $filesystem = $filesystem->reveal();

        $prefixFilesystem = new PrefixFilesystem($filesystem, $prefix);
        $prefixFilesystem->rename($path, $newpath);
    }

    public function testCopy()
    {
        $prefix = 'plugins/swag_simple_test/';
        $path = 'test/file.txt';
        $newpath = 'test/renamed_file.txt';

        $filesystem = $this->prophesize(FilesystemInterface::class);
        $filesystem->copy(Argument::is($prefix . $path), Argument::is($prefix . $newpath))->shouldBeCalled();
        $filesystem = $filesystem->reveal();

        $prefixFilesystem = new PrefixFilesystem($filesystem, $prefix);
        $prefixFilesystem->copy($path, $newpath);
    }

    public function testDelete()
    {
        $prefix = 'plugins/swag_simple_test/';
        $path = 'test/file.txt';

        $filesystem = $this->prophesize(FilesystemInterface::class);
        $filesystem->delete(Argument::is($prefix . $path))->shouldBeCalled();
        $filesystem = $filesystem->reveal();

        $prefixFilesystem = new PrefixFilesystem($filesystem, $prefix);
        $prefixFilesystem->delete($path);
    }

    public function testDeleteDir()
    {
        $prefix = 'plugins/swag_simple_test/';
        $path = 'test/file.txt';

        $filesystem = $this->prophesize(FilesystemInterface::class);
        $filesystem->deleteDir(Argument::is($prefix . $path))->shouldBeCalled();
        $filesystem = $filesystem->reveal();

        $prefixFilesystem = new PrefixFilesystem($filesystem, $prefix);
        $prefixFilesystem->deleteDir($path);
    }

    public function testCreateDir()
    {
        $prefix = 'plugins/swag_simple_test/';
        $path = 'test';

        $filesystem = $this->prophesize(FilesystemInterface::class);
        $filesystem->createDir(Argument::is($prefix . $path), Argument::is([]))->shouldBeCalled();
        $filesystem = $filesystem->reveal();

        $prefixFilesystem = new PrefixFilesystem($filesystem, $prefix);
        $prefixFilesystem->createDir($path);
    }

    public function testSetVisibility()
    {
        $prefix = 'plugins/swag_simple_test/';
        $path = 'test/file.txt';

        $filesystem = $this->prophesize(FilesystemInterface::class);
        $filesystem->setVisibility(Argument::is($prefix . $path), Argument::is(AdapterInterface::VISIBILITY_PUBLIC))->shouldBeCalled();
        $filesystem = $filesystem->reveal();

        $prefixFilesystem = new PrefixFilesystem($filesystem, $prefix);
        $prefixFilesystem->setVisibility($path, AdapterInterface::VISIBILITY_PUBLIC);
    }

    public function testPut()
    {
        $prefix = 'plugins/swag_simple_test/';
        $path = 'test/file.txt';

        $filesystem = $this->prophesize(FilesystemInterface::class);
        $filesystem->put(Argument::is($prefix . $path), Argument::is('content'), Argument::is([]))->shouldBeCalled();
        $filesystem = $filesystem->reveal();

        $prefixFilesystem = new PrefixFilesystem($filesystem, $prefix);
        $prefixFilesystem->put($path, 'content');
    }

    public function testPutStream()
    {
        $prefix = 'plugins/swag_simple_test/';
        $path = 'test/file.txt';

        $filesystem = $this->prophesize(FilesystemInterface::class);
        $filesystem->putStream(Argument::is($prefix . $path), Argument::is('content'), Argument::is([]))->shouldBeCalled();
        $filesystem = $filesystem->reveal();

        $prefixFilesystem = new PrefixFilesystem($filesystem, $prefix);
        $prefixFilesystem->putStream($path, 'content');
    }

    public function testReadAndDelete()
    {
        $prefix = 'plugins/swag_simple_test/';
        $path = 'test/file.txt';

        $filesystem = $this->prophesize(FilesystemInterface::class);
        $filesystem->readAndDelete(Argument::is($prefix . $path))->shouldBeCalled();
        $filesystem = $filesystem->reveal();

        $prefixFilesystem = new PrefixFilesystem($filesystem, $prefix);
        $prefixFilesystem->readAndDelete($path);
    }

    public function testGet()
    {
        $prefix = 'plugins/swag_simple_test/';
        $path = 'test/file.txt';

        $filesystem = $this->prophesize(FilesystemInterface::class);
        $filesystem->get(Argument::is($prefix . $path), Argument::is(null))->shouldBeCalled();
        $filesystem = $filesystem->reveal();

        $prefixFilesystem = new PrefixFilesystem($filesystem, $prefix);
        $prefixFilesystem->get($path);
    }

    public function testAddPlugin()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Filesystem plugins are not allowed in prefixed filesystems.');

        $filesystem = $this->prophesize(FilesystemInterface::class)->reveal();
        $prefix = 'plugins/swag_simple_test/';

        $prefixFilesystem = new PrefixFilesystem($filesystem, $prefix);
        $prefixFilesystem->addPlugin(new DummyFilesystemPlugin());
    }

    public function testPathTraversal()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Path traversal is not allowed.');

        $filesystem = $this->prophesize(FilesystemInterface::class)->reveal();
        $prefix = 'plugins/swag_simple_test/';

        $prefixFilesystem = new PrefixFilesystem($filesystem, $prefix);
        $prefixFilesystem->has('../../../foo.bar');
    }
}

class DummyFilesystemPlugin implements PluginInterface
{
    public function getMethod()
    {
    }

    public function setFilesystem(FilesystemInterface $filesystem)
    {
    }
}
