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

class CleanupFilesFinder
{
    /**
     * @var string
     */
    private $shopwarePath;

    /**
     * @param string $shopwarePath
     */
    public function __construct($shopwarePath)
    {
        $this->shopwarePath = $shopwarePath;
    }

    /**
     * @return string[]
     */
    public function getCleanupFiles()
    {
        $cleanupFile = UPDATE_ASSET_PATH . '/cleanup.txt';
        if (!is_file($cleanupFile)) {
            return [];
        }

        $lines = file($cleanupFile, \FILE_IGNORE_NEW_LINES);

        $cleanupList = [];
        foreach ($lines as $path) {
            $realpath = $this->shopwarePath . '/' . $path;
            if (file_exists($realpath)) {
                $cleanupList[] = $realpath;
            }
        }

        return $cleanupList;
    }
}
