<?php

use Shopware\Components\CacheManager;
use Doctrine\ORM\AbstractQuery;

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

    public function preDispatch()
    {
        parent::preDispatch();

        $this->cacheManager = $this->get('shopware.cache_manager');
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
            $this->cacheManager->getThemeCacheInfo(),
            $this->cacheManager->getShopwareProxyCacheInfo(),
            $this->cacheManager->getDoctrineProxyCacheInfo()
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
        if ($cache['theme'] == 'on' || $cache['frontend'] == 'on') {
            $this->cacheManager->clearHttpCache();
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
     * Warms up the cache for a theme shop
     */
    public function themeCacheWarmUpAction()
    {
        $shopId = $this->Request()->get('shopId');

        $repository = $this->get('models')->getRepository('Shopware\Models\Shop\Shop');

        $query = $repository->getShopsWithThemes(array('shop.id' => $shopId));

        /**@var $shop \Shopware\Models\Shop\Shop*/
        $shop = $query->getResult(
            AbstractQuery::HYDRATE_OBJECT
        )[0];

        if (!$shop) {
            $this->View()->assign(array(
                'success' => false
            ));
            return;
        }

        if ($shop->getMain()) {
            $shop = $shop->getMain();
        }

        /** @var $compiler \Shopware\Components\Theme\Compiler */
        $compiler = $this->container->get('theme_compiler');
        $compiler->compileJavascript('new', $shop->getTemplate(), $shop);
        $compiler->compileLess('new', $shop->getTemplate(), $shop);

        $this->View()->assign(array(
            'success' => true
        ));
    }

    public function moveThemeFilesAction()
    {
        /**@var $repository \Shopware\Models\Shop\Repository*/
        $repository = $this->get('models')->getRepository('Shopware\Models\Shop\Shop');
        $shops = $repository->getShopsWithThemes()->getResult();
        $compiler = $this->container->get('theme_compiler');
        $pathResolver = $this->container->get('theme_path_resolver');

        $time = time();

        foreach ($shops as $shop) {
            $oldTimestamp = $compiler->getThemeTimestamp($shop);
            if ($oldTimestamp == $time) {
                $time++;
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
}
