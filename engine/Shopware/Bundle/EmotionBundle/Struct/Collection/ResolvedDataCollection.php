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
     * @var array<int|string, Media>
     */
    private array $mediaList = [];

    /**
     * @param int $mediaId
     *
     * @return Media|null
     */
    public function getMedia($mediaId)
    {
        return $this->mediaList[$mediaId] ?? null;
    }

    /**
     * @param string $mediaPath
     *
     * @return Media|null
     */
    public function getMediaByPath($mediaPath)
    {
        return $this->mediaList[$mediaPath] ?? null;
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
     * @param array<int|string, Media> $mediaList
     */
    public function setMediaList($mediaList)
    {
        $this->mediaList = $mediaList;
    }
}
