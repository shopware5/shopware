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

class FormProvider implements UrlProviderInterface
{
    const NAME = 'form';

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var RouterInterface
     */
    protected $router;

    public function __construct(Connection $connection, RouterInterface $router)
    {
        $this->connection = $connection;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrls(Context $context, $limit = null, $offset = null): array
    {
        $qb = $this->getBaseQuery()
            ->addSelect(['id'])
            ->setParameter(':shop', $context->getShopId())
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
            array_filter(array_map(
                function ($custom) {
                    return ['sViewport' => 'forms', 'sFid' => $custom['id']];
                },
                $result
            )),
            $context
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCount(Context $context): int
    {
        return (int) $this->getBaseQuery()
            ->addSelect(['COUNT(id)'])
            ->setParameter(':shop', $context->getShopId())
            ->execute()
            ->fetchColumn();
    }

    protected function getBaseQuery(): QueryBuilder
    {
        return $this->connection->createQueryBuilder()
            ->from('s_cms_support')
            ->where('active = 1')
            ->andWhere('shop_ids IS NULL OR shop_ids LIKE CONCAT("%|",:shop,"|%")');
    }
}
