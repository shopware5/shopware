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

namespace Shopware\Bundle\StoreFrontBundle\Struct\Product;

use Shopware\Bundle\StoreFrontBundle\Struct\Extendable;

class Unit extends Extendable
{
    /**
     * Unique identifier of the struct.
     *
     * @var int
     */
    protected $id;

    /**
     * Contains a name of the unit.
     * This value will be translated over the translation service.
     *
     * @var string
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $unit;

    /**
     * Contains the numeric value of the purchase unit.
     * Used to calculate the unit price of the product.
     *
     * Example:
     *  reference unit equals 1.0 liter
     *  purchase unit  equals 0.7 liter
     *
     *  product price       7,- €
     *  reference price    10,- €
     *
     * @var float
     */
    protected $purchaseUnit;

    /**
     * Contains the numeric value of the reference unit.
     * Used to calculate the unit price of the product.
     *
     * Example:
     *  reference unit equals 1.0 liter
     *  purchase unit  equals 0.7 liter
     *  product price       7,- €
     *  reference price    10,- €
     *
     * @var float
     */
    protected $referenceUnit;

    /**
     * Alphanumeric description how the product
     * units are delivered.
     *
     * Example: bottle, box, pair
     *
     * @var string
     */
    protected $packUnit;

    /**
     * Minimal purchase value for the product.
     * Used as minimum value to add a product to the basket.
     *
     * @var float
     */
    protected $minPurchase;

    /**
     * Maximal purchase value for the product.
     * Used as maximum value to add a product to the basket.
     *
     * @var float
     */
    protected $maxPurchase;

    /**
     * Numeric step value for the purchase.
     * This value is used to generate the quantity combo box
     * on the product detail page and in the basket.
     *
     * @var float
     */
    protected $purchaseStep;

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $unit
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
    }

    /**
     * @return string|null
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @return string
     */
    public function getPackUnit()
    {
        return $this->packUnit;
    }

    /**
     * @param string $packUnit
     */
    public function setPackUnit($packUnit)
    {
        $this->packUnit = $packUnit;
    }

    /**
     * @return float
     */
    public function getPurchaseUnit()
    {
        return $this->purchaseUnit;
    }

    /**
     * @param float $purchaseUnit
     */
    public function setPurchaseUnit($purchaseUnit)
    {
        $this->purchaseUnit = $purchaseUnit;
    }

    /**
     * @return float
     */
    public function getReferenceUnit()
    {
        return $this->referenceUnit;
    }

    /**
     * @param float $referenceUnit
     */
    public function setReferenceUnit($referenceUnit)
    {
        $this->referenceUnit = $referenceUnit;
    }

    /**
     * @param float $maxPurchase
     */
    public function setMaxPurchase($maxPurchase)
    {
        $this->maxPurchase = $maxPurchase;
    }

    /**
     * @return float
     */
    public function getMaxPurchase()
    {
        return $this->maxPurchase;
    }

    /**
     * @param float $minPurchase
     */
    public function setMinPurchase($minPurchase)
    {
        $this->minPurchase = $minPurchase;
    }

    /**
     * @return float
     */
    public function getMinPurchase()
    {
        return empty($this->minPurchase) ? 1 : $this->minPurchase;
    }

    /**
     * @param float $purchaseStep
     */
    public function setPurchaseStep($purchaseStep)
    {
        $this->purchaseStep = $purchaseStep;
    }

    /**
     * @return float
     */
    public function getPurchaseStep()
    {
        return $this->purchaseStep;
    }
}
