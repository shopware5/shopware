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

namespace Shopware\Tests\Unit\Bundle\MediaBundle\Commands;

use DateInterval;
use DateTime;
use Generator;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Shopware\Bundle\MediaBundle\Commands\MediaOptimizeCommand;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Bundle\MediaBundle\OptimizerServiceInterface;
use Shopware\Components\Filesystem\PrefixFilesystem;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class MediaOptimizeCommandTest extends TestCase
{
    /**
     * @dataProvider getFiles
     *
     * @param array<string, mixed> $file
     * @param array<string, mixed> $optimizerParameter
     */
    public function testOptimizeFilesWithSetModifiedDate(array $file, int $expectedCount, array $optimizerParameter): void
    {
        // required as ProgressBar is a final class https://github.com/symfony/symfony/issues/24098
        $progressBarMock = new ProgressBar(new NullOutput());

        $datetime = new DateTime();
        $datetime = $datetime->format('d-m-Y h:i:s');

        $input = $this->getMockBuilder(InputInterface::class)->disableOriginalConstructor()->getMock();
        $media = $this->getMockBuilder(MediaServiceInterface::class)->disableOriginalConstructor()->getMock();
        $optimizer = $this->getMockBuilder(OptimizerServiceInterface::class)->disableOriginalConstructor()->getMock();
        $output = $this->getMockBuilder(OutputInterface::class)->disableOriginalConstructor()->getMock();

        $prefixSystem = $this->getMockBuilder(PrefixFilesystem::class)->disableOriginalConstructor()->getMock();

        $media->method('getFilesystem')->willReturn($prefixSystem);

        $prefixSystem->method('listContents')->willReturn([$file]);

        $input->method('getOption')->willReturn($datetime);

        $mediaCommand = new ReflectionClass(MediaOptimizeCommand::class);
        $mediaCommandMethod = $mediaCommand->getMethod('optimizeFiles');
        $mediaCommandMethod->setAccessible(true);

        $output->method('getVerbosity')->willReturn(null);

        $optimizer->expects(static::exactly($expectedCount))->method('optimize')->with(...$optimizerParameter)->willReturn(1);

        $moc_instance = new MediaOptimizeCommand();

        $mediaCommandMethod->invokeArgs($moc_instance, ['mock_dir', $input, $media, $optimizer, $progressBarMock, $output]);
    }

    public function getFiles(): Generator
    {
        yield 'Future date' => [
            [
                'type' => 'file',
                'path' => 'media/image/39/c9/ee/grafik-20220712-084133RI1xVeZ6gUpof_1280x1280.png',
                'timestamp' => (new DateTime())->add(DateInterval::createFromDateString('2 day'))->getTimestamp(),
                'size' => 75541,
                'dirname' => 'media/image/39/c9/ee',
                'basename' => 'grafik-20220712-084133RI1xVeZ6gUpof_1280x1280.png',
                'extension' => 'png',
                'filename' => 'grafik-20220712-084133RI1xVeZ6gUpof_1280x1280',
            ],
            1,
            ['media/image/39/c9/ee/grafik-20220712-084133RI1xVeZ6gUpof_1280x1280.png'],
            ]
        ;

        yield 'Past date' => [
            [
                'type' => 'file',
                'path' => 'media/image/39/c9/ee/grafik-20220712-084133RI1xVeZ6gUpof_1280x1280.png',
                'timestamp' => (new DateTime())->sub(DateInterval::createFromDateString('2 day'))->getTimestamp(),
                'size' => 75541,
                'dirname' => 'media/image/39/c9/ee',
                'basename' => 'grafik-20220712-084133RI1xVeZ6gUpof_1280x1280.png',
                'extension' => 'png',
                'filename' => 'grafik-20220712-084133RI1xVeZ6gUpof_1280x1280',
            ],
            0,
            [],
            ];
    }
}
