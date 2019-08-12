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

namespace Shopware\Components;

use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\Theme\PathResolver;

class CacheManager
{
    public const CACHE_TAG_TEMPLATE = 'template';
    public const CACHE_TAG_CONFIG = 'config';
    public const CACHE_TAG_ROUTER = 'router';
    public const CACHE_TAG_PROXY = 'proxy';
    public const CACHE_TAG_THEME = 'theme';
    public const CACHE_TAG_HTTP = 'http';
    public const CACHE_TAG_SEARCH = 'search';

    public const ITEM_TAG_CONFIG = 'Shopware_Config';
    public const ITEM_TAG_PLUGIN = 'Shopware_Plugin';
    public const ITEM_TAG_MODELS = 'Shopware_Models';
    public const ITEM_TAG_SEARCH = 'Shopware_Modules_Search';
    public const ITEM_TAG_PLUGIN_CONFIG = 'Shopware_Plugin_Config_';

    /**
     * @var Container
     */
    private $container;

    /**
     * @var \Shopware\Components\Model\Configuration
     */
    private $emConfig;

    /**
     * @var \Zend_Cache_Core
     */
    private $cache;

    /**
     * @var \Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    private $db;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var \Enlight_Event_EventManager
     */
    private $events;

    /**
     * @var PathResolver
     */
    private $themePathResolver;

    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->cache = $container->get('cache');
        $this->emConfig = $container->get('shopware.model_config');
        $this->db = $container->get('db');
        $this->config = $container->get('config');
        $this->events = $container->get('events');
        $this->themePathResolver = $container->get('theme_path_resolver');
    }

    /**
     * @return \Zend_Cache_Core
     */
    public function getCoreCache()
    {
        return $this->cache;
    }

    /**
     * Clear HTTP-Cache
     */
    public function clearHttpCache()
    {
        if ($this->container->getParameter('shopware.httpCache.enabled')) {
            $this->clearDirectory(
                $this->container->getParameter('shopware.httpCache.cache_dir')
            );
        }

        // Fire event to let Plugin-Implementation clear cache
        $this->events->notify('Shopware_Plugins_HttpCache_ClearCache');
    }

    /**
     * Clear template cache
     */
    public function clearTemplateCache()
    {
        $cacheDir = $this->container->getParameter('shopware.template.cacheDir');
        $compileDir = $this->container->getParameter('shopware.template.compileDir');

        $this->clearDirectory($compileDir);

        if ($cacheDir != $compileDir) {
            $this->clearDirectory($cacheDir);
        }
    }

    /**
     * Clear theme cache
     */
    public function clearThemeCache()
    {
        $this->clearDirectory($this->themePathResolver->getCacheDirectory());
    }

    /**
     * Clear rewrite cache
     */
    public function clearRewriteCache()
    {
        $cache = (int) $this->config->routerCache;
        $cache = $cache < 360 ? 86400 : $cache;

        $sql = "SELECT `id` FROM `s_core_config_elements` WHERE `name` LIKE 'routerlastupdate'";
        $elementId = $this->db->fetchOne($sql);

        $sql = '
            SELECT v.shop_id, v.value
            FROM s_core_config_values v
            WHERE v.element_id=?
        ';
        $values = $this->db->fetchPairs($sql, [$elementId]);

        foreach ($values as $shopId => $value) {
            $value = unserialize($value, ['allowed_classes' => false]);
            $value = min(strtotime($value), time() - $cache);
            $value = date('Y-m-d H:i:s', $value);
            $value = serialize($value);
            $sql = '
                UPDATE s_core_config_values SET value=?
                WHERE shop_id=? AND element_id=?
            ';
            $this->db->query($sql, [$value, $shopId, $elementId]);
        }
    }

    /**
     * Clear search cache
     */
    public function clearSearchCache()
    {
        $sql = "SELECT `id` FROM `s_core_config_elements` WHERE `name` LIKE 'fuzzysearchlastupdate'";
        $elementId = $this->db->fetchOne($sql);

        $sql = 'DELETE FROM s_core_config_values WHERE element_id=?';
        $this->db->query($sql, [$elementId]);
    }

    /**
     * Clear search cache
     */
    public function clearConfigCache()
    {
        $capabilities = $this->cache->getBackend()->getCapabilities();
        if (!empty($capabilities['tags'])) {
            $this->cache->clean(
                \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
                [
                    self::ITEM_TAG_CONFIG,
                    self::ITEM_TAG_PLUGIN,
                ]
            );
        } else {
            $this->cache->clean();
        }
    }

    /**
     * Clear proxy cache
     *
     * Clears:
     * - Shopware Proxies
     * - Classmap
     * - Doctrine-Proxies
     * - Doctrine-Annotations
     * - Doctrine-Metadata
     */
    public function clearProxyCache()
    {
        $configuration = $this->emConfig;

        $metaDataCache = $configuration->getMetadataCacheImpl();
        if (method_exists($metaDataCache, 'deleteAll')) {
            $metaDataCache->deleteAll();
        }

        // Clear the APCu cache because this may cause problems when attributes are removed
        if (function_exists('apcu_clear_cache')) {
            apcu_clear_cache();
        }

        // Clear Shopware Proxies / Classmaps / Container
        $this->clearDirectory($this->container->getParameter('shopware.hook.proxyDir'));

        // Clear Annotation file cache
        $this->clearDirectory($this->container->getParameter('shopware.model.proxyDir'));
    }

    public function clearOpCache()
    {
        if (extension_loaded('Zend OPcache') && ini_get('opcache.enable')) {
            opcache_reset();
        }
    }

    /**
     * Returns cache information
     *
     * @param \Enlight_Controller_Request_Request|null $request
     *
     * @return array
     */
    public function getHttpCacheInfo($request = null)
    {
        if ($this->container->getParameter('shopware.httpCache.enabled')) {
            $info = $this->getDirectoryInfo(
                $this->container->getParameter('shopware.httpCache.cache_dir')
            );
        } else {
            $info = [];
        }

        $info['name'] = 'Http-Reverse-Proxy';

        if ($request && $request->getHeader('Surrogate-Capability')) {
            $info['backend'] = $request->getHeader('Surrogate-Capability');
        } else {
            $info['backend'] = 'Unknown';
        }

        return $info;
    }

    /**
     * Returns cache information
     *
     * @return array
     */
    public function getConfigCacheInfo()
    {
        $backendCache = $this->cache->getBackend();

        if ($backendCache instanceof \Zend_Cache_Backend_Apcu) {
            $info = [];
            $apcInfo = apcu_cache_info('user');
            $info['files'] = $apcInfo['num_entries'];
            $info['size'] = $this->encodeSize($apcInfo['mem_size']);
            $apcInfo = apcu_sma_info();
            $info['freeSpace'] = $this->encodeSize($apcInfo['avail_mem']);
        } elseif ($backendCache instanceof \Zend_Cache_Backend_Redis) {
            $info = [];

            /** @var \Redis $redis */
            $redis = $backendCache->getRedis();
            $info['files'] = $redis->dbSize();
            $info['size'] = $this->encodeSize($redis->info()['used_memory']);
        } else {
            $cacheConfig = $this->container->getParameter('shopware.cache');
            $dir = null;

            if (!empty($cacheConfig['backendOptions']['cache_dir'])) {
                $dir = $cacheConfig['backendOptions']['cache_dir'];
            } elseif (!empty($cacheConfig['backendOptions']['slow_backend_options']['cache_dir'])) {
                $dir = $cacheConfig['backendOptions']['slow_backend_options']['cache_dir'];
            }
            $info = $this->getDirectoryInfo($dir);
        }

        $info['name'] = 'Shopware configuration';

        $backend = get_class($backendCache);
        $backend = str_replace('Zend_Cache_Backend_', '', $backend);

        $info['backend'] = $backend;

        return $info;
    }

    /**
     * Returns template cache information
     *
     * @return array
     */
    public function getTemplateCacheInfo()
    {
        $dir = $this->container->getParameter('shopware.template.compileDir');
        $info = $this->getDirectoryInfo($dir);
        $info['name'] = 'Shopware templates';

        return $info;
    }

    /**
     * Returns template cache information
     *
     * @return array
     */
    public function getThemeCacheInfo()
    {
        $dir = $this->container->get('theme_path_resolver')->getCacheDirectory();
        $info = $this->getDirectoryInfo($dir);
        $info['name'] = 'Shopware theme';

        return $info;
    }

    /**
     * Returns cache information
     *
     * @return array
     */
    public function getDoctrineProxyCacheInfo()
    {
        $dir = $this->container->getParameter('shopware.model.proxydir');
        $info = $this->getDirectoryInfo($dir);
        $info['name'] = 'Doctrine Proxies';

        return $info;
    }

    /**
     * Returns cache information
     *
     * @return array
     */
    public function getShopwareProxyCacheInfo()
    {
        $dir = $this->container->getParameter('shopware.hook.proxyDir');

        $info = $this->getDirectoryInfo($dir);
        $info['name'] = 'Shopware Proxies';

        return $info;
    }

    /**
     * Returns cache information
     *
     * @return array
     */
    public function getOpCacheCacheInfo()
    {
        $info = [];
        if (extension_loaded('Zend OPcache') && ini_get('opcache.enable')) {
            $status = opcache_get_status(false);
            $info['files'] = $status['opcache_statistics']['num_cached_scripts'];
            $info['size'] = $this->encodeSize($status['memory_usage']['used_memory']);
            $info['freeSpace'] = $this->encodeSize($status['memory_usage']['free_memory']);
        } else {
            $info['message'] = 'Zend OPcache is not available';
        }
        $info['name'] = 'Zend OPcache';

        return $info;
    }

    /**
     * Returns cache information
     *
     * @param string $dir
     *
     * @return array
     */
    public function getDirectoryInfo($dir)
    {
        $docRoot = $this->container->getParameter('shopware.app.rootdir') . '/';

        $info = [];
        $info['dir'] = str_replace($docRoot, '', $dir);
        $info['dir'] = str_replace(DIRECTORY_SEPARATOR, '/', $info['dir']);
        $info['dir'] = rtrim($info['dir'], '/') . '/';

        if (!file_exists($dir) || !is_dir($dir)) {
            $info['message'] = 'Cache dir not exists';

            return $info;
        }

        if (!is_readable($dir)) {
            $info['message'] = 'Cache dir is not readable';

            return $info;
        }

        if (!is_writable($dir)) {
            $info['message'] = 'Cache dir is not writable';
        }

        $info['size'] = (float) 0;
        $info['files'] = 0;

        $dirIterator = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator(
            $dirIterator,
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        /** @var \SplFileInfo $entry */
        foreach ($iterator as $entry) {
            if ($entry->getFilename() === '.gitkeep') {
                continue;
            }

            if (!$entry->isFile()) {
                continue;
            }

            $info['size'] += $entry->getSize();
            ++$info['files'];
        }
        $info['size'] = $this->encodeSize($info['size']);
        $info['freeSpace'] = disk_free_space($dir);
        $info['freeSpace'] = $this->encodeSize($info['freeSpace']);

        return $info;
    }

    /**
     * Format size method
     *
     * @param float $bytes
     *
     * @return string
     */
    public function encodeSize($bytes)
    {
        $types = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes >= 1024 && $i < (count($types) - 1); $bytes /= 1024, $i++);

        return round($bytes, 2) . ' ' . $types[$i];
    }

    /**
     * Clear caches by list of CacheManager::CACHE_TAG_* items
     *
     * @param string[] $tags
     *
     * @return int
     */
    public function clearByTags(array $tags)
    {
        $count = 0;

        foreach (array_unique($tags) as $tag) {
            $count += (int) $this->clearByTag($tag);
        }

        return $count;
    }

    /**
     * Clear cache by its tag of CacheManager::CACHE_TAG_*
     *
     * @param string $tag
     *
     * @return bool
     */
    public function clearByTag($tag)
    {
        switch ($tag) {
            case self::CACHE_TAG_CONFIG:
                $this->clearConfigCache();
                break;
            case self::CACHE_TAG_SEARCH:
                $this->clearSearchCache();
                break;
            case self::CACHE_TAG_ROUTER:
                $this->clearRewriteCache();
                break;
            case self::CACHE_TAG_TEMPLATE:
                $this->clearTemplateCache();
                break;
            case self::CACHE_TAG_THEME:
            case self::CACHE_TAG_HTTP:
                $this->clearHttpCache();
                break;
            case self::CACHE_TAG_PROXY:
                $this->clearProxyCache();
                $this->clearOpCache();
                break;
            default:
                return false;
        }

        return true;
    }

    /**
     * Clear directory contents
     *
     * @param string $dir
     */
    private function clearDirectory($dir)
    {
        if (!file_exists($dir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        /** @var \SplFileInfo $path */
        foreach ($iterator as $path) {
            if ($path->getFilename() === '.gitkeep') {
                continue;
            }

            if ($path->isDir()) {
                rmdir($path->__toString());
            } else {
                if (!$path->isFile()) {
                    continue;
                }
                unlink($path->__toString());
            }
        }
    }
}
