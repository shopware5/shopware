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

use Doctrine\Common\Collections\ArrayCollection;
use League\Flysystem\FilesystemInterface;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\MediaBundle\Adapters\AwsS3v3Factory;
use Shopware\Bundle\MediaBundle\Adapters\GoogleStorageFactory;
use Shopware\Bundle\MediaBundle\Adapters\LocalAdapterFactory;
use Shopware\Components\Filesystem\Exception\AdapterFactoryNotFoundException;
use Shopware\Components\Filesystem\Exception\DuplicateFilesystemFactoryException;
use Shopware\Components\Filesystem\FilesystemFactory;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class FilesystemFactoryTest extends TestCase
{
    public function testFactoryWithoutAdapterFactories()
    {
        $this->expectException(AdapterFactoryNotFoundException::class);
        $this->expectExceptionMessage('Adapter factory for type "local" was not found.');

        $factory = new FilesystemFactory(new ArrayCollection());
        $factory->factory($this->getValidFactoryConfig());
    }

    public function testFactoryWithSingleAdapterFactoryThatDoesNotMatch()
    {
        $this->expectException(AdapterFactoryNotFoundException::class);
        $this->expectExceptionMessage('Adapter factory for type "local" was not found.');

        $factory = new FilesystemFactory(new ArrayCollection([
            new AwsS3v3Factory(),
        ]));

        $factory->factory($this->getValidFactoryConfig());
    }

    public function testFactoryWithExistingAdapterFactories()
    {
        $factory = $this->createCompleteFactory();
        $filesystem = $factory->factory($this->getValidFactoryConfig());

        static::assertInstanceOf(FilesystemInterface::class, $filesystem);
    }

    public function testFactoryWithEmptyConfig()
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "type" is missing.');

        $factory = $this->createCompleteFactory();
        $factory->factory([]);
    }

    public function testDuplicateTypeDetection()
    {
        $this->expectException(DuplicateFilesystemFactoryException::class);
        $this->expectExceptionMessage('The type of factory "local" must be unique.');

        new FilesystemFactory(new ArrayCollection([
            new LocalAdapterFactory(),
            new LocalAdapterFactory(),
        ]));
    }

    public function testFactoryWithoutType()
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The required option "type" is missing.');

        $factory = $this->createCompleteFactory();
        $factory->factory(['config' => ['root' => 'web']]);
    }

    private function createCompleteFactory()
    {
        return new FilesystemFactory(new ArrayCollection([
            new LocalAdapterFactory(),
            new AwsS3v3Factory(),
            new GoogleStorageFactory(),
        ]));
    }

    private function getValidFactoryConfig()
    {
        return [
            'type' => 'local',
            'config' => [
                'root' => 'web',
            ],
        ];
    }
}
