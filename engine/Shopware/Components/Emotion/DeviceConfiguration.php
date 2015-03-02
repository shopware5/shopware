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
            'emotion.show_listing as showListing'
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

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
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
     * @param $emotionId
     * @throws \Exception
     * @return array
     */
    public function getLandingPageById($emotionId)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select(array(
            'emotion.id',
            'emotion.device as devices',
        ));

        $query->from('s_emotion', 'emotion')
            ->where('emotion.id = :emotionId')
            ->andWhere('valid_from IS NULL || valid_from <= now()')
            ->andWhere('valid_to IS NULL || valid_to >= now()')
            ->setParameter('emotionId', $emotionId);

        /**@var $statement \PDOStatement */
        $statement = $query->execute();

        return $statement->fetch(\PDO::FETCH_ASSOC);
    }
}
