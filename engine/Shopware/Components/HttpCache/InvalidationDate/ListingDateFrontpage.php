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

namespace Shopware\Components\HttpCache\InvalidationDate;

use Doctrine\DBAL\Connection;
use Enlight_Controller_Request_Request as Request;
use Shopware\Models\Shop\Shop;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ListingDateFrontpage implements InvalidationDateInterface
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
     * @var Shop
     */
    private $shop;

    /**
     * @param string $route
     */
    public function __construct(Connection $connection, ContainerInterface $container, $route = 'frontend/index/index')
    {
        $this->connection = $connection;
        $this->route = $route;
        /** @var Shop $shop */
        $shop = $container->get('shop');
        $this->shop = $shop;
    }

    /**
     * {@inheritdoc}
     */
    public function getInvalidationDate(Request $request)
    {
        $categoryId = (int) $this->shop->getCategory()->getId();
        $emotions = $this->getNextDateQuery($categoryId)->execute()->fetchAll();
        $dates = array_filter(
            array_merge(
                array_column($emotions, 'valid_from'),
                array_column($emotions, 'valid_to')
            )
        );

        return $this->getMostRecentDate($dates);
    }

    /**
     * {@inheritdoc}
     */
    public function getNextDateQuery($resourceId)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select(['valid_from', 'valid_to']);
        $query->from('s_emotion', 'emotion');
        $query->innerJoin(
            'emotion',
            's_emotion_categories',
            'mapping',
            'mapping.emotion_id = emotion.id AND mapping.category_id = :resourceId'
        );
        $query->where('emotion.active = 1');
        $query->andWhere('(emotion.valid_from IS NOT NULL OR emotion.valid_to IS NOT NULL)');
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
