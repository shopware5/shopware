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

/**
 * Class IndexFactory
 */
class IndexFactory implements IndexFactoryInterface
{
    /**
     * @var string
     */
    private $prefix;

    /**
     * @var int|null
     */
    private $numberOfShards;

    /**
     * @var int|null
     */
    private $numberOfReplicas;

    /**
     * @param string   $prefix
     * @param int|null $numberOfShards
     * @param int|null $numberOfReplicas
     */
    public function __construct($prefix, $numberOfShards = null, $numberOfReplicas = null)
    {
        $this->prefix = $prefix;
        $this->numberOfShards = $numberOfShards;
        $this->numberOfReplicas = $numberOfReplicas;
    }

    /**
     * @param Shop $shop
     *
     * @return IndexConfiguration
     */
    public function createIndexConfiguration(Shop $shop)
    {
        return new IndexConfiguration(
            $this->getIndexName($shop) . '_' . $this->getTimestamp(),
            $this->getIndexName($shop),
            $this->numberOfShards,
            $this->numberOfReplicas
        );
    }

    /**
     * @param Shop $shop
     *
     * @return ShopIndex
     */
    public function createShopIndex(Shop $shop)
    {
        return new ShopIndex($this->getIndexName($shop), $shop);
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
     * @param Shop $shop
     *
     * @return string
     */
    private function getIndexName(Shop $shop)
    {
        return $this->getPrefix() . $shop->getId();
    }
}
