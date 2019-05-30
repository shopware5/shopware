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

class StaticProvider implements UrlProviderInterface
{
    const NAME = 'static';

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
            ->addSelect(['id', 'link'])
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
                    if (empty($custom['link']) || !$this->isShopwareLink($custom['link'])) {
                        return ['sViewport' => 'custom', 'sCustom' => $custom['id']];
                    }
                    $parts = parse_url($custom['link']);
                    parse_str($parts['query'], $query);

                    if (isset($query['sViewport,registerFC'])) {
                        unset($query['sViewport,registerFC']);
                        $query['sViewport'] = 'registerFC';
                    }

                    return $query;
                },
                $result
            )),
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
            ->setParameter(':shop', $context->getShopId())
            ->execute()
            ->fetchColumn();
    }

    /**
     * @return QueryBuilder
     */
    protected function getBaseQuery()
    {
        return $this->connection->createQueryBuilder()
            ->from('s_cms_static')
            ->where('active = 1')
            ->andWhere('shop_ids IS NULL OR shop_ids LIKE CONCAT("%|",:shop,"|%")');
    }

    /**
     * @param string $link
     *
     * @return bool
     */
    private function isShopwareLink($link)
    {
        return strpos($link, 'shopware.php') !== false;
    }
}
