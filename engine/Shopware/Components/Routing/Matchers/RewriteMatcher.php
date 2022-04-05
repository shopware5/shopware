<?php

declare(strict_types=1);
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

namespace Shopware\Components\Routing\Matchers;

use Doctrine\DBAL\Connection;
use Shopware\Components\QueryAliasMapper;
use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\MatcherInterface;
use Shopware_Components_Config as Config;

class RewriteMatcher implements MatcherInterface
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var array{module: 'frontend', controller: 'index', action: 'index'}
     */
    protected $defaultRoute = [
        'module' => 'frontend',
        'controller' => 'index',
        'action' => 'index',
    ];

    private Config $config;

    private QueryAliasMapper $queryAliasMapper;

    public function __construct(Connection $connection, QueryAliasMapper $queryAliasMapper, Config $config)
    {
        $this->connection = $connection;
        $this->queryAliasMapper = $queryAliasMapper;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function match($pathInfo, Context $context)
    {
        if (str_starts_with($pathInfo, '/backend/') || str_starts_with($pathInfo, '/api/')) {
            return $pathInfo;
        }

        $shopId = $context->getShopId();
        // Consider SEO URLs only if the shop ID is set, which is the case e.g. for storefront requests
        if ($shopId === null) {
            return $pathInfo;
        }

        // Rewrites queries
        $params = $context->getParams();
        $params = $this->queryAliasMapper->replaceShortParams($params);

        if (isset($params['sAction'])) {
            $params['action'] = $params['sAction'];
        }

        if (isset($params['sViewport'])) {
            $params['controller'] = $params['sViewport'];
        }

        $context->setParams($params);

        // /widgets and /index supports short request queries
        if ($pathInfo === '/' || str_starts_with($pathInfo, '/widgets/')) {
            return $pathInfo;
        }

        $pathInfo = ltrim($pathInfo, '/');
        $route = $this->getRoute($shopId, $pathInfo);

        if (!\is_array($route)) {
            return $pathInfo;
        }

        $query = $this->getQueryFormOrgPath($route['orgPath']);
        if (empty($route['main']) || (int) $route['shopId'] !== $shopId) {
            $query['rewriteAlias'] = true;
        } else {
            $query['rewriteUrl'] = true;
        }

        return $query;
    }

    /**
     * @return array<string, mixed>|false
     */
    private function getRoute(int $shopId, string $pathInfo)
    {
        $sql = '
          SELECT subshopID as shopId, path, org_path as orgPath, main
          FROM s_core_rewrite_urls
          WHERE path LIKE :pathInfo
          ORDER BY subshopID = :shopId DESC, main DESC
          LIMIT 1
        ';

        if ($this->config->get('ignore_trailing_slash')) {
            $sql = '
                (' . $sql . '
                ) UNION ALL (
                  SELECT subshopID as shopId, path, org_path as orgPath, 0 as main
                  FROM s_core_rewrite_urls
                  WHERE (path LIKE TRIM(TRAILING "/" FROM :pathInfo)) OR (TRIM(TRAILING "/" FROM path) LIKE :pathInfo)
                  ORDER BY subshopID = :shopId DESC, LENGTH(path), main DESC
                  LIMIT 1
                )
            ';
        }

        return $this->connection->executeQuery($sql, [
            'shopId' => $shopId,
            'pathInfo' => $pathInfo,
        ])->fetchAssociative();
    }

    /**
     * @return array<string, mixed>
     */
    private function getQueryFormOrgPath(string $orgPath): array
    {
        parse_str($orgPath, $query);
        $query = array_merge($this->defaultRoute, $query);
        if (isset($query['sViewport'])) {
            $query['controller'] = $query['sViewport'];
            unset($query['sViewport']);
            if (isset($query['sAction'])) {
                $query['action'] = $query['sAction'];
                unset($query['sAction']);
            }
        }

        return $query;
    }
}
