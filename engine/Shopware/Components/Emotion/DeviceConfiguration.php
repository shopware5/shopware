<?php

declare(strict_types=1);
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

namespace Shopware\Components\Emotion;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Models\Emotion\Emotion;

class DeviceConfiguration implements DeviceConfigurationInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @deprecated - Unused. Will be removed without replacement in 5.8
     *
     * @param int $categoryId
     * @param int $pageIndex
     *
     * @return array<array<string, mixed>>
     */
    public function getListingEmotions($categoryId, $pageIndex)
    {
        $emotions = $this->get($categoryId);
        $showListing = array_column($emotions, 'showListing');

        if (!empty($showListing) && max($showListing) > 0) {
            return $emotions;
        }

        if ((int) $pageIndex > 0) {
            return $this->getEmotionsByVisibility($emotions, [
                Emotion::LISTING_VISIBILITY_ONLY_LISTING,
                Emotion::LISTING_VISIBILITY_ONLY_START_AND_LISTING,
            ]);
        }

        $entryPageEmotions = $this->getEmotionsByVisibility($emotions, [
            Emotion::LISTING_VISIBILITY_ONLY_START,
            Emotion::LISTING_VISIBILITY_ONLY_START_AND_LISTING,
        ]);

        if (!empty($entryPageEmotions)) {
            return $entryPageEmotions;
        }

        return $this->getEmotionsByVisibility($emotions, [
            Emotion::LISTING_VISIBILITY_ONLY_LISTING,
            Emotion::LISTING_VISIBILITY_ONLY_START_AND_LISTING,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function get($categoryId)
    {
        $emotions = $this->connection->createQueryBuilder()
            ->select([
                'emotion.id',
                'emotion.device as devices',
                'emotion.show_listing as showListing',
                'emotion.fullscreen',
                'emotion.customer_stream_ids',
                'emotion.replacement',
                'emotion.position',
                'emotion.listing_visibility',
                'GROUP_CONCAT(shops.shop_id SEPARATOR \',\') as shopIds',
            ])
            ->from('s_emotion', 'emotion')
            ->leftJoin('emotion', 's_emotion_shops', 'shops', 'shops.emotion_id = emotion.id')
            ->andWhere('emotion.active = 1')
            ->andWhere('emotion.is_landingpage = 0')
            ->andWhere('(emotion.valid_to   >= NOW() OR emotion.valid_to IS NULL)')
            ->andWhere('(emotion.valid_from <= NOW() OR emotion.valid_from IS NULL)')
            ->andWhere('emotion.preview_id IS NULL')
            ->addOrderBy('emotion.position', 'ASC')
            ->addOrderBy('emotion.id', 'ASC')
            ->groupBy('emotion.id')
            ->setParameter(':categoryId', $categoryId)
            ->innerJoin(
                'emotion',
                's_emotion_categories',
                'category',
                'category.emotion_id = emotion.id
             AND category.category_id = :categoryId'
            )
            ->execute()
            ->fetchAllAssociative();

        $emotions = array_map(function ($emotion) {
            $emotion['devicesArray'] = explode(',', $emotion['devices']);
            $emotion['shopIds'] = array_filter(explode(',', $emotion['shopIds'] ?? ''));

            return $emotion;
        }, $emotions);

        return $this->sortEmotionsByPositionAndId($emotions);
    }

    /**
     * {@inheritdoc}
     */
    public function getById($emotionId)
    {
        $emotion = $this->connection->createQueryBuilder()
            ->select([
                'emotion.id',
                'emotion.device as devices',
                'emotion.show_listing as showListing',
            ])
            ->from('s_emotion', 'emotion')
            ->where('emotion.id = :emotionId')
            ->setParameter(':emotionId', $emotionId)
            ->execute()
            ->fetchAssociative();

        if (!\is_array($emotion)) {
            return [];
        }

        return $emotion;
    }

    /**
     * {@inheritdoc}
     */
    public function getLandingPage($id)
    {
        $master = $this->getMasterLandingPage($id);

        if (!$master) {
            return null;
        }

        $children = $this->getChildrenLandingPages($id);
        $children = array_merge([$master], $children);

        $children = array_map(function ($emotion) {
            $emotion['devicesArray'] = explode(',', $emotion['devices']);

            return $emotion;
        }, $children);

        $master['emotions'] = $children;

        return $master;
    }

    /**
     * {@inheritdoc}
     */
    public function getLandingPageShops($emotionId)
    {
        return $this->getLandingPageShopsQuery()
            ->setParameter(':id', $emotionId)
            ->execute()
            ->fetchFirstColumn();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getMasterLandingPage(int $id): ?array
    {
        $landingPage = $this->getLandingPageQuery()
            ->andWhere('emotion.id = :id')
            ->setParameter('id', $id)
            ->execute()
            ->fetchAssociative();

        if (!\is_array($landingPage)) {
            return null;
        }

        return $landingPage;
    }

    /**
     * @return array<array<string, mixed>>
     */
    private function getChildrenLandingPages(int $parentId): array
    {
        $emotions = $this->getLandingPageQuery()
            ->andWhere('emotion.parent_id = :id')
            ->setParameter(':id', $parentId)
            ->execute()
            ->fetchAllAssociative();

        return $this->sortEmotionsByPositionAndId($emotions);
    }

    private function getLandingPageQuery(): QueryBuilder
    {
        return $this->connection->createQueryBuilder()
            ->select([
                'emotion.id',
                'emotion.position',
                'emotion.device as devices',
                'emotion.name',
                'emotion.seo_title',
                'emotion.seo_keywords',
                'emotion.seo_description',
                'emotion.valid_from',
                'emotion.valid_to',
                'emotion.customer_stream_ids',
                'emotion.replacement',
                'now()',
            ])
            ->from('s_emotion', 'emotion')
            ->andWhere('emotion.active = 1')
            ->andWhere('emotion.is_landingpage = 1')
            ->andWhere('(emotion.valid_from IS NULL OR emotion.valid_from <= now())')
            ->andWhere('(emotion.valid_to IS NULL OR emotion.valid_to >= now())')
            ->orderBy('emotion.position', 'ASC')
            ->addOrderBy('emotion.id', 'ASC');
    }

    /**
     * Get QueryBuilder for shops of an emotion.
     */
    private function getLandingPageShopsQuery(): QueryBuilder
    {
        return $this->connection->createQueryBuilder()
            ->select(['shops.shop_id'])
            ->from('s_emotion_shops', 'shops')
            ->where('shops.emotion_id = :id');
    }

    /**
     * @param array<array<string, mixed>> $emotions
     *
     * @return array<array<string, mixed>>
     */
    private function sortEmotionsByPositionAndId(array $emotions): array
    {
        usort($emotions, function ($a, $b) {
            if ($a['position'] === $b['position']) {
                return ($a['id'] < $b['id']) ? -1 : 1;
            }

            return ($a['position'] < $b['position']) ? -1 : 1;
        });

        return $emotions;
    }

    /**
     * @param array<array<string, mixed>>               $emotions
     * @param array<Emotion::LISTING_VISIBILITY_ONLY_*> $visibility
     *
     * @return array<array<string, mixed>>
     */
    private function getEmotionsByVisibility(array $emotions, array $visibility): array
    {
        return array_filter($emotions, function ($emotion) use ($visibility) {
            return \in_array($emotion['listing_visibility'], $visibility);
        });
    }
}
