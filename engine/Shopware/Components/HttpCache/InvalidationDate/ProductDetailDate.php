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

class ProductDetailDate implements InvalidationDateInterface
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
     * @var \Enlight_Controller_Front
     */
    private $front;

    /**
     * @param string $route
     */
    public function __construct(Connection $connection, \Enlight_Controller_Front $front, $route = 'frontend/detail/index')
    {
        $this->connection = $connection;
        $this->front = $front;
        $this->route = $route;
    }

    /**
     * {@inheritdoc}
     */
    public function getInvalidationDate(Request $request)
    {
        $orderNumber = $this->front->Request()->getParam('number');
        $releaseDates = $this->getNextDateQuery($orderNumber)->execute()->fetchAll();
        $dates = array_column($releaseDates, 'releasedate');

        return $this->getMostRecentDate($dates);
    }

    /**
     * {@inheritdoc}
     */
    public function getNextDateQuery($resourceId)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select('releasedate')
            ->from('s_articles_details', 'detail')
            ->where('detail.ordernumber = :resourceId')
            ->andWhere('detail.active = 1')
            ->andWhere('detail.releasedate IS NOT NULL')
            ->orderBy('detail.releasedate', 'DESC')
            ->setMaxResults(1)
            ->setParameter(':resourceId', $resourceId);

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
