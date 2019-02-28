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

namespace ShopwarePlugins\SwagUpdate\Components\Steps;

use ShopwarePlugins\SwagUpdate\Components\Archive\Entry\Zip as ZipEntry;
use ShopwarePlugins\SwagUpdate\Components\Archive\Zip;
use Symfony\Component\Filesystem\Filesystem;

class UnpackStep
{
    /**
     * @var string
     */
    private $destinationDir;

    /**
     * @var string
     */
    private $source;

    /**
     * @param string $source
     * @param string $destinationDir
     */
    public function __construct($source, $destinationDir)
    {
        $this->source = $source;
        $this->destinationDir = rtrim($destinationDir, '/') . '/';
    }

    /**
     * @param int $offset
     *
     * @throws \RuntimeException
     * @throws \Exception
     *
     * @return FinishResult|ValidResult
     */
    public function run($offset)
    {
        $fs = new Filesystem();
        $requestTime = time();

        try {
            $source = new Zip($this->source);
            $count = $source->count();
            $source->seek($offset);
        } catch (\Exception $e) {
            @unlink($this->source);
            throw new \Exception(sprintf('Could not open update package:<br>%s', $e->getMessage()), 0, $e);
        }

        /** @var ZipEntry $entry */
        while (list($position, $entry) = $source->each()) {
            $name = $entry->getName();
            $targetName = $this->destinationDir . $name;

            if (!$entry->isDir()) {
                $fs->dumpFile($targetName, $entry->getContents());
            }

            if (time() - $requestTime >= 20 || ($position + 1) % 1000 === 0) {
                $source->close();

                return new ValidResult($position + 1, $count);
            }
        }

        $source->close();
        unlink($this->source);

        return new FinishResult($count, $count);
    }
}
