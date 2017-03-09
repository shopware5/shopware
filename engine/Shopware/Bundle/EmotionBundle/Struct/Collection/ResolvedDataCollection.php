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

use Shopware\Bundle\SearchBundle\BatchProductSearchResult;
use Shopware\Bundle\StoreFrontBundle\Struct\Extendable;
use Shopware\Bundle\StoreFrontBundle\Struct\Media;

class ResolvedDataCollection extends Extendable
{
    /**
     * @var BatchProductSearchResult
     */
    private $batchResult;

    /**
     * @var Media[]
     */
    private $mediaList = [];

    /**
     * @param int $mediaId
     *
     * @return Media|null
     */
    public function getMedia($mediaId)
    {
        if (!array_key_exists($mediaId, $this->mediaList)) {
            return null;
        }

        return $this->mediaList[$mediaId];
    }

    /**
     * @param int $mediaPath
     *
     * @return Media|null
     */
    public function getMediaByPath($mediaPath)
    {
        if (!array_key_exists($mediaPath, $this->mediaList)) {
            return null;
        }

        return $this->mediaList[$mediaPath];
    }

    /**
     * @return BatchProductSearchResult
     */
    public function getBatchResult()
    {
        return $this->batchResult;
    }

    /**
     * @param BatchProductSearchResult $batchResult
     */
    public function setBatchResult($batchResult)
    {
        $this->batchResult = $batchResult;
    }

    /**
     * @param Media[] $mediaList
     */
    public function setMediaList($mediaList)
    {
        $this->mediaList = $mediaList;
    }
}
