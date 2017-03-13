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

namespace Shopware\Bundle\MediaBundle;

use League\Flysystem\FilesystemInterface;
use Shopware\Bundle\MediaBundle\Strategy\StrategyInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class MediaMigration implements MediaMigrationInterface
{
    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var StrategyInterface
     */
    private $fromStrategy;

    /**
     * @var StrategyInterface
     */
    private $toStrategy;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var int
     */
    private $filesCount = 0;

    /**
     * @var array
     */
    private $counter = [
        'skipped' => 0,
        'moved' => 0,
    ];

    public function __construct(FilesystemInterface $filesystem, StrategyInterface $fromStrategy, StrategyInterface $toStrategy, OutputInterface $output)
    {
        if ($fromStrategy === $toStrategy) {
            throw new \InvalidArgumentException('The strategies must not be the same.');
        }

        $this->filesystem = $filesystem;
        $this->fromStrategy = $fromStrategy;
        $this->toStrategy = $toStrategy;
        $this->output = $output;
    }

    public function run(bool $skipScan = false): void
    {
        $this->output->writeln(' // Migrating all media files in your filesystem. This might take some time, depending on the number of media files you have.');
        $this->output->writeln('');

        if (!$skipScan) {
            $this->filesCount = $this->countFiles('media');
        }

        $progressBar = $this->createProgressBar();
        $this->migrateFiles('media', $progressBar);
        $progressBar->finish();

        $this->displaySummary();
    }

    public function migrateFile(string $path): void
    {
        $virtualPath = $this->fromStrategy->normalize($path);
        $toPath = $this->toStrategy->encode($virtualPath);

        // file already exists
        if ($this->filesystem->has($toPath)) {
            ++$this->counter['skipped'];

            return;
        }

        // move file to new filesystem and remove the old one
        $this->filesystem->rename($path, $toPath);

        ++$this->counter['moved'];
    }

    public function countFiles(string $directory): int
    {
        /** @var array $contents */
        $contents = $this->filesystem->listContents($directory);
        $cnt = 0;

        foreach ($contents as $item) {
            if ($item['type'] === 'dir') {
                $cnt += $this->countFiles($item['path']);
            }

            if ($item['type'] === 'file') {
                if (strpos($item['basename'], '.') === 0) {
                    continue;
                }

                ++$cnt;
            }
        }

        return $cnt;
    }

    /**
     * @param string      $directory
     * @param ProgressBar $progressBar
     */
    private function migrateFiles($directory, ProgressBar $progressBar)
    {
        /** @var array $contents */
        $contents = $this->filesystem->listContents($directory);

        foreach ($contents as $item) {
            if ($item['type'] === 'dir') {
                $this->migrateFiles($item['path'], $progressBar);
                continue;
            }

            if ($item['type'] === 'file') {
                if (strpos($item['basename'], '.') === 0) {
                    continue;
                }

                $progressBar->setMessage($item['path'], 'filename');
                $this->migrateFile($item['path']);
                $progressBar->advance();
            }
        }
    }

    private function createProgressBar(): ProgressBar
    {
        $progressBar = new ProgressBar($this->output, $this->filesCount);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent%% Elapsed: %elapsed%' . "\n" . ' Current file: %filename%');
        $progressBar->setMessage('', 'filename');

        return $progressBar;
    }

    private function displaySummary(): void
    {
        $rows = [];
        foreach ($this->counter as $key => $value) {
            $rows[] = [$key, $value];
        }

        $this->output->writeln('');
        $this->output->writeln('');

        $table = new Table($this->output);
        $table->setStyle('borderless');
        $table->setHeaders(['Action', 'Number of items']);
        $table->setRows($rows);
        $table->render();
    }
}
