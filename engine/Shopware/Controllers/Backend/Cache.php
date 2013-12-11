<?php
use Shopware\Components\CacheManager;

/**
 * Shopware 4
 * Copyright © shopware AG
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
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Backend_Cache extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * @var CacheManager
     */
    protected $cacheManager;

    public function init()
    {
        $this->cacheManager = $this->get('shopware.cache_manager');

        parent::init();
    }

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
            $this->cacheManager->getConfigCacheInfo(),
            $this->cacheManager->getHttpCacheInfo($this->Request()),
            $this->cacheManager->getTemplateCacheInfo(),
            $this->cacheManager->getShopwareProxyCacheInfo(),
            $this->cacheManager->getDoctrineFileCacheInfo(),
            $this->cacheManager->getDoctrineProxyCacheInfo(),
        );

        $this->View()->assign(array(
            'success' => true,
            'data'    => $data,
            'total'   => count($data)
        ));
    }

    /**
     * Clear cache action
     */
    public function clearCacheAction()
    {
        $cache = $this->Request()->getPost('cache', array());

        $cacheInstance = $this->cacheManager->getCoreCache();

        $capabilities = $cacheInstance->getBackend()->getCapabilities();

        if (empty($capabilities['tags'])) {
            if ($cache['config'] == 'on' || $cache['template'] == 'on') {
                $cacheInstance->clean();
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
                $cacheInstance->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $tags);
            } else {
                $cacheInstance->clean();
            }
        }

        if ($cache['config'] == 'on' || $cache['backend'] == 'on' || $cache['frontend'] == 'on') {
            $this->cacheManager->clearTemplateCache();
        }
        if ($cache['search'] == 'on') {
            $this->cacheManager->clearSearchCache();
        }
        if ($cache['router'] == 'on') {
            $this->cacheManager->clearRewriteCache();
        }
        if ($cache['template'] == 'on' || $cache['backend'] == 'on' || $cache['frontend'] == 'on') {
            $this->cacheManager->clearTemplateCache();
        }
        if ($cache['http'] == 'on' || $cache['frontend'] == 'on') {
            $this->cacheManager->clearHttpCache();
        }
        if ($cache['proxy'] == 'on') {
            $this->cacheManager->clearProxyCache();
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
}
