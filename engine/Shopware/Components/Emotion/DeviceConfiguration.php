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

namespace Shopware\Components\Emotion;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Models\Emotion\Emotion;

class DeviceConfiguration
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param int $categoryId
     * @param int $pageIndex
     * @return array[]
     */
    public function getListingEmotions($categoryId, $pageIndex)
    {
        $emotions = $this->get($categoryId);

        if (max(array_column($emotions, 'showListing')) > 0) {
            return $emotions;
        }

        if ((int) $pageIndex > 0) {
            return $this->getEmotionsByVisibility($emotions, [
                Emotion::LISTING_VISIBILITY_ONLY_LISTING,
                Emotion::LISTING_VISIBILITY_ONLY_START_AND_LISTING
            ]);
        }

        $entryPageEmotions = $this->getEmotionsByVisibility($emotions, [
            Emotion::LISTING_VISIBILITY_ONLY_START,
            Emotion::LISTING_VISIBILITY_ONLY_START_AND_LISTING
        ]);

        if (!empty($entryPageEmotions)) {
            return $entryPageEmotions;
        }

        return $this->getEmotionsByVisibility($emotions, [
            Emotion::LISTING_VISIBILITY_ONLY_LISTING,
            Emotion::LISTING_VISIBILITY_ONLY_START_AND_LISTING
        ]);
    }

    /**
     * @param int $categoryId
     * @throws \Exception
     * @return array
     */
    public function get($categoryId)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select([
            'emotion.id',
            'emotion.device as devices',
            'emotion.show_listing as showListing',
            'emotion.fullscreen',
            'emotion.position',
            'emotion.listing_visibility'
        ]);

        $query->from('s_emotion', 'emotion');
        $query->where('emotion.active = 1');
        $query->andWhere('emotion.is_landingpage = 0');
        $query->andWhere('(emotion.valid_to  >= NOW() OR emotion.valid_to IS NULL)');
        $query->andWhere('(emotion.valid_from <= NOW() OR emotion.valid_from IS NULL)');
        $query->andWhere('emotion.preview_id IS NULL');
        $query->setParameter(':categoryId', $categoryId);
        $query->innerJoin('emotion', 's_emotion_categories', 'category', 'category.emotion_id = emotion.id AND category.category_id = :categoryId');

        /**@var $statement \PDOStatement */
        $statement = $query->execute();

        $emotions = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $emotions = array_map(function ($emotion) {
            $emotion['devicesArray'] = explode(',', $emotion['devices']);
            return $emotion;
        }, $emotions);

        return $this->sortEmotionsByPositionAndId($emotions);
    }

    /**
     * @param int $emotionId
     * @throws \Exception
     * @return array
     */
    public function getById($emotionId)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select([
            'emotion.id',
            'emotion.device as devices',
            'emotion.show_listing as showListing'
        ]);

        $query->from('s_emotion', 'emotion')
            ->where('emotion.id = :emotionId')
            ->setParameter(':emotionId', $emotionId);

        /**@var $statement \PDOStatement */
        $statement = $query->execute();

        return $statement->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @param $id
     * @throws \Exception
     * @return array
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
     * Get shops of landingpage by emotion id.
     *
     * @param $emotionId
     * @return array
     */
    public function getLandingPageShops($emotionId)
    {
        $query = $this->getLandingPageShopsQuery();

        $query->setParameter(':id', $emotionId);

        /**@var $statement \PDOStatement */
        $statement = $query->execute();

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * @param $id
     * @return array|null
     */
    private function getMasterLandingPage($id)
    {
        $query = $this->getLandingPageQuery()
            ->andWhere('emotion.id = :id')
            ->setParameter('id', $id);

        /**@var $statement \PDOStatement */
        $statement = $query->execute();

        return $statement->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @param int $parentId
     * @return array
     */
    private function getChildrenLandingPages($parentId)
    {
        $query = $this->getLandingPageQuery()
            ->andWhere('emotion.parent_id = :id')
            ->setParameter(':id', $parentId);

        /**@var $statement \PDOStatement */
        $statement = $query->execute();

        $emotions = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return $this->sortEmotionsByPositionAndId($emotions);
    }

    /**
     * @return QueryBuilder
     */
    private function getLandingPageQuery()
    {
        $query = $this->connection->createQueryBuilder();

        $query->select([
            'emotion.id',
            'emotion.position',
            'emotion.device as devices',
            'emotion.name',
            'emotion.seo_title',
            'emotion.seo_keywords',
            'emotion.seo_description',
            'emotion.valid_from',
            'emotion.valid_to',
            'now()'
        ]);

        $query->from('s_emotion', 'emotion')
            ->andWhere('emotion.active = 1')
            ->andWhere('emotion.is_landingpage = 1')
            ->andWhere('(emotion.valid_from IS NULL OR emotion.valid_from <= now())')
            ->andWhere('(emotion.valid_to IS NULL OR emotion.valid_to >= now())')
        ;

        return $query;
    }

    /**
     * Get QueryBuilder for shops of an emotion.
     *
     * @return QueryBuilder
     */
    private function getLandingPageShopsQuery()
    {
        $query = $this->connection->createQueryBuilder();

        $query->select([ 'shops.shop_id' ])
            ->from('s_emotion_shops', 'shops')
            ->where('shops.emotion_id = :id');

        return $query;
    }

    /**
     * @param array $emotions
     * @return array
     */
    private function sortEmotionsByPositionAndId(array $emotions)
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
     * @param array $emotions
     * @param array $visibility
     * @return array
     */
    private function getEmotionsByVisibility(array $emotions, array $visibility)
    {
        return array_filter($emotions, function ($emotion) use ($visibility) {
            return in_array($emotion['listing_visibility'], $visibility);
        });
    }
}
