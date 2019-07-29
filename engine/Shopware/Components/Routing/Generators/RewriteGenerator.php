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

namespace Shopware\Components\Routing\Generators;

use Enlight_Event_EventManager as EventManager;
use Shopware\Components\QueryAliasMapper;
use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\GeneratorListInterface;
use Shopware\Components\Routing\RewriteGenerator\RepositoryInterface;

class RewriteGenerator implements GeneratorListInterface
{
    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * @var QueryAliasMapper
     */
    private $queryAliasMapper;

    /**
     * @var EventManager
     */
    private $eventManager;

    public function __construct(
        RepositoryInterface $repository,
        QueryAliasMapper $queryAliasMapper,
        EventManager $eventManager
    ) {
        $this->repository = $repository;
        $this->queryAliasMapper = $queryAliasMapper;
        $this->eventManager = $eventManager;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(array $params, Context $context)
    {
        if ($context->getShopId() === null) {
            return false;
        }

        if (array_key_exists('_seo', $params) && !$params['_seo']) {
            return false;
        }

        if (array_key_exists('_seo', $params)) {
            unset($params['_seo']);
        }

        $orgQuery = $this->preAssemble($params, $context);

        if (!is_array($orgQuery)) {
            return false;
        }

        $orgPath = http_build_query($orgQuery, '', '&');
        list($url) = $this->repository->rewriteList([$orgPath], $context->getShopId());

        if ($url === false) {
            return false;
        }

        if ($context->isUrlToLower()) {
            $url = strtolower($url);
        }
        $query = array_diff_key($params, $orgQuery);
        // Remove globals
        unset($query['module'], $query['controller']);
        // Remove action, if action is a part of the seo url
        if (isset($orgQuery['sAction']) || (isset($query['action']) && $query['action'] === 'index')) {
            unset($query['action']);
        }

        if (!empty($query)) {
            $url .= '?' . $this->rewriteQuery($query);
        }

        return $url;
    }

    /**
     * @return array
     */
    public function generateList(array $list, Context $context)
    {
        $orgQueryList = array_map(function ($params) use ($context) {
            return $this->preAssemble($params, $context);
        }, $list);

        if (max($orgQueryList) === false) {
            return $list;
        }

        $orgPathList = array_map(function ($orgQuery) {
            return http_build_query($orgQuery, '', '&');
        }, $orgQueryList);

        $urls = $this->repository->rewriteList($orgPathList, $context->getShopId());
        if (max($urls) === false) {
            return $list;
        }

        //Add query / strtolower
        array_walk($urls, function (&$url, $key) use ($context, $list, $orgQueryList) {
            if ($url !== false) {
                if ($context->isUrlToLower()) {
                    $url = strtolower($url);
                }
                $query = array_diff_key($list[$key], $orgQueryList[$key]);
                unset($query['module'], $query['controller']);
                if (isset($orgQueryList[$key]['sAction']) || (isset($query['action']) && $query['action'] === 'index')) {
                    unset($query['action']);
                }
                if (!empty($query)) {
                    $url .= '?' . $this->rewriteQuery($query);
                }
            }
        });

        return $urls;
    }

    /**
     * @param array $query
     *
     * @return array
     */
    protected function getOrgQueryArray($query)
    {
        $orgQuery = ['sViewport' => $query['controller']];
        switch ($query['controller']) {
            case 'detail':
                $orgQuery['sArticle'] = $query['sArticle'];
                break;
            case 'blog':
                if (isset($query['action']) && $query['action'] !== 'index') {
                    $orgQuery['sAction'] = $query['action'];
                    $orgQuery['sCategory'] = $query['sCategory'];
                    $orgQuery['blogArticle'] = $query['blogArticle'];
                } else {
                    $orgQuery['sCategory'] = $query['sCategory'];
                }
                break;
            case 'cat':
                $orgQuery['sCategory'] = $query['sCategory'];
                break;
            case 'supplier':
                $orgQuery['sSupplier'] = $query['sSupplier'];
                break;
            case 'campaign':
                if (isset($query['sCategory'])) {
                    $orgQuery['sCategory'] = $query['sCategory'];
                }
                $orgQuery['emotionId'] = $query['emotionId'];
                break;
            case 'support':
            case 'ticket':
            case 'forms':
                $orgQuery['sViewport'] = 'forms';
                if (isset($query['sFid'])) {
                    $orgQuery['sFid'] = $query['sFid'];
                }
                break;
            case 'custom':
                if (isset($query['sCustom'])) {
                    $orgQuery['sCustom'] = $query['sCustom'];
                }
                break;
            case 'content':
                if (isset($query['sContent'])) {
                    $orgQuery['sContent'] = $query['sContent'];
                }
                break;
            case 'listing':
                if (isset($query['action']) && $query['action'] === 'manufacturer') {
                    $orgQuery['sAction'] = $query['action'];
                    $orgQuery['sSupplier'] = $query['sSupplier'];
                }
                break;
            default:
                if (isset($query['action'])) {
                    $orgQuery['sAction'] = $query['action'];
                }

                if (isset($query['id'])) {
                    $orgQuery['id'] = $query['id'];
                }
                break;
        }

        return $this->eventManager->filter(
            'Shopware_Components_RewriteGenerator_FilterQuery',
            $orgQuery,
            [
                'query' => $query,
            ]
        );
    }

    /**
     * @return array|bool
     */
    private function preAssemble(array $params, Context $context)
    {
        if (isset($params['module']) && $params['module'] !== 'frontend') {
            return false;
        }

        if (!isset($params['controller'])) {
            return false;
        }

        return $this->getOrgQueryArray($params);
    }

    private function rewriteQuery(array $query): string
    {
        $tmp = $this->queryAliasMapper->replaceLongParams($query);

        return http_build_query($tmp, '', '&');
    }
}
