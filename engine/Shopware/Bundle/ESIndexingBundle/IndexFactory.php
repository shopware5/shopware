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

namespace Shopware\Bundle\ESIndexingBundle;

use Shopware\Bundle\ESIndexingBundle\Struct\IndexConfiguration;
use Shopware\Bundle\ESIndexingBundle\Struct\ShopIndex;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;

class IndexFactory implements IndexFactoryInterface
{
    /**
     * @var string
     */
    private $prefix;

    /**
     * @var array
     */
    private $indexConfig;

    /**
     * @param string $prefix
     */
    public function __construct($prefix, array $indexConfig)
    {
        $this->prefix = $prefix;
        $this->indexConfig = $indexConfig;
    }

    /**
     * @param string $mappingType
     *
     * @return IndexConfiguration
     */
    public function createIndexConfiguration(Shop $shop, $mappingType)
    {
        return new IndexConfiguration(
            $this->getIndexName($shop, $mappingType) . '_' . $this->getTimestamp(),
            $this->getIndexName($shop, $mappingType),
            null,
            null,
            null,
            null,
            $this->indexConfig
        );
    }

    /**
     * @param string $mappingType
     *
     * @return ShopIndex
     */
    public function createShopIndex(Shop $shop, $mappingType)
    {
        return new ShopIndex($this->getIndexName($shop, $mappingType), $shop, $mappingType);
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @return string
     */
    private function getTimestamp()
    {
        $date = new \DateTime();

        return $date->format('YmdHis');
    }

    /**
     * @param string $mappingType
     *
     * @return string
     */
    private function getIndexName(Shop $shop, $mappingType)
    {
        return $this->getPrefix() . $shop->getId() . '_' . $mappingType;
    }
}
