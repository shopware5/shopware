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

use Doctrine\ORM\AbstractQuery;
use Shopware\Components\CacheManager;

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
class Shopware_Controllers_Backend_Cache extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * {@inheritdoc}
     */
    public function preDispatch()
    {
        parent::preDispatch();

        $this->cacheManager = $this->get('shopware.cache_manager');
    }

    /**
     * Cache info action
     */
    public function getInfoAction()
    {
        $data = [
            $this->cacheManager->getConfigCacheInfo(),
            $this->cacheManager->getHttpCacheInfo($this->Request()),
            $this->cacheManager->getTemplateCacheInfo(),
            $this->cacheManager->getThemeCacheInfo(),
            $this->cacheManager->getShopwareProxyCacheInfo(),
            $this->cacheManager->getDoctrineProxyCacheInfo(),
            $this->cacheManager->getOpCacheCacheInfo(),
        ];

        $this->View()->assign([
            'success' => true,
            'data' => $data,
            'total' => count($data),
        ]);
    }

    /**
     * Clear cache action
     *
     * @throws \Zend_Cache_Exception
     */
    public function clearCacheAction()
    {
        $cache = $this->Request()->getPost('cache', []);

        $cacheInstance = $this->cacheManager->getCoreCache();

        $capabilities = $cacheInstance->getBackend()->getCapabilities();

        if (empty($capabilities['tags'])) {
            if ($cache['config'] === 'on' || $cache['template'] === 'on') {
                $cacheInstance->clean();
            }
        } else {
            $tags = [];
            if ($cache['config'] === 'on' || $cache['backend'] === 'on') {
                $tags[] = CacheManager::ITEM_TAG_CONFIG;
                $tags[] = CacheManager::ITEM_TAG_PLUGIN;
            }
            if ($cache['search'] === 'on') {
                $tags[] = CacheManager::ITEM_TAG_SEARCH;
            }
            if ($cache['backend'] === 'on') {
                $tags[] = CacheManager::ITEM_TAG_CONFIG;
            }
            if ($cache['proxy'] === 'on') {
                $tags[] = CacheManager::ITEM_TAG_MODELS;
            }
            if (!empty($tags) && $tags < 7) {
                $cacheInstance->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $tags);
            } else {
                $cacheInstance->clean();
            }
        }

        $cacheTags = [];

        foreach ($cache as $cacheTag => $value) {
            if ($value === 'on') {
                if ($cacheTag === 'frontend') {
                    $cacheTags[] = CacheManager::CACHE_TAG_CONFIG;
                    $cacheTags[] = CacheManager::CACHE_TAG_TEMPLATE;
                    $cacheTags[] = CacheManager::CACHE_TAG_THEME;
                    $cacheTags[] = CacheManager::CACHE_TAG_HTTP;
                } elseif ($cacheTag === 'backend') {
                    $cacheTags[] = CacheManager::CACHE_TAG_CONFIG;
                    $cacheTags[] = CacheManager::CACHE_TAG_TEMPLATE;
                } else {
                    $cacheTags[] = $cacheTag;
                }
            }
        }

        $this->cacheManager->clearByTags($cacheTags);

        $this->View()->assign([
            'success' => true,
        ]);
    }

    /**
     * Warms up the cache for a theme shop
     */
    public function themeCacheWarmUpAction()
    {
        $shopId = $this->Request()->get('shopId');

        $repository = $this->get('models')->getRepository(\Shopware\Models\Shop\Shop::class);

        $query = $repository->getShopsWithThemes(['shop.id' => $shopId]);

        /** @var \Shopware\Models\Shop\Shop|null $shop */
        $shop = $query->getResult(
            AbstractQuery::HYDRATE_OBJECT
        )[0];

        if (!$shop) {
            $this->View()->assign([
                'success' => false,
            ]);

            return;
        }

        if ($shop->getMain()) {
            $shop = $shop->getMain();
        }

        try {
            /** @var \Shopware\Components\Theme\Compiler $compiler */
            $compiler = $this->container->get('theme_compiler');
            $compiler->compileJavascript('new', $shop->getTemplate(), $shop);
            $compiler->compileLess('new', $shop->getTemplate(), $shop);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

            return;
        }

        $this->View()->assign([
            'success' => true,
        ]);
    }

    public function moveThemeFilesAction()
    {
        /** @var \Shopware\Models\Shop\Repository $repository */
        $repository = $this->get('models')->getRepository(\Shopware\Models\Shop\Shop::class);
        $shops = $repository->getShopsWithThemes()->getResult();
        $compiler = $this->container->get('theme_compiler');
        $pathResolver = $this->container->get('theme_path_resolver');

        $time = time();

        foreach ($shops as $shop) {
            $oldTimestamp = (int) $compiler->getThemeTimestamp($shop);
            if ($oldTimestamp === $time) {
                ++$time;
            }

            $new = $pathResolver->getCssFilePath($shop, 'new');
            if (!file_exists($new)) {
                continue;
            }

            rename(
                $pathResolver->getCssFilePath($shop, 'new'),
                $pathResolver->getCssFilePath($shop, $time)
            );

            rename(
                $pathResolver->getJsFilePath($shop, 'new'),
                $pathResolver->getJsFilePath($shop, $time)
            );

            $compiler->clearThemeCache($shop, $oldTimestamp);
            $compiler->createThemeTimestamp($shop, $time);
        }
    }

    /**
     * Clear cache action
     */
    public function clearDirectAction()
    {
        $cache = $this->Request()->getQuery('cache');
        switch ($cache) {
            case 'Config':
                $this->cacheManager->clearHttpCache();
                $this->cacheManager->clearTemplateCache();
                $this->cacheManager->clearConfigCache();
                $this->cacheManager->clearSearchCache();
                $this->cacheManager->clearProxyCache();
                break;
            default:
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function initAcl()
    {
        // read
        $this->addAclPermission('getInfo', 'read', 'Insufficient Permissions');
        // update
        $this->addAclPermission('config', 'update', 'Insufficient Permissions');
        // clear
        $this->addAclPermission('clearCache', 'clear', 'Insufficient Permissions');
        $this->addAclPermission('clearDirect', 'clear', 'Insufficient Permissions');
    }
}
