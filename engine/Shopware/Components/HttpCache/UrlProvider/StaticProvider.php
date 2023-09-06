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

namespace Shopware\Components\HttpCache\UrlProvider;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\RouterInterface;

class StaticProvider implements UrlProviderInterface
{
    public const NAME = 'static';

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

        if (!\count($result)) {
            return [];
        }

        return $this->router->generateList(
            array_filter(
                array_merge(
                    array_map([$this, 'createRequestParameters'], $result),
                    array_map([$this, 'createXhrRequestParameters'], $result)
                )
            ),
            $context
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCount(Context $context)
    {
        $countSites = (int) $this->getBaseQuery()
            ->addSelect(['COUNT(id)'])
            ->setParameter(':shop', $context->getShopId())
            ->execute()
            ->fetchColumn();

        $countSitesForXhr = (int) $this->getBaseQuery()
            ->addSelect(['COUNT(id)'])
            ->setParameter(':shop', $context->getShopId())
            ->andWhere('link IS NULL OR link = ""')
            ->execute()
            ->fetchColumn();

        return $countSites + $countSitesForXhr;
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

    private function isShopwareLink(string $link): bool
    {
        return str_contains($link, 'shopware.php');
    }

    /**
     * @param array<string, mixed> $custom
     *
     * @return array<array-key, mixed>
     */
    private function createRequestParameters(array $custom): array
    {
        if (empty($custom['link']) || !$this->isShopwareLink($custom['link'])) {
            return ['sViewport' => 'custom', 'sCustom' => $custom['id']];
        }
        $parsedQuery = (string) parse_url($custom['link'], PHP_URL_QUERY);
        parse_str($parsedQuery, $query);

        if (isset($query['sViewport,registerFC'])) {
            unset($query['sViewport,registerFC']);
            $query['sViewport'] = 'registerFC';
        }

        return $query;
    }

    /**
     * @param array<string, mixed> $custom
     *
     * @return array<string, mixed>|null
     */
    private function createXhrRequestParameters(array $custom): ?array
    {
        if (empty($custom['link'])) {
            return ['sViewport' => 'custom', 'sCustom' => $custom['id'], 'isXHR' => 1];
        }

        return null;
    }
}
