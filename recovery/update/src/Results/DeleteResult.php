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

namespace Shopware\Recovery\Update\Results;

class DeleteResult
{
    /**
     * @var bool
     */
    private $isReady;

    /**
     * @var int
     */
    private $fileCount;

    /**
     * @param int $fileCount
     */
    public function __construct($fileCount = 0)
    {
        $this->isReady = false;
        $this->fileCount = $fileCount;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return __CLASS__;
    }

    /**
     * @return bool
     */
    public function getIsReady()
    {
        return $this->isReady;
    }

    /**
     * @return int
     */
    public function getFileCount()
    {
        return $this->fileCount;
    }

    /**
     * sets $this->isReady to "true"
     */
    public function setReady()
    {
        $this->isReady = true;
    }

    /**
     * Counts a $this->fileCount high
     */
    public function countUp()
    {
        ++$this->fileCount;
    }
}
