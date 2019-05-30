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

namespace Shopware\Components\HttpCache\UrlProvider;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\RouterInterface;

class ManufacturerProvider implements UrlProviderInterface
{
    const NAME = 'manufacturer';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(Connection $connection, RouterInterface $router)
    {
        $this->connection = $connection;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrls(Context $context, $limit = null, $offset = null)
    {
        $qb = $this->getBaseQuery()
            ->addSelect(['id'])
            ->orderBy('id');

        if ($limit !== null && $offset !== null) {
            $qb->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        $result = $qb->execute()->fetchAll();

        if (!count($result)) {
            return [];
        }

        return $this->router->generateList(
            array_map(
                function ($manufacturer) {
                    return ['sViewport' => 'listing', 'sAction' => 'manufacturer', 'sSupplier' => $manufacturer['id']];
                },
                $result
            ),
            $context
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCount(Context $context)
    {
        return (int) $this->getBaseQuery()
            ->addSelect(['COUNT(id)'])
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return QueryBuilder
     */
    private function getBaseQuery()
    {
        return $this->connection->createQueryBuilder()
            ->from('s_articles_supplier');
    }
}
