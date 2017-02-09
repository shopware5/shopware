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

namespace Shopware\Bundle\CartBundle\Domain\Delivery;

use Shopware\Bundle\CartBundle\Domain\JsonSerializableTrait;

class DeliveryInformation implements \JsonSerializable
{
    use JsonSerializableTrait;

    /**
     * @var float
     */
    protected $stock;

    /**
     * @var float
     */
    protected $height;

    /**
     * @var float
     */
    protected $width;

    /**
     * @var float
     */
    protected $length;

    /**
     * @var float
     */
    protected $weight;

    /**
     * @var DeliveryDate
     */
    protected $inStockDeliveryDate;

    /**
     * @var DeliveryDate
     */
    protected $outOfStockDeliveryDate;

    /**
     * @param float $stock
     * @param float $height
     * @param float $width
     * @param float $length
     * @param float $weight
     * @param DeliveryDate $inStockDeliveryDate
     * @param DeliveryDate $outOfStockDeliveryDate
     */
    public function __construct(
        $stock,
        $height,
        $width,
        $length,
        $weight,
        DeliveryDate $inStockDeliveryDate,
        DeliveryDate $outOfStockDeliveryDate
    ) {
        $this->stock = $stock;
        $this->height = $height;
        $this->width = $width;
        $this->length = $length;
        $this->weight = $weight;
        $this->inStockDeliveryDate = $inStockDeliveryDate;
        $this->outOfStockDeliveryDate = $outOfStockDeliveryDate;
    }

    /**
     * @return float
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * @return float
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @return float
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return float
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @return DeliveryDate
     */
    public function getInStockDeliveryDate()
    {
        return $this->inStockDeliveryDate;
    }

    /**
     * @return DeliveryDate
     */
    public function getOutOfStockDeliveryDate()
    {
        return $this->outOfStockDeliveryDate;
    }
}
