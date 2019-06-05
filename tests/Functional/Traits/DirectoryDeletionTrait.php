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

namespace Shopware\Tests\Functional\Traits;

trait DirectoryDeletionTrait
{
    /**
     * Deletes a directory recursively, no matter if it contains anything or not
     *
     * @param string $dir
     */
    protected function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        /** @var \SplFileInfo $path */
        foreach ($iterator as $path) {
            if ($path->getFilename() === '.gitkeep') {
                continue;
            }
            if ($path->isDir()) {
                rmdir((string) $path);
            } else {
                if (!$path->isFile()) {
                    continue;
                }
                unlink((string) $path);
            }
        }
    }
}
