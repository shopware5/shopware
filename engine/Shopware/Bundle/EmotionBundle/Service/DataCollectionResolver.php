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

namespace Shopware\Bundle\EmotionBundle\Service;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\EmotionBundle\Struct\Collection\PrepareDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Collection\ResolvedDataCollection;
use Shopware\Bundle\SearchBundle\BatchProductSearch;
use Shopware\Bundle\StoreFrontBundle\Service\MediaServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class DataCollectionResolver implements DataCollectionResolverInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * @var BatchProductSearch
     */
    private $batchProductSearch;

    public function __construct(
        BatchProductSearch $batchProductSearch,
        Connection $connection,
        MediaServiceInterface $mediaService
    ) {
        $this->batchProductSearch = $batchProductSearch;
        $this->connection = $connection;
        $this->mediaService = $mediaService;
    }

    /**
     * @return ResolvedDataCollection
     */
    public function resolve(PrepareDataCollection $prepareDataCollection, ShopContextInterface $context)
    {
        // resolve prepared data
        $batchResult = $this->resolveBatchRequest($prepareDataCollection, $context);
        $mediaList = $this->resolveMedia($prepareDataCollection, $context);

        $resolvedDataCollection = new ResolvedDataCollection();
        $resolvedDataCollection->setBatchResult($batchResult);
        $resolvedDataCollection->setMediaList($mediaList);

        return $resolvedDataCollection;
    }

    /**
     * @return array
     */
    private function resolveMedia(PrepareDataCollection $prepareDataCollection, ShopContextInterface $context)
    {
        $mediaIds = $this->convertMediaPathsToIds($prepareDataCollection->getMediaPathList());
        $mediaIds = array_merge($prepareDataCollection->getMediaIdList(), $mediaIds);

        if (count($mediaIds) === 0) {
            return [];
        }

        $mediaIds = array_keys(array_flip($mediaIds));
        $mediaIds = array_map('intval', $mediaIds);

        $mediaList = $this->mediaService->getList($mediaIds, $context);

        $medias = [];
        foreach ($mediaList as $media) {
            $medias[$media->getId()] = $media;
            $medias[$media->getPath()] = $media;
        }

        return $medias;
    }

    /**
     * @param string[] $mediaPaths
     *
     * @return int[]
     */
    private function convertMediaPathsToIds(array $mediaPaths = [])
    {
        return $this->connection->createQueryBuilder()
            ->select(['id'])
            ->from('s_media')
            ->where('path in (:paths)')
            ->setParameter('paths', $mediaPaths, Connection::PARAM_STR_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * @return \Shopware\Bundle\SearchBundle\BatchProductSearchResult
     */
    private function resolveBatchRequest(PrepareDataCollection $prepareDataCollection, ShopContextInterface $context)
    {
        $request = $prepareDataCollection->getBatchRequest();

        return $this->batchProductSearch->search($request, $context);
    }
}
