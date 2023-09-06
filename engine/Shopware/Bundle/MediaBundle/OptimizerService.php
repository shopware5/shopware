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

namespace Shopware\Bundle\MediaBundle;

use finfo;
use IteratorAggregate;
use Shopware\Bundle\MediaBundle\Exception\OptimizerNotFoundException;
use Shopware\Bundle\MediaBundle\Optimizer\OptimizerInterface;

class OptimizerService implements OptimizerServiceInterface
{
    /**
     * @var OptimizerInterface[]
     */
    private $optimizers = [];

    public function __construct(IteratorAggregate $optimizers)
    {
        $this->optimizers = iterator_to_array($optimizers, false);
    }

    /**
     * {@inheritdoc}
     */
    public function optimize($filepath)
    {
        $mime = $this->getMimeTypeByFile($filepath);

        $optimizer = $this->getOptimizerByMimeType($mime);

        // Reading and resetting the permissions on the file since some optimizer are unable to do so themselves.
        $perms = fileperms($filepath);
        if (!\is_int($perms)) {
            $perms = 0644;
        }
        $optimizer->run($filepath);
        chmod($filepath, $perms);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptimizers()
    {
        return $this->optimizers;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptimizerByMimeType($mime)
    {
        foreach ($this->optimizers as $optimizer) {
            if (\in_array($mime, $optimizer->getSupportedMimeTypes()) && $optimizer->isRunnable()) {
                return $optimizer;
            }
        }

        throw new OptimizerNotFoundException(sprintf('Optimizer for mime-type "%s" not found.', $mime));
    }

    /**
     * {@inheritdoc}
     */
    private function getMimeTypeByFile($filepath)
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);

        return $finfo->file($filepath);
    }
}
