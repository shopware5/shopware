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
use Shopware\Bundle\ESIndexingBundle\Console\ProgressHelperInterface;
use Shopware\Bundle\MediaBundle\Strategy\StrategyInterface;

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
     * @var ProgressHelperInterface
     */
    private $progressHelper;

    /**
     * @var MediaMigrationResult
     */
    private $migrationResult;

    public function __construct(FilesystemInterface $filesystem, StrategyInterface $fromStrategy, StrategyInterface $toStrategy, ProgressHelperInterface $progressHelper)
    {
        if ($fromStrategy === $toStrategy) {
            throw new \InvalidArgumentException('The strategies must not be the same.');
        }

        $this->filesystem = $filesystem;
        $this->fromStrategy = $fromStrategy;
        $this->toStrategy = $toStrategy;
        $this->progressHelper = $progressHelper;
        $this->migrationResult = new MediaMigrationResult();
    }

    public function run(bool $skipScan = false): MediaMigrationResult
    {
        $this->progressHelper->writeln(' // Migrating all media files in your filesystem. This might take some time, depending on the number of media files you have.');
        $this->progressHelper->writeln('');

        $fileCount = 0;
        if (!$skipScan) {
            $fileCount = $this->countFiles('media');
        }

        $this->setupProgressBar($fileCount);
        $this->migrateFiles('media');
        $this->progressHelper->finish();

        return $this->migrationResult;
    }

    public function migrateFile(string $path): void
    {
        $virtualPath = $this->fromStrategy->normalize($path);
        $toPath = $this->toStrategy->encode($virtualPath);

        // file already exists
        if ($this->filesystem->has($toPath)) {
            ++$this->migrationResult->skipped;

            return;
        }

        // move file to new filesystem and remove the old one
        $this->filesystem->rename($path, $toPath);

        ++$this->migrationResult->migrated;
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

    private function migrateFiles(string $directory)
    {
        /** @var array $contents */
        $contents = $this->filesystem->listContents($directory);

        foreach ($contents as $item) {
            if ($item['type'] === 'dir') {
                $this->migrateFiles($item['path']);
                continue;
            }

            if ($item['type'] === 'file') {
                if (strpos($item['basename'], '.') === 0) {
                    continue;
                }

                $this->progressHelper->setMessage($item['path'], 'filename');
                $this->migrateFile($item['path']);
                $this->progressHelper->advance();
            }
        }
    }

    private function setupProgressBar(int $fileCount = 0): void
    {
        $this->progressHelper->start($fileCount);
        $this->progressHelper->setFormat(' %current%/%max% [%bar%] %percent%% Elapsed: %elapsed%' . "\n" . ' Current file: %filename%');
        $this->progressHelper->setMessage('', 'filename');
    }
}
