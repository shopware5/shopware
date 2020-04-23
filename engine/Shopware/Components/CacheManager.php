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

use Doctrine\DBAL\Connection;
use Enlight_Controller_Request_Request;
use Enlight_Event_EventManager;
use PDO;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Redis;
use Shopware\Components\Model\Configuration;
use Shopware\Components\Theme\PathResolver;
use Shopware_Components_Config;
use SplFileInfo;
use Zend_Cache;
use Zend_Cache_Backend_Apcu;
use Zend_Cache_Backend_Redis;
use Zend_Cache_Core;

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
     * @var Configuration
     */
    private $emConfig;

    /**
     * @var Zend_Cache_Core
     */
    private $cache;

    /**
     * @var Connection
     */
    private $db;

    /**
     * @var Shopware_Components_Config
     */
    private $config;

    /**
     * @var Enlight_Event_EventManager
     */
    private $events;

    /**
     * @var PathResolver
     */
    private $themePathResolver;

    /**
     * @var array
     */
    private $httpCache;

    /**
     * @var array
     */
    private $cacheConfig;

    /**
     * @var array
     */
    private $templateConfig;

    /**
     * @var string
     */
    private $docRoot;

    /**
     * @var string
     */
    private $hookProxyDir;

    /**
     * @var string
     */
    private $modelProxyDir;

    public function __construct(
        Zend_Cache_Core $cache,
        Configuration $emConfig,
        Connection $db,
        Shopware_Components_Config $config,
        ContainerAwareEventManager $events,
        PathResolver $themePathResolver,
        array $httpCache,
        array $cacheConfig,
        array $templateConfig,
        string $docRoot,
        string $hookProxyDir,
        string $modelProxyDir
    ) {
        $this->cache = $cache;
        $this->emConfig = $emConfig;
        $this->db = $db;
        $this->config = $config;
        $this->events = $events;
        $this->themePathResolver = $themePathResolver;

        $this->httpCache = $httpCache;
        $this->cacheConfig = $cacheConfig;
        $this->templateConfig = $templateConfig;
        $this->docRoot = $docRoot;
        $this->hookProxyDir = $hookProxyDir;
        $this->modelProxyDir = $modelProxyDir;
    }

    /**
     * @deprecated in 5.6, will be removed in 5.8. Use `cache` service directly via DI
     *
     * @return Zend_Cache_Core
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
        if ($this->httpCache['enabled']) {
            $this->clearDirectory($this->httpCache['cache_dir']);
        }

        // Fire event to let Plugin-Implementation clear cache
        $this->events->notify('Shopware_Plugins_HttpCache_ClearCache');
    }

    /**
     * Clear template cache
     */
    public function clearTemplateCache()
    {
        $cacheDir = $this->templateConfig['cacheDir'];
        $compileDir = $this->templateConfig['compileDir'];

        $this->clearDirectory($compileDir);

        if ($cacheDir !== $compileDir) {
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
        $cache = (int) $this->config->offsetGet('routerCache');
        $cache = $cache < 360 ? 86400 : $cache;

        $builder = $this->db->createQueryBuilder();
        $builder
            ->from('s_core_config_values', 'v')
            ->join('v', 's_core_config_elements', 'e', 'v.element_id = e.id')
            ->select(['v.shop_id', 'v.value', 'e.id as element_id'])
            ->where($builder->expr()->like('e.name', $builder->expr()->literal('routerlastupdate')));

        $values = $builder->execute()->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $this->db->prepare('UPDATE s_core_config_values SET value=? WHERE shop_id=? AND element_id=?');

        foreach ($values as $rawValue) {
            $value = unserialize($rawValue['value'], ['allowed_classes' => false]);
            $value = min(strtotime($value), time() - $cache);
            $value = date('Y-m-d H:i:s', $value);
            $value = serialize($value);
            $stmt->execute([$value, $rawValue['shop_id'], $rawValue['element_id']]);
        }
    }

    /**
     * Clear search cache
     */
    public function clearSearchCache()
    {
        $sql = 'DELETE v FROM s_core_config_values v
                JOIN s_core_config_elements e ON e.id = v.element_id
                WHERE `name` LIKE "fuzzysearchlastupdate"';
        $this->db->executeQuery($sql);
    }

    /**
     * Clear search cache
     */
    public function clearConfigCache()
    {
        $capabilities = $this->cache->getBackend()->getCapabilities();
        if (!empty($capabilities['tags'])) {
            $this->cache->clean(
                Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
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
        $this->clearDirectory($this->hookProxyDir);

        // Clear Annotation file cache
        $this->clearDirectory($this->modelProxyDir);
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
     * @param Enlight_Controller_Request_Request|null $request
     *
     * @return array
     */
    public function getHttpCacheInfo($request = null)
    {
        $info = $this->httpCache['enabled'] ? $this->getDirectoryInfo($this->httpCache['cache_dir']) : [];

        $info['name'] = 'Http-Reverse-Proxy';
        $info['backend'] = 'Unknown';

        if ($request && $request->getHeader('Surrogate-Capability')) {
            $info['backend'] = $request->getHeader('Surrogate-Capability');
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

        if ($backendCache instanceof Zend_Cache_Backend_Apcu) {
            $info = [];
            $apcInfo = apcu_cache_info('user');
            $info['files'] = $apcInfo['num_entries'];
            $info['size'] = $this->encodeSize($apcInfo['mem_size']);
            $apcInfo = apcu_sma_info();
            $info['freeSpace'] = $this->encodeSize($apcInfo['avail_mem']);
        } elseif ($backendCache instanceof Zend_Cache_Backend_Redis) {
            $info = [];

            /** @var Redis $redis */
            $redis = $backendCache->getRedis();
            $info['files'] = $redis->dbSize();
            $info['size'] = $this->encodeSize($redis->info()['used_memory']);
        } else {
            $dir = null;

            if (!empty($this->cacheConfig['backendOptions']['cache_dir'])) {
                $dir = $this->cacheConfig['backendOptions']['cache_dir'];
            } elseif (!empty($this->cacheConfig['backendOptions']['slow_backend_options']['cache_dir'])) {
                $dir = $this->cacheConfig['backendOptions']['slow_backend_options']['cache_dir'];
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
        $info = $this->getDirectoryInfo($this->templateConfig['compileDir']);
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
        $dir = $this->themePathResolver->getCacheDirectory();
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
        $info = $this->getDirectoryInfo($this->modelProxyDir);
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
        $info = $this->getDirectoryInfo($this->hookProxyDir);
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
     * @deprecated in 5.6, will be private in 5.8 without replacement
     *
     * @param string $dir
     *
     * @return array
     */
    public function getDirectoryInfo($dir)
    {
        $info = [];
        $info['dir'] = str_replace($this->docRoot . '/', '', $dir);
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

        $dirIterator = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator(
            $dirIterator,
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        /** @var SplFileInfo $entry */
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
     * Format size method
     *
     * @deprecated in 5.6, will be private in 5.8 without replacement
     *
     * @param float $bytes
     *
     * @return string
     */
    public function encodeSize($bytes)
    {
        $types = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes >= 1024 && $i < (count($types) - 1); ++$i) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $types[$i];
    }

    /**
     * Clear directory contents
     */
    private function clearDirectory(string $dir): void
    {
        if (!file_exists($dir)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        /** @var SplFileInfo $path */
        foreach ($iterator as $path) {
            if ($path->getFilename() === '.gitkeep') {
                continue;
            }

            if ($path->isDir()) {
                rmdir((string) $path);
            } else {
                if (!$path->isFile()) {
                    continue;
                }
                unlink((string) $path);
            }
        }
    }
}
