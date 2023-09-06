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

namespace Shopware\Bundle\EmotionBundle\Struct\Collection;

use Shopware\Bundle\SearchBundle\BatchProductNumberSearchRequest;
use Shopware\Bundle\StoreFrontBundle\Struct\Extendable;

class PrepareDataCollection extends Extendable
{
    private BatchProductNumberSearchRequest $batchRequest;

    /**
     * @var array<int>
     */
    private array $mediaIdList = [];

    /**
     * @var array<string>
     */
    private array $mediaPathList = [];

    public function __construct()
    {
        $this->batchRequest = new BatchProductNumberSearchRequest();
    }

    /**
     * @param array<int> $ids
     */
    public function addMediaIds(array $ids = [])
    {
        $this->mediaIdList = array_merge($this->mediaIdList, $ids);
    }

    /**
     * @param array<string> $paths
     */
    public function addMediaPaths(array $paths = [])
    {
        $this->mediaPathList = array_merge($this->mediaPathList, $paths);
    }

    /**
     * @return array<int>
     */
    public function getMediaIdList()
    {
        return $this->mediaIdList;
    }

    /**
     * @return array<string>
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
