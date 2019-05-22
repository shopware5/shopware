<?php
/**
 * Enlight
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://enlight.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Enlight
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */

/**
 * The Enlight_View_Cache is an interface to implement the caching of the view.
 *
 * The Enlight_View_Cache defines an interface to implement the view caching.
 * If you want to implement your own view class then you have to implement this interface to support the view caching.
 *
 * @category   Enlight
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
interface Enlight_View_Cache
{
    /**
     * This function disable or enable the view caching.
     *
     * @param bool $value
     *
     * @return Enlight_View_Cache
     */
    public function setCaching($value = true);

    /**
     * Returns if the view is already cached.
     *
     * @deprecated 4.2
     *
     * @return bool
     */
    public function isCached();

    /**
     * This function sets the id for caching
     *
     * @param string|array $cache_id
     *
     * @return Enlight_View_Cache
     */
    public function setCacheId($cache_id = null);

    /**
     * This function add an id for caching.
     *
     * @param string|array $cache_id
     *
     * @return Enlight_View_Cache
     */
    public function addCacheId($cache_id);
}
