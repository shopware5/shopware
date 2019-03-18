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

namespace Shopware\Components\Routing\Matchers;

use Doctrine\DBAL\Connection;
use Shopware\Components\QueryAliasMapper;
use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\MatcherInterface;

class RewriteMatcher implements MatcherInterface
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var string[]
     */
    protected $defaultRoute = [
        'module' => 'frontend',
        'controller' => 'index',
        'action' => 'index',
    ];

    /**
     * @var QueryAliasMapper
     */
    private $queryAliasMapper;

    public function __construct(Connection $connection, QueryAliasMapper $queryAliasMapper)
    {
        $this->connection = $connection;
        $this->queryAliasMapper = $queryAliasMapper;
    }

    /**
     * {@inheritdoc}
     */
    public function match($pathInfo, Context $context)
    {
        if (strpos($pathInfo, '/backend/') === 0 || strpos($pathInfo, '/api/') === 0) {
            return $pathInfo;
        }
        if ($context->getShopId() === null) { // only frontend
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
        if ($pathInfo === '/' || strpos($pathInfo, '/widgets/') === 0) {
            return $pathInfo;
        }

        $pathInfo = ltrim($pathInfo, '/');
        $statement = $this->getRouteStatement();
        $statement->bindValue(':shopId', $context->getShopId(), \PDO::PARAM_INT);
        $statement->bindValue(':pathInfo', $pathInfo, \PDO::PARAM_STR);

        if ($statement->execute() && $statement->rowCount() > 0) {
            $route = $statement->fetch(\PDO::FETCH_ASSOC);
            $query = $this->getQueryFormOrgPath($route['orgPath']);
            if (empty($route['main']) || $route['shopId'] != $context->getShopId()) {
                $query['rewriteAlias'] = true;
            } else {
                $query['rewriteUrl'] = true;
            }

            return $query;
        }

        return $pathInfo;
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return \Doctrine\DBAL\Driver\Statement
     */
    private function getRouteStatement()
    {
        $sql = '
          SELECT subshopID as shopId, path, org_path as orgPath, main
          FROM s_core_rewrite_urls
          WHERE path LIKE :pathInfo
          ORDER BY subshopID = :shopId DESC, main DESC
          LIMIT 1
        ';

        return $this->connection->prepare($sql);
    }

    /**
     * @param string $orgPath
     *
     * @return array
     */
    private function getQueryFormOrgPath($orgPath)
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
