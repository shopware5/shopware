<?php
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

namespace Shopware\Tests\Unit\Components\Filesystem\Adapter;

use League\Flysystem\AwsS3v3\AwsS3Adapter;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\MediaBundle\Adapters\AwsS3v3Factory;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class AwsS3v3FactoryTest extends TestCase
{
    /**
     * @var AwsS3v3Factory
     */
    private $factory;

    public function setUp(): void
    {
        $this->factory = new AwsS3v3Factory();
    }

    public function testTypeLocal()
    {
        static::assertSame('s3', $this->factory->getType());
    }

    public function testCreationWithEmptyConfig()
    {
        $this->expectException(MissingOptionsException::class);

        $this->factory->create([]);
    }

    public function testCreationWithValidConfig()
    {
        $filesystem = $this->factory->create([
            'bucket' => 'foobar',
            'region' => 'eu-central-1',
            'credentials' => [
                'key' => 'app-key',
                'secret' => 'app-secret',
            ],
        ]);

        static::assertInstanceOf(AwsS3Adapter::class, $filesystem);
    }
}
