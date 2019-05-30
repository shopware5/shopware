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

namespace Shopware\Recovery\Update;

class PathBuilder
{
    /**
     * @var string
     */
    private $baseDir;

    /**
     * @var string
     */
    private $sourceDir;

    /**
     * @var string
     */
    private $updateDirRelative;

    /**
     * @var string
     */
    private $backupDirRelative;

    /**
     * @param string $basePath
     * @param string $sourcePath
     * @param string $backupPath
     */
    public function __construct($basePath, $sourcePath, $backupPath)
    {
        $baseDir = rtrim($basePath, '/\\') . DIRECTORY_SEPARATOR;
        $sourceDir = rtrim($sourcePath, '/\\') . DIRECTORY_SEPARATOR;
        $backupDir = rtrim($backupPath, '/\\') . DIRECTORY_SEPARATOR;

        $updateDirRelative = str_replace($baseDir, '', $sourceDir);
        $backupDirRelative = str_replace($baseDir, '', $backupDir);

        $this->sourceDir = $sourceDir;
        $this->baseDir = $basePath;

        $this->updateDirRelative = $updateDirRelative;
        $this->backupDirRelative = $backupDirRelative;
    }

    /**
     * @return string
     */
    public function getSourceDir()
    {
        return $this->sourceDir;
    }

    /**
     * @return string
     */
    public function getBackupDirRelative()
    {
        return $this->backupDirRelative;
    }

    /**
     * @return string
     */
    public function createTargetPath(\SplFileInfo $file)
    {
        return str_ireplace($this->sourceDir, '', $file->getPathname());
    }

    /**
     * @return string
     */
    public function createSourcePath(\SplFileInfo $file)
    {
        return $this->updateDirRelative . $this->createTargetPath($file);
    }

    /**
     * @return string
     */
    public function createBackupPath(\SplFileInfo $file)
    {
        return $this->backupDirRelative . $this->createTargetPath($file);
    }

    /**
     * @return string
     */
    public function getUpdateDirRelative()
    {
        return $this->updateDirRelative;
    }
}
