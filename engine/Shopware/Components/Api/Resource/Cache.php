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

use Shopware\Components\Api\BatchInterface;
use Shopware\Components\Api\Exception as ApiException;
use Shopware\Components\CacheManager;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\DependencyInjection\ContainerAwareInterface;

/**
 * Cache API Resource
 *
 * This resource provides access to all shopware caches.
 * It is used internally by the Cache/Performance backend module
 *
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Cache extends Resource implements ContainerAwareInterface, BatchInterface
{
    /**
     * @var \Enlight_Controller_Request_Request
     */
    private $request;

    /**
     * @var CacheManager
     */
    private $cacheManager;

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
        }
    }

    /**
     * @param string $id
     *
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     *
     * @return array
     */
    public function getOne($id)
    {
        $this->checkPrivilege('read');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
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
     * @throws \Shopware\Components\Api\Exception\ParameterMissingException
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     *
     * @return array
     */
    public function delete($id)
    {
        $this->checkPrivilege('delete');

        if (empty($id)) {
            throw new ApiException\ParameterMissingException();
        }

        $this->clearCache($id);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdByData($data)
    {
        if (isset($data['id'])) {
            return $data['id'];
        }

        return false;
    }

    /**
     * Overwrites the base implementation as the cache endpoint does not involve any entity related logic
     *
     * @param $data
     *
     * @return array
     */
    public function batchDelete($data)
    {
        $results = [];
        foreach ($data as $key => $datum) {
            $id = $this->getIdByData($datum);

            try {
                $results[$key] = [
                    'success' => true,
                    'operation' => 'delete',
                    'data' => $this->delete($id),
                ];
            } catch (\Exception $e) {
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
     * @return \Enlight_Controller_Request_Request
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * Clears a given cache info item. This method maintains compatibility with the odl cache module's behaviour.
     *
     * @param string $cache
     *
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     */
    protected function clearCache($cache)
    {
        $capabilities = $this->cacheManager->getCoreCache()->getBackend()->getCapabilities();

        if ($cache == 'all') {
            $this->cacheManager->getCoreCache()->clean();

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
                $tags[] = 'Shopware_Config';
                $tags[] = 'Shopware_Plugin';
                $this->cacheManager->clearConfigCache();
                break;
            case 'template':
                $this->cacheManager->clearTemplateCache();
                break;
            case 'backend':
                $tags[] = 'Shopware_Config';
                $tags[] = 'Shopware_Plugin';
                $this->cacheManager->clearTemplateCache();
                break;
            case 'proxy':
                $tags[] = 'Shopware_Models';
                $this->cacheManager->clearProxyCache();
                break;
            case 'doctrine-proxy':
                $tags[] = 'Shopware_Models';
                $this->cacheManager->clearProxyCache();
                break;
            case 'doctrine-file':
                $tags[] = 'Shopware_Models';
                $this->cacheManager->clearProxyCache();
                break;
            case 'search':
                $tags[] = 'Shopware_Modules_Search';
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
                throw new ApiException\NotFoundException("Cache {$cache} is not a valid cache id.");
        }

        if (!empty($capabilities['tags'])) {
            if (!empty($tags)) {
                $this->cacheManager->getCoreCache()->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $tags);
            } else {
                $this->cacheManager->getCoreCache()->clean();
            }
        }
    }

    /**
     * Returns a given cache info item
     *
     * @param $cache
     *
     * @throws \Shopware\Components\Api\Exception\NotFoundException
     *
     * @return array
     */
    private function getCacheInfo($cache)
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
                throw new ApiException\NotFoundException("Cache {$cache} is not a valid cache id.");
        }

        $cacheInfo['id'] = $cache;

        return $cacheInfo;
    }
}
