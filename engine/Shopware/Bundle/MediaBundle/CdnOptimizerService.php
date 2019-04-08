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

namespace Shopware\Bundle\MediaBundle;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;

/**
 * This decorator of the OptimizerService allows to optimize remote files on CDNs like Amazon S3, Microsoft Azure and others.
 */
class CdnOptimizerService implements OptimizerServiceInterface
{
    /**
     * @var OptimizerServiceInterface
     */
    private $optimizerService;

    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    public function __construct(
        OptimizerServiceInterface $optimizerService,
        MediaServiceInterface $mediaService,
        FilesystemInterface $filesystem = null
    ) {
        $this->optimizerService = $optimizerService;
        $this->mediaService = $mediaService;
        $this->filesystem = $filesystem ? $filesystem : new Filesystem(new Local(sys_get_temp_dir()));
    }

    /**
     * {@inheritdoc}
     */
    public function optimize($filepath)
    {
        // If the file is on the local filesystem we can optimize it directly
        if ($this->mediaService->getAdapterType() === 'local') {
            $this->optimizerService->optimize($filepath);

            return;
        }

        // Generate unique temporary file name
        $tempFileName = uniqid('CdnOptimizerTemp-', true);

        $mediaServiceAdapter = $this->mediaService->getFilesystem();

        try {
            // Load file from remote filesystem, optimize it and upload it back again.
            $this->filesystem->writeStream($tempFileName, $mediaServiceAdapter->readStream($filepath));

            $this->optimizerService->optimize(sys_get_temp_dir() . '/' . $tempFileName);

            $mediaServiceAdapter->updateStream($filepath, $this->filesystem->readStream($tempFileName));
        } catch (\League\Flysystem\Exception $exception) {
            throw $exception;
        } finally {
            try {
                $this->filesystem->delete($tempFileName);
            } catch (\Exception $exception) {
                // Empty catch intended, an exception thrown here could hide possible other exception.
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getOptimizers()
    {
        return $this->optimizerService->getOptimizers();
    }

    /**
     * {@inheritdoc}
     */
    public function getOptimizerByMimeType($mime)
    {
        return $this->optimizerService->getOptimizerByMimeType($mime);
    }
}
