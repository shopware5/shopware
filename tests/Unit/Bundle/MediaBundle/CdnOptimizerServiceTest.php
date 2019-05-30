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

namespace Shopware\Tests\Unit\Bundle\MediaBundle;

use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\MediaBundle\CdnOptimizerService;
use Shopware\Bundle\MediaBundle\MediaService;
use Shopware\Bundle\MediaBundle\Optimizer\JpegtranOptimizer;
use Shopware\Bundle\MediaBundle\OptimizerService;

class CdnOptimizerServiceTest extends TestCase
{
    public function testDeleteOfTempFileOnException()
    {
        $filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods(['writeStream', 'readStream', 'delete'])
            ->getMock();

        $filesystemMock
            ->expects(static::once())
            ->method('writeStream')
            ->withAnyParameters();

        $filesystemMock
            ->expects(static::once())
            ->method('readStream')
            ->withAnyParameters();

        $filesystemMock
            ->expects(static::once())
            ->method('delete');

        $mediaServiceAdapterMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods(['updateStream', 'readStream'])
            ->getMock();

        $mediaServiceAdapterMock
            ->expects(static::once())
            ->method('updateStream')
            ->willThrowException(new FileNotFoundException('test.file'));

        $mediaServiceAdapterMock
            ->expects(static::once())
            ->method('readStream')
            ->withAnyParameters();

        $optimizerServiceMock = $this->getMockBuilder(OptimizerService::class)
            ->disableOriginalConstructor()
            ->setMethods(['optimize'])
            ->getMock();

        $mediaServiceMock = $this->getMockBuilder(MediaService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAdapterType', 'getFilesystem'])
            ->getMock();

        $mediaServiceMock
            ->expects(static::once())
            ->method('getAdapterType')
            ->willReturn('s3');

        $mediaServiceMock
            ->expects(static::once())
            ->method('getFilesystem')
            ->willReturn($mediaServiceAdapterMock);

        $cdnOptimizerService = new CdnOptimizerService(
            $optimizerServiceMock,
            $mediaServiceMock,
            $filesystemMock
        );

        $this->expectException(FileNotFoundException::class);
        $cdnOptimizerService->optimize('hello.world');
    }

    public function testHappyCase()
    {
        $filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods(['writeStream', 'readStream', 'delete'])
            ->getMock();

        $filesystemMock
            ->expects(static::once())
            ->method('writeStream')
            ->withAnyParameters();

        $filesystemMock
            ->expects(static::once())
            ->method('readStream')
            ->withAnyParameters();

        $filesystemMock
            ->expects(static::once())
            ->method('delete');

        $mediaServiceAdapterMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods(['updateStream', 'readStream'])
            ->getMock();

        $mediaServiceAdapterMock
            ->expects(static::once())
            ->method('updateStream')
            ->withAnyParameters();

        $mediaServiceAdapterMock
            ->expects(static::once())
            ->method('readStream')
            ->withAnyParameters();

        $optimizerServiceMock = $this->getMockBuilder(OptimizerService::class)
            ->disableOriginalConstructor()
            ->setMethods(['optimize'])
            ->getMock();

        $mediaServiceMock = $this->getMockBuilder(MediaService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAdapterType', 'getFilesystem'])
            ->getMock();

        $mediaServiceMock
            ->expects(static::once())
            ->method('getAdapterType')
            ->willReturn('s3');

        $mediaServiceMock
            ->expects(static::once())
            ->method('getFilesystem')
            ->willReturn($mediaServiceAdapterMock);

        $cdnOptimizerService = new CdnOptimizerService(
            $optimizerServiceMock,
            $mediaServiceMock,
            $filesystemMock
        );

        $cdnOptimizerService->optimize('hello.world');
    }

    public function testLocalOptimize()
    {
        $optimizerServiceMock = $this->getMockBuilder(OptimizerService::class)
            ->disableOriginalConstructor()
            ->setMethods(['optimize'])
            ->getMock();

        $mediaServiceMock = $this->getMockBuilder(MediaService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAdapterType'])
            ->getMock();

        $mediaServiceMock
            ->expects(static::once())
            ->method('getAdapterType')
            ->willReturn('local');

        $cdnOptimizerService = new CdnOptimizerService(
            $optimizerServiceMock,
            $mediaServiceMock
        );

        $cdnOptimizerService->optimize('hello.world');
    }

    public function testGetOptimizerByMimeType()
    {
        $optimizer = new JpegtranOptimizer();

        $optimizerServiceMock = $this->getMockBuilder(OptimizerService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOptimizerByMimeType'])
            ->getMock();

        $optimizerServiceMock
            ->expects(static::once())
            ->method('getOptimizerByMimeType')
            ->with('jpeg')
            ->willReturn($optimizer);

        $mediaServiceMock = $this->getMockBuilder(MediaService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cdnOptimizerService = new CdnOptimizerService(
            $optimizerServiceMock,
            $mediaServiceMock
        );

        static::assertEquals($optimizer, $cdnOptimizerService->getOptimizerByMimeType('jpeg'));
    }

    public function testGetOptimizers()
    {
        $optimizerServiceMock = $this->getMockBuilder(OptimizerService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOptimizers'])
            ->getMock();

        $optimizerServiceMock
            ->expects(static::once())
            ->method('getOptimizers')
            ->willReturn(['foo', 'bar']);

        $mediaServiceMock = $this->getMockBuilder(MediaService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cdnOptimizerService = new CdnOptimizerService(
            $optimizerServiceMock,
            $mediaServiceMock
        );

        static::assertEquals(['foo', 'bar'], $cdnOptimizerService->getOptimizers());
    }
}
