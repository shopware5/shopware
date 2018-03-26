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

namespace Shopware\Bundle\PluginInstallerBundle\Context;

class OrderRequest
{
    /**
     * @var string
     */
    private $licenceShop;

    /**
     * @var string
     */
    private $bookingShop;

    /**
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
     * @param string $licenceShop
     * @param string $bookingShop
     * @param string $orderNumber
     * @param float  $price
     * @param string $priceType
     */
    public function __construct(
        $licenceShop,
        $bookingShop,
        $orderNumber,
        $price,
        $priceType
    ) {
        $this->licenceShop = $licenceShop;
        $this->bookingShop = $bookingShop;
        $this->orderNumber = $orderNumber;
        $this->price = $price;
        $this->priceType = $priceType;
    }

    /**
     * @return string
     */
    public function getLicenceShop()
    {
        return $this->licenceShop;
    }

    /**
     * @return string
     */
    public function getBookingShop()
    {
        return $this->bookingShop;
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
}
