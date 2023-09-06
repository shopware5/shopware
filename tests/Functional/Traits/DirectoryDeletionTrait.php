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

namespace Shopware\Tests\Functional\Traits;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

trait DirectoryDeletionTrait
{
    /**
     * Deletes a directory recursively, no matter if it contains anything or not
     */
    protected function deleteDirectory(string $dir): void
    {
        if (!file_exists($dir)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

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
