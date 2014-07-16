<?php
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

namespace Shopware\Components\DependencyInjection\Bridge;

use Zend_Cache_Core;
use Zend_Locale_Data;

/**
 * This is no real factory!
 *
 * Wrapper for accessing the used zend cache instance
 * + call of Zend_Locale_Data::setCache.
 *
 * @category  Shopware
 * @package   Shopware\Components\DependencyInjection\Bridge
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Cache
{
    /**
     * @param \Zend_Cache_Core $zendCache
     * @return \Zend_Cache_Core
     */
    public function factory(\Zend_Cache_Core $zendCache)
    {
        Zend_Locale_Data::setCache($zendCache);

        return $zendCache;
    }
}
