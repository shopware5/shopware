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

namespace Shopware\Components\DependencyInjection\Bridge;

use Shopware\Components\ShopwareReleaseStruct;
use Zend_Cache_Core;
use Zend_Locale_Data;

/**
 * Wrapper for accessing the used zend cache instance
 * + call of Zend_Locale_Data::setCache.
 */
class Cache
{
    /**
     * @param string $backend
     * @param array  $frontendOptions
     * @param array  $backendOptions
     *
     * @throws \Zend_Cache_Exception
     *
     * @return Zend_Cache_Core
     */
    public function factory($backend, $frontendOptions = [], $backendOptions = [], ShopwareReleaseStruct $release)
    {
        $backendOptions['release'] = $release;

        $backend = $this->createBackend($backend, $backendOptions);
        $cacheCore = $this->createCacheCore($frontendOptions);

        $cacheCore->setBackend($backend);

        \Zend_Locale_Data::setCache($cacheCore);
        \Zend_Db_Table_Abstract::setDefaultMetadataCache($cacheCore);

        return $cacheCore;
    }

    /**
     * @param string $backend
     * @param array  $backendOptions
     *
     * @return \Zend_Cache_Backend
     */
    private function createBackend($backend, $backendOptions)
    {
        if (strtolower($backend) === 'auto') {
            $backend = $this->createAutomaticBackend($backendOptions);
        } else {
            if (strtolower($backend) === 'apc') {
                $backend = 'apcu';
            }

            $backend = \Zend_Cache::_makeBackend($backend, $backendOptions);
        }

        return $backend;
    }

    /**
     * @param array $backendOptions
     *
     * @return \Zend_Cache_Backend
     */
    private function createAutomaticBackend($backendOptions = [])
    {
        if ($this->isApcuAvailable()) {
            $backend = new \Zend_Cache_Backend_Apcu($backendOptions);
        } else {
            $backend = new \Zend_Cache_Backend_File($backendOptions);
        }

        return $backend;
    }

    /**
     * @return bool
     */
    private function isApcuAvailable()
    {
        if (PHP_SAPI === 'cli') {
            return false;
        }

        if (!extension_loaded('apcu')) {
            return false;
        }

        if (!ini_get('apc.enabled')) {
            return false;
        }

        return true;
    }

    /**
     * @param array $frontendOptions
     *
     * @return Zend_Cache_Core
     */
    private function createCacheCore($frontendOptions = [])
    {
        return new Zend_Cache_Core($frontendOptions);
    }
}
