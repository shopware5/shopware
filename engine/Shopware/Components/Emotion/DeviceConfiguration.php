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
     * @throws \Exception
     * @return array
     */
    public function get($categoryId)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select(array(
            'emotion.id',
            'emotion.device as devices',
            'emotion.show_listing as showListing',
            'emotion.fullscreen'
        ));

        $query->from('s_emotion', 'emotion')
            ->where('emotion.active = 1')
            ->andWhere('emotion.is_landingpage = 0')
            ->andWhere('(emotion.valid_to   >= NOW() OR emotion.valid_to IS NULL)')
            ->andWhere('(emotion.valid_from <= NOW() OR emotion.valid_from IS NULL)')
            ->addOrderBy('emotion.position', 'ASC')
            ->addOrderBy('emotion.id', 'ASC')
            ->setParameter(':categoryId', $categoryId);

        $query->innerJoin(
            'emotion',
            's_emotion_categories',
            'category',
            'category.emotion_id = emotion.id
             AND category.category_id = :categoryId'
        );

        /**@var $statement \PDOStatement */
        $statement = $query->execute();

        $emotions = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $emotions = array_map(function ($emotion) {
            $emotion['devicesArray'] = explode(',', $emotion['devices']);
            return $emotion;
        }, $emotions);

        return $emotions;
    }

    /**
     * @param $emotionId
     * @throws \Exception
     * @return array
     */
    public function getById($emotionId)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select(array(
            'emotion.id',
            'emotion.device as devices',
            'emotion.show_listing as showListing'
        ));

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

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @return QueryBuilder
     */
    private function getLandingPageQuery()
    {
        $query = $this->connection->createQueryBuilder();

        $query->select([
            'emotion.id',
            'emotion.device as devices',
            'emotion.name',
            'emotion.seo_keywords',
            'emotion.seo_description',
            'emotion.valid_from',
            'emotion.valid_to',
            'now()'
        ]);

        $query->from('s_emotion', 'emotion')
            ->andWhere('emotion.active = 1')
            ->andWhere('(valid_from IS NULL OR valid_from <= now())')
            ->andWhere('(valid_to IS NULL OR valid_to >= now())')
            ->orderBy('emotion.position', 'ASC')
            ->addOrderBy('emotion.id', 'ASC')
        ;

        return $query;
    }
}
