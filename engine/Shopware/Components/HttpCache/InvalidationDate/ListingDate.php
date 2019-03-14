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

namespace Shopware\Components\HttpCache\InvalidationDate;

use Doctrine\DBAL\Connection;
use Enlight_Controller_Request_Request as Request;

class ListingDate implements InvalidationDateInterface
{
    use InvalidationDateTrait;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $route;

    /**
     * @param string $route
     */
    public function __construct(Connection $connection, $route = 'frontend/listing/index')
    {
        $this->connection = $connection;
        $this->route = $route;
    }

    /**
     * {@inheritdoc}
     */
    public function getInvalidationDate(Request $request)
    {
        $categoryId = (int) $request->getParam('sCategory');
        $emotions = $this->getNextDateQuery($categoryId)->execute()->fetchAll();

        if (empty($emotions)) {
            return null;
        }

        $dates = array_merge(
            array_column($emotions, 'valid_from'),
            array_column($emotions, 'valid_to')
        );

        return $this->getMostRecentDate($dates);
    }

    /**
     * {@inheritdoc}
     */
    public function getNextDateQuery($resourceId)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select(['valid_from', 'valid_to'])
            ->from('s_emotion', 'emotion')
            ->innerJoin(
                'emotion',
                's_emotion_categories',
                'mapping',
                'mapping.emotion_id = emotion.id AND mapping.category_id = :resourceId')
            ->where('emotion.active = 1')
            ->andWhere('(emotion.valid_from IS NOT NULL OR emotion.valid_to IS NOT NULL)');

        $query->setParameter(':resourceId', $resourceId);

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsRoute($route)
    {
        return $route === $this->route;
    }
}
