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

namespace Shopware\Components\Cart\Struct;

use Shopware\Bundle\StoreFrontBundle\Struct\Extendable;

class DiscountContext extends Extendable
{
    /**
     * @var string
     */
    private $sessionId;

    /**
     * @var int
     */
    private $discountType;

    /**
     * @var float
     */
    private $discountValue;

    /**
     * @var string
     */
    private $discountName;

    /**
     * @var string
     */
    private $orderNumber;

    /**
     * @var int
     */
    private $basketMode;

    /**
     * @var float
     */
    private $currencyFactor;

    /**
     * @var bool
     */
    private $isNetPrice;

    /**
     * @var Price
     */
    private $price;

    /**
     * @var int
     */
    private $basketId;

    /**
     * @param string      $sessionId
     * @param int|null    $discountType
     * @param float|null  $discountValue
     * @param string|null $discountName
     * @param string|null $orderNumber
     * @param int|null    $basketMode
     * @param float|null  $currencyFactor
     * @param bool|null   $isNetPrice
     */
    public function __construct(
        $sessionId,
        $discountType,
        $discountValue,
        $discountName,
        $orderNumber,
        $basketMode,
        $currencyFactor,
        $isNetPrice
    ) {
        $this->sessionId = $sessionId;
        $this->discountType = $discountType;
        $this->discountValue = $discountValue;
        $this->discountName = $discountName;
        $this->orderNumber = $orderNumber;
        $this->basketMode = $basketMode;
        $this->currencyFactor = $currencyFactor;
        $this->isNetPrice = $isNetPrice;
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @param string $sessionId
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * @return int
     */
    public function getDiscountType()
    {
        return $this->discountType;
    }

    /**
     * @param int $discountType
     */
    public function setDiscountType($discountType)
    {
        $this->discountType = $discountType;
    }

    /**
     * @return float
     */
    public function getDiscountValue()
    {
        return $this->discountValue;
    }

    /**
     * @param float $discountValue
     */
    public function setDiscountValue($discountValue)
    {
        $this->discountValue = $discountValue;
    }

    /**
     * @return string
     */
    public function getDiscountName()
    {
        return $this->discountName;
    }

    /**
     * @param string $discountName
     */
    public function setDiscountName($discountName)
    {
        $this->discountName = $discountName;
    }

    /**
     * @return string
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * @param string $orderNumber
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @return int
     */
    public function getBasketMode()
    {
        return $this->basketMode;
    }

    /**
     * @param int $basketMode
     */
    public function setBasketMode($basketMode)
    {
        $this->basketMode = $basketMode;
    }

    /**
     * @return float
     */
    public function getCurrencyFactor()
    {
        return $this->currencyFactor;
    }

    /**
     * @param float $currencyFactor
     */
    public function setCurrencyFactor($currencyFactor)
    {
        $this->currencyFactor = $currencyFactor;
    }

    /**
     * @return bool
     */
    public function isNetPrice()
    {
        return $this->isNetPrice;
    }

    /**
     * @param bool $isNetPrice
     */
    public function setIsNetPrice($isNetPrice)
    {
        $this->isNetPrice = $isNetPrice;
    }

    /**
     * @return Price
     */
    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice(Price $price)
    {
        $this->price = $price;
    }

    /**
     * @return int
     */
    public function getBasketId()
    {
        return $this->basketId;
    }

    /**
     * @param int $basketId
     */
    public function setBasketId($basketId)
    {
        $this->basketId = $basketId;
    }
}
