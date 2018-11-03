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

namespace Shopware\Components\Plugin;

use Shopware\Models\Shop\Shop;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.com)
 */
class CachedConfigReader implements ConfigReader
{
    /**
     * @var ConfigReader
     */
    private $reader;

    /**
     * @var array
     */
    private $configStorage;

    /**
     * @param ConfigReader $reader
     */
    public function __construct(ConfigReader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function getByPluginName($pluginName, Shop $shop = null)
    {
        if ($shop) {
            $cacheKey = $pluginName . $shop->getId();
        } else {
            $cacheKey = $pluginName;
        }

        if (!isset($this->configStorage[$cacheKey])) {
            $this->configStorage[$cacheKey] = $this->reader->getByPluginName($pluginName, $shop);
        }

        return $this->configStorage[$cacheKey];
    }
}
