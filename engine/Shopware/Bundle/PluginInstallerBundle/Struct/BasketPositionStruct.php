<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\PluginInstallerBundle\Struct;

use JsonSerializable;
use Shopware\Components\ObjectJsonSerializeTraitDeprecated;

class BasketPositionStruct implements JsonSerializable
{
    use ObjectJsonSerializeTraitDeprecated;

    /**
     * @var PluginStruct
     */
    private $plugin;

    /**
     * Technical name of the plugin
     *
     * @var string
     */
    private $orderNumber;

    /**
     * @var float
     */
    private $price;

    /**
     * @var string
     */
    private $priceType;

    /**
     * @param string $orderNumber
     * @param float  $price
     * @param string $priceType
     */
    public function __construct($orderNumber, $price, $priceType)
    {
        $this->price = $price;
        $this->priceType = $priceType;
        $this->orderNumber = $orderNumber;
    }

    /**
     * @return PluginStruct
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * @return string
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return string
     */
    public function getPriceType()
    {
        return $this->priceType;
    }

    /**
     * @param PluginStruct $plugin
     */
    public function setPlugin($plugin)
    {
        $this->plugin = $plugin;
    }
}
