<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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

/**
 * @category  Shopware
 * @package   Shopware\Controllers\Backend
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Backend_Cache extends Shopware_Controllers_Backend_ExtJs
{
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

    /**
     * Cache info action
     */
    public function getInfoAction()
    {
        $data = array(
            $this->getConfigCacheInfo(),
            $this->getHttpCacheInfo(),
            $this->getBackendCacheInfo(),
            $this->getTemplateCacheInfo(),
            $this->getShopwareProxyCacheInfo(),
            $this->getDoctrineFileCacheInfo(),
            $this->getDoctrineProxyCacheInfo(),
        );

        $this->View()->assign(array(
            'success' => true,
            'data'    => $data,
            'total'   => count($data)
        ));
    }

    /**
     *
     * Helpers to clear various caches
     *
     */

    /**
     * Clear cache action
     */
    public function clearCacheAction()
    {
        $cache = $this->Request()->getPost('cache', array());

        $capabilities = Shopware()->Cache()->getBackend()->getCapabilities();

        if (empty($capabilities['tags'])) {
            if ($cache['config'] == 'on' || $cache['template'] == 'on') {
                Shopware()->Cache()->clean();
            }
        } else {
            $tags = array();
            if ($cache['config'] == 'on' || $cache['backend'] == 'on') {
                $tags[] = 'Shopware_Config';
                $tags[] = 'Shopware_Plugin';
            }
            if ($cache['search'] == 'on') {
                $tags[] = 'Shopware_Modules_Search';
            }
            if ($cache['backend'] == 'on') {
                $tags[] = 'Shopware_Config';
            }
            if ($cache['proxy'] == 'on') {
                $tags[] = 'Shopware_Models';
            }
            if (!empty($tags) && $tags < 7) {
                Shopware()->Cache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $tags);
            } else {
                Shopware()->Cache()->clean();
            }
        }

        if ($cache['config'] == 'on' || $cache['backend'] == 'on' || $cache['frontend'] == 'on') {
            $this->clearCompilerCache();
        }
        if ($cache['search'] == 'on') {
            $this->clearSearchCache();
        }
        if ($cache['router'] == 'on') {
            $this->clearRewriteCache();
        }
        if ($cache['template'] == 'on' || $cache['backend'] == 'on' || $cache['frontend'] == 'on') {
            $this->clearTemplateCache();
            $this->clearCompilerCache();
        }
        if ($cache['http'] == 'on' || $cache['frontend'] == 'on') {
            $this->clearFrontendCache();
        }
        if ($cache['proxy'] == 'on') {
            $this->clearProxyCache();
        }

        $this->View()->assign(array(
            'success' => true
        ));
    }

    /**
     * Clear cache action
     */
    public function clearDirectAction()
    {
        $cache = $this->Request()->getQuery('cache');
        switch ($cache) {
            case 'Config':
                $this->clearFrontendCache();
                $this->clearBackendCache();
                $this->clearConfigCache();
                $this->clearCompilerCache();
                $this->clearSearchCache();
                $this->clearProxyCache();
                break;
            default:
                break;
        }
    }

    /**
     * Clear static cache
     * @return bool
     */
    protected function clearFrontendCache()
    {
        $result = Shopware()->Events()->notify('Shopware_Plugins_HttpCache_ClearCache');

        return $result->getReturn();
    }

    /**
     * Clear template cache
     */
    protected function clearBackendCache()
    {
        $this->clearTemplateCache();
        $this->clearCompilerCache();
    }

    /**
     * Clear compiler cache
     */
    protected function clearTemplateCache()
    {
        Shopware()->Template()->clearAllCache();
    }

    /**
     * Clear compiler cache
     */
    protected function clearCompilerCache()
    {
        Shopware()->Template()->clearCompiledTemplate();
    }

    /**
     * Clear rewrite cache
     */
    protected function clearRewriteCache()
    {
        $cache = (int)Shopware()->Config()->routerCache;
        $cache = $cache < 360 ? 86400 : $cache;

        $sql = "SELECT `id` FROM `s_core_config_elements` WHERE `name` LIKE 'routerlastupdate'";
        $elementId = Shopware()->Db()->fetchOne($sql);

        $sql = "
            SELECT v.shop_id, v.value
            FROM s_core_config_values v
            WHERE v.element_id=?
        ";
        $values = Shopware()->Db()->fetchPairs($sql, array($elementId));

        foreach ($values as $shopId => $value) {
            $value = unserialize($value);
            $value = min(strtotime($value), time() - $cache);
            $value = date('Y-m-d H:i:s', $value);
            $value = serialize($value);
            $sql = '
                UPDATE s_core_config_values SET value=?
                WHERE shop_id=? AND element_id=?
            ';
            Shopware()->Db()->query($sql, array($value, $shopId, $elementId));
        }
    }

    /**
     * Clear search cache
     */
    protected function clearSearchCache()
    {
        $sql = "SELECT `id` FROM `s_core_config_elements` WHERE `name` LIKE 'fuzzysearchlastupdate'";
        $elementId = Shopware()->Db()->fetchOne($sql);

        $sql = 'DELETE FROM s_core_config_values WHERE element_id=?';
        Shopware()->Db()->query($sql, array($elementId));
    }

    /**
     * Clear search cache
     */
    protected function clearConfigCache()
    {
        Shopware()->Cache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array(
            'Shopware_Config', 'Shopware_Plugin'
        ));
    }

    /**
     * Clear proxy cache
     *
     * Clears:
     * - Shopware Proxies
     * - Classmap
     * - Doctrine-Proxies
     * - Doctrine-Anotations
     * - Doctrine-Metadata
     */
    protected function clearProxyCache()
    {
        $configuration = Shopware()->Models()->getConfiguration();
        $metaDataCache = $configuration->getMetadataCacheImpl();
        if (method_exists($metaDataCache, 'deleteAll')) {
            $metaDataCache->deleteAll();
        }

        // Clear Shopware Proxies
        Shopware()->Hooks()->getProxyFactory()->clearCache();

        // Clear classmap
        $classMap = Shopware()->Hooks()->getProxyFactory()->getProxyDir() . 'ClassMap_' . \Shopware::REVISION . '.php';
        @unlink($classMap);

        // Clear Doctrine Proxies
        $files = new GlobIterator(
            $configuration->getProxyDir() . '*.php',
            FilesystemIterator::CURRENT_AS_PATHNAME
        );

        foreach ($files as $filePath) {
            @unlink($filePath);
        }

        // Clear Anotation file cache
        $files = new GlobIterator(
            $configuration->getFileCacheDir() . '*.php',
            FilesystemIterator::CURRENT_AS_PATHNAME
        );

        foreach ($files as $filePath) {
            @unlink($filePath);
        }
    }

    /*
     *
     * Some helper methods to get the relevant caches
     *
     */

    /**
     * Returns cache information
     *
     * @return array
     */
    public function getHttpCacheInfo()
    {
        $request = $this->Request();

        if ($this->resourceLoader->has('HttpCache')) {
            /** @var $proxy Shopware\Components\HttpCache\AppCache */
            $proxy = $this->resourceLoader->get('HttpCache');
            $info = $this->getDirectoryInfo($proxy->getCacheDir());
            $info['backend'] = 'shopware="ESI/1.0"';
        } elseif ($request->getHeader('Surrogate-Capability') !== false) {
            $info = array(
                'backend' => $request->getHeader('Surrogate-Capability')
            );
        }
        $info['name'] = 'Http-Reverse-Proxy';
        return $info;
    }

    /**
     * Returns cache information
     *
     * @return array
     */
    public function getConfigCacheInfo()
    {
        $cache_config = Shopware()->getOption('cache');
        if ($cache_config['backend'] == 'apc' && extension_loaded('apc')) {
            $apcInfo = apc_cache_info('user');
            $info['files'] = $apcInfo['num_entries'];
            $info['size'] = $this->encodeSize($apcInfo['mem_size']);
        } else {
            if (!empty($cache_config['backendOptions']['cache_dir'])) {
                $dir = $cache_config['backendOptions']['cache_dir'];
            } elseif (!empty($cache_config['backendOptions']['slow_backend_options']['cache_dir'])) {
                $dir = $cache_config['backendOptions']['slow_backend_options']['cache_dir'];
            }
            $info = $this->getDirectoryInfo($dir);
        }
        $info['name'] = 'Shopware configuration';
        $info['backend'] = empty($cache_config['backend']) ? 'File' : $cache_config['backend'];
        return $info;
    }

    /**
     * Returns cache information
     *
     * @return array
     */
    public function getTemplateCacheInfo()
    {
        $dir = $this->View()->Engine()->getCompileDir();

        $info = $this->getDirectoryInfo($dir);
        $info['name'] = 'Smarty compiled templates';
        return $info;
    }

    /**
     * Returns cache information
     *
     * @return array
     */
    public function getBackendCacheInfo()
    {
        $dir = $this->View()->Engine()->getCacheDir();
        $info = $this->getDirectoryInfo($dir);
        $info['name'] = 'Smarty cached templates';
        return $info;
    }

    /**
     * Returns cache information
     *
     * @return array
     */
    public function getDoctrineProxyCacheInfo()
    {
        $dir = Shopware()->Models()->getConfiguration()->getProxyDir();
        $info = $this->getDirectoryInfo($dir);
        $info['name'] = 'Doctrine Proxies';
        return $info;
    }

    /**
     * Returns cache information
     *
     * @return array
     */
    public function getDoctrineFileCacheInfo()
    {
        $dir = Shopware()->Models()->getConfiguration()->getFileCacheDir();
        $info = $this->getDirectoryInfo($dir);
        $info['name'] = 'Doctrine annotations';

        return $info;
    }

    /**
     * Returns cache information
     *
     * @return array
     */
    public function getShopwareProxyCacheInfo()
    {
        $dir = Shopware()->Hooks()->getProxyFactory()->getProxyDir();
        $info = $this->getDirectoryInfo($dir);
        $info['name'] = 'Shopware Proxies';
        return $info;
    }

    /**
     * Returns cache information
     *
     * @param string $dir
     * @return array
     */
    public function getDirectoryInfo($dir)
    {
        $info = array();
        $info['dir'] = str_replace(Shopware()->DocPath(), '', $dir);
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

        $info['size'] = (float)0;
        $info['files'] = 0;
        $dir_iterator = new RecursiveDirectoryIterator($dir);
        $iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $entry) {
            if (!$entry->isFile()) {
                continue;
            }

            if ($entry->getFileName() === '.gitkeep') {
                continue;
            }

            $info['size'] += $entry->getSize();
            $info['files']++;
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
     * @return string
     */
    public static function encodeSize($bytes)
    {
        $types = array('B', 'KB', 'MB', 'GB', 'TB');
        for ($i = 0; $bytes >= 1024 && $i < (count($types) - 1); $bytes /= 1024, $i++) ;
        return (round($bytes, 2) . ' ' . $types[$i]);
    }
}
