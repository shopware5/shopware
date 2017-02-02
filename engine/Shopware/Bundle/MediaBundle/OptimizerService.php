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

use Shopware\Bundle\MediaBundle\Exception\OptimizerNotFoundException;
use Shopware\Bundle\MediaBundle\Optimizer\OptimizerInterface;

class OptimizerService implements OptimizerServiceInterface
{
    /**
     * @var OptimizerInterface[]
     */
    private $optimizers = [];

    /**
     * @param OptimizerInterface[] $optimizers
     */
    public function __construct(array $optimizers)
    {
        $this->optimizers = $optimizers;
    }

    /**
     * @inheritdoc
     */
    public function optimize($filepath)
    {
        $mime = $this->getMimeTypeByFile($filepath);

        $optimizer = $this->getOptimizerByMimeType($mime);
        $optimizer->run($filepath);
    }

    /**
     * @inheritdoc
     */
    public function getOptimizers()
    {
        return $this->optimizers;
    }

    /**
     * @inheritdoc
     */
    private function getMimeTypeByFile($filepath)
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        return $finfo->file($filepath);
    }

    /**
     * @inheritdoc
     */
    public function getOptimizerByMimeType($mime)
    {
        foreach ($this->optimizers as $optimizer) {
            if (in_array($mime, $optimizer->getSupportedMimeTypes()) && $optimizer->isRunnable()) {
                return $optimizer;
            }
        }

        throw new OptimizerNotFoundException(sprintf('Optimizer for mime-type "%s" not found.', $mime));
    }
}
