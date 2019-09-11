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

namespace Shopware\Components\Api\Resource;

use Enlight_Controller_Request_Request;
use Exception;
use Shopware\Components\Api\BatchInterface;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\CacheManager;
use Shopware\Components\DependencyInjection\Container;
use Zend_Cache;
use Zend_Cache_Core;
use Zend_Cache_Exception;

/**
 * Cache API Resource
 *
 * This resource provides access to all shopware caches.
 * It is used internally by the Cache/Performance backend module
 */
class Cache extends Resource implements BatchInterface
{
    /**
     * @var Enlight_Controller_Request_Request
     */
    private $request;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @var Zend_Cache_Core
     */
    private $cache;

    /**
     * Sets the Container.
     *
     * @param Container $container
     */
    public function setContainer(Container $container = null)
    {
        if ($container) {
            $this->request = $container->get('front')->Request();
            $this->cacheManager = $container->get('shopware.cache_manager');
            $this->cache = $container->get('cache');
        }
        parent::setContainer($container);
    }

    /**
     * @param string $id
     *
     * @throws ParameterMissingException
     *
     * @return array
     */
    public function getOne($id)
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ParameterMissingException('id');
        }

        return $this->getCacheInfo($id);
    }

    /**
     * @return array
     */
    public function getList()
    {
        $this->checkPrivilege('read');

        $data = [
            $this->getCacheInfo('config'),
            $this->getCacheInfo('http'),
            $this->getCacheInfo('template'),
            $this->getCacheInfo('proxy'),
            $this->getCacheInfo('doctrine-proxy'),
            $this->getCacheInfo('opcache'),
        ];

        return ['data' => $data, 'total' => count($data)];
    }

    /**
     * @param string $id
     *
     * @throws ParameterMissingException
     *
     * @return true
     */
    public function delete($id)
    {
        $this->checkPrivilege('delete');

        if (empty($id)) {
            throw new ParameterMissingException('id');
        }

        $this->clearCache($id);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdByData($data)
    {
        return $data['id'] ?? false;
    }

    /**
     * Overwrites the base implementation as the cache endpoint does not involve any entity related logic
     *
     * @param array $data
     *
     * @return array
     */
    public function batchDelete($data)
    {
        if (empty($data)) {
            $data = [
                'config' => ['id' => 'config'],
                'http' => ['id' => 'http'],
                'template' => ['id' => 'template'],
                'proxy' => ['id' => 'proxy'],
                'doctrine-proxy' => ['id' => 'doctrine-proxy'],
                'opcache' => ['id' => 'opcache'],
            ];
        }

        $results = [];
        foreach ($data as $key => $datum) {
            $id = $this->getIdByData($datum);

            try {
                $results[$key] = [
                    'success' => true,
                    'operation' => 'delete',
                    'data' => $this->delete((string) $id),
                ];
            } catch (Exception $e) {
                $message = $e->getMessage();

                $results[$key] = [
                    'success' => false,
                    'message' => $message,
                    'trace' => $e->getTraceAsString(),
                ];
            }
        }

        return $results;
    }

    /**
     * @deprecated in 5.6, will be removed without a replacement
     *
     * @return Enlight_Controller_Request_Request
     */
    protected function getRequest()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.7. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        return $this->request;
    }

    /**
     * Clears a given cache info item. This method maintains compatibility with the odl cache module's behaviour.
     *
     * @param string $cache
     *
     * @throws NotFoundException
     * @throws Zend_Cache_Exception
     */
    protected function clearCache($cache)
    {
        $capabilities = $this->cache->getBackend()->getCapabilities();

        if ($cache === 'all') {
            $this->cache->clean();

            $this->cacheManager->clearHttpCache();
            $this->cacheManager->clearConfigCache();
            $this->cacheManager->clearTemplateCache();
            $this->cacheManager->clearProxyCache();
            $this->cacheManager->clearSearchCache();
            $this->cacheManager->clearOpCache();

            return;
        }

        switch ($cache) {
            case 'http':
                $this->cacheManager->clearHttpCache();
                break;
            case 'config':
                $tags[] = CacheManager::ITEM_TAG_CONFIG;
                $tags[] = CacheManager::ITEM_TAG_PLUGIN;
                $this->cacheManager->clearConfigCache();
                break;
            case 'template':
                $this->cacheManager->clearTemplateCache();
                break;
            case 'backend':
                $tags[] = CacheManager::ITEM_TAG_CONFIG;
                $tags[] = CacheManager::ITEM_TAG_PLUGIN;
                $this->cacheManager->clearTemplateCache();
                break;
            case 'proxy':
            case 'doctrine-proxy':
            case 'doctrine-file':
                $tags[] = CacheManager::ITEM_TAG_MODELS;
                $this->cacheManager->clearProxyCache();
                break;
            case 'search':
                $tags[] = CacheManager::ITEM_TAG_SEARCH;
                $this->cacheManager->clearSearchCache();
                break;
            case 'rewrite':
            case 'router':
                $this->cacheManager->clearRewriteCache();
                break;
            case 'opcache':
                $this->cacheManager->clearOpCache();
                break;
            default:
                throw new NotFoundException(sprintf('Cache "%s" is not a valid cache id.', $cache));
        }

        if (!empty($capabilities['tags'])) {
            if (!empty($tags)) {
                $this->cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $tags);
            } else {
                $this->cache->clean();
            }
        }
    }

    /**
     * Returns a given cache info item
     *
     * @param string $cache
     *
     * @throws NotFoundException
     */
    private function getCacheInfo($cache): array
    {
        switch ($cache) {
            case 'http':
                $cacheInfo = $this->cacheManager->getHttpCacheInfo();
                break;
            case 'config':
                $cacheInfo = $this->cacheManager->getConfigCacheInfo();
                break;
            case 'template':
                $cacheInfo = $this->cacheManager->getTemplateCacheInfo();
                break;
            case 'proxy':
                $cacheInfo = $this->cacheManager->getShopwareProxyCacheInfo();
                break;
            case 'doctrine-proxy':
                $cacheInfo = $this->cacheManager->getDoctrineProxyCacheInfo();
                break;
            case 'opcache':
                $cacheInfo = $this->cacheManager->getOpCacheCacheInfo();
                break;
            default:
                throw new NotFoundException(sprintf('Cache "%s" is not a valid cache id.', $cache));
        }

        $cacheInfo['id'] = $cache;

        return $cacheInfo;
    }
}
