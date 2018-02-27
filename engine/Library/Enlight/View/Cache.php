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
