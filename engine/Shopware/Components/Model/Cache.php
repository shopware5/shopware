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

namespace Shopware\Components\Model;

/**
 * Interface for the various standard models.
 *
 * This interface defines all standard functions for the various models
 * These standard function must later be implemented in the various models.
 * <code>
 * $cacheComponent = new Shopware\Components\Model\Cache($cache);
 * </code>
 */
class Cache extends \Doctrine\Common\Cache\CacheProvider
{
    /**
     * @var \Zend_Cache_Core
     */
    private $cache;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @param \Zend_Cache_Core $cache
     * @param string $prefix
     */
    public function __construct(\Zend_Cache_Core $cache, $prefix = null)
    {
        if ($prefix === null) {
            $prefix = 'Shopware_Models_' . \Shopware::REVISION . '_';
        }

        $this->prefix = $prefix;
        $this->cache = $cache;
    }

    /**
     * Fetches an entry from the cache.
     *
     * @param string $id cache id The id of the cache entry to fetch.
     *
     * @return string The cached data or FALSE, if no cache entry exists for the given id.
     */
    protected function doFetch($id)
    {
        return $this->cache->load($this->prefix . md5($id));
    }

    /**
     * Test if an entry exists in the cache.
     *
     * @param string $id cache id The cache id of the entry to check for.
     *
     * @return boolean TRUE if a cache entry exists for the given cache id, FALSE otherwise.
     */
    protected function doContains($id)
    {
        return $this->cache->test($this->prefix . md5($id));
    }

    /**
     * Puts data into the cache.
     *
     * @param string $id       The cache id.
     * @param string $data     The cache entry/data.
     * @param bool|int $lifeTime The lifetime. If != false, sets a specific lifetime for this cache entry (null => infinite lifeTime).
     *
     * @return boolean TRUE if the entry was successfully stored in the cache, FALSE otherwise.
     */
    protected function doSave($id, $data, $lifeTime = false)
    {
        return $this->cache->save($data, $this->prefix . md5($id), array('Shopware_Models'), $lifeTime);
    }

    /**
     * Deletes a cache entry.
     *
     * @param string $id cache id
     *
     * @return boolean TRUE if the cache entry was successfully deleted, FALSE otherwise.
     */
    protected function doDelete($id)
    {
        return $this->cache->remove(md5($id));
    }

    /**
     * Deletes all cache entries.
     *
     * @return boolean TRUE if the cache entry was successfully deleted, FALSE otherwise.
     */
    protected function doFlush()
    {
        $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('Shopware_Models'));
    }

    /**
     * Retrieves cached information from data store
     *
     * @since   2.2
     * @return  array An associative array with server's statistics if available, NULL otherwise.
     */
    protected function doGetStats()
    {
        return null;
    }
}
