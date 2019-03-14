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

namespace ShopwarePlugins\SwagUpdate\Components;

class FileSystem
{
    /**
     * @var string[]
     */
    private $VCSDirs = [
        '.git',
        '.svn',
    ];

    /**
     * @param string $directory
     * @param bool   $fixPermission
     *
     * @return array of errors
     */
    public function checkSingleDirectoryPermissions($directory, $fixPermission = false)
    {
        $errors = [];

        if (!is_dir($directory)) {
            $errors[] = $directory;

            return $errors;
        }

        if ($fixPermission && !is_writable($directory)) {
            $fileInfo = new \SplFileInfo($directory);
            $this->fixDirectoryPermission($fileInfo);
        }

        if (!is_writable($directory)) {
            $errors[] = $directory;

            return $errors;
        }

        return $errors;
    }

    /**
     * @param string $directory
     * @param bool   $fixPermission
     *
     * @return array of errors
     */
    public function checkDirectoryPermissions($directory, $fixPermission = false)
    {
        $errors = $this->checkSingleDirectoryPermissions($directory, $fixPermission);

        if (!empty($errors)) {
            return $errors;
        }

        /** @var \DirectoryIterator $fileInfo */
        foreach (new \DirectoryIterator($directory) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            if ($fileInfo->isFile()) {
                if ($fixPermission && !$fileInfo->isWritable()) {
                    $this->fixFilePermission($fileInfo);
                }

                if (!$fileInfo->isWritable()) {
                    $errors[] = $fileInfo->getPathname();
                }

                continue;
            }

            // skip VCS dirs
            if (in_array($fileInfo->getBasename(), $this->VCSDirs, true)) {
                continue;
            }

            if ($fixPermission && !$fileInfo->isWritable()) {
                $this->fixDirectoryPermission($fileInfo);
            }

            if (!$fileInfo->isWritable()) {
                $errors[] = $fileInfo->getPathname();
                continue;
            }

            $errors = array_merge($errors, $this->checkDirectoryPermissions($fileInfo->getPathname(), $fixPermission));
        }

        return $errors;
    }

    private function fixDirectoryPermission(\SplFileInfo $fileInfo)
    {
        try {
            $permission = substr(sprintf('%o', $fileInfo->getPerms()), -4);
        } catch (\Exception $e) {
            // cannot get permissions...
            return;
        }

        $newPermission = $permission;

        // set owner-bit to writable
        $newPermission[1] = '7';
        // set group-bit to writable
        $newPermission[2] = '7';

        $newPermission = octdec($newPermission);
        chmod($fileInfo->getPathname(), $newPermission);
        clearstatcache(false, $fileInfo->getPathname());
    }

    private function fixFilePermission(\SplFileInfo $fileInfo)
    {
        try {
            $permission = substr(sprintf('%o', $fileInfo->getPerms()), -4);
        } catch (\Exception $e) {
            // cannot get permissions...
            return;
        }

        $newPermission = $permission;

        // set owner-bit to writable
        $newPermission[1] = '6';
        // set group-bit to writable
        $newPermission[2] = '6';

        if ($fileInfo->isExecutable()) {
            // set owner-bit to writable/executable
            $newPermission[1] = '7';
            // set group-bit to writable/executable
            $newPermission[2] = '7';
        }

        $newPermission = octdec($newPermission);
        chmod($fileInfo->getPathname(), $newPermission);
        clearstatcache(false, $fileInfo->getPathname());
    }
}
