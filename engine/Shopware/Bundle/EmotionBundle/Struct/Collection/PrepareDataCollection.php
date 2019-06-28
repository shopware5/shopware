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

namespace Shopware\Bundle\EmotionBundle\Struct\Collection;

use Shopware\Bundle\SearchBundle\BatchProductNumberSearchRequest;
use Shopware\Bundle\StoreFrontBundle\Struct\Extendable;

class PrepareDataCollection extends Extendable
{
    /**
     * @var BatchProductNumberSearchRequest
     */
    private $batchRequest;

    /**
     * @var int[]
     */
    private $mediaIdList = [];

    /**
     * @var string[]
     */
    private $mediaPathList = [];

    public function __construct()
    {
        $this->batchRequest = new BatchProductNumberSearchRequest();
    }

    /**
     * @param int[] $ids
     */
    public function addMediaIds(array $ids = [])
    {
        $this->mediaIdList = array_merge($this->mediaIdList, $ids);
    }

    /**
     * @param string[] $paths
     */
    public function addMediaPaths(array $paths = [])
    {
        $this->mediaPathList = array_merge($this->mediaPathList, $paths);
    }

    /**
     * @return int[]
     */
    public function getMediaIdList()
    {
        return $this->mediaIdList;
    }

    /**
     * @return string[]
     */
    public function getMediaPathList()
    {
        return $this->mediaPathList;
    }

    /**
     * @return BatchProductNumberSearchRequest
     */
    public function getBatchRequest()
    {
        return $this->batchRequest;
    }
}
