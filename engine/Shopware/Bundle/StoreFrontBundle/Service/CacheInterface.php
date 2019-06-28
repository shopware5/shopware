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

namespace Shopware\Bundle\StoreFrontBundle\Service;

interface CacheInterface
{
    /**
     * Fetches an entry from the cache.
     *
     * @param string $id the id of the cache entry to fetch
     *
     * @return mixed the cached data or FALSE, if no cache entry exists for the given id
     */
    public function fetch($id);

    /**
     * Puts data into the cache.
     *
     * @param string $id       the cache id
     * @param mixed  $data     the cache entry/data
     * @param int    $lifeTime the cache lifetime.
     *                         If != 0, sets a specific lifetime for this cache entry (0 => infinite lifeTime)
     *
     * @return bool tRUE if the entry was successfully stored in the cache, FALSE otherwise
     */
    public function save($id, $data, $lifeTime = 0);
}
