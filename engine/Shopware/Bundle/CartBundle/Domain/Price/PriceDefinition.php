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

namespace Shopware\Bundle\CartBundle\Domain\Price;

use Shopware\Bundle\CartBundle\Domain\JsonSerializableTrait;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRuleCollection;

class PriceDefinition implements \JsonSerializable
{
    use JsonSerializableTrait;

    /**
     * @var float
     */
    protected $price;

    /**
     * @var TaxRuleCollection
     */
    protected $taxRules;

    /**
     * @var float
     */
    protected $quantity;

    /**
     * @var bool
     */
    private $isCalculated;

    /**
     * @param float $price
     * @param TaxRuleCollection $taxRules
     * @param float|int $quantity
     * @param bool $isCalculated
     */
    public function __construct($price, TaxRuleCollection $taxRules, $quantity = 1, $isCalculated = false)
    {
        $this->price = $price;
        $this->taxRules = $taxRules;
        $this->quantity = $quantity;
        $this->isCalculated = $isCalculated;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return TaxRuleCollection
     */
    public function getTaxRules()
    {
        return $this->taxRules;
    }

    /**
     * @return float
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return boolean
     */
    public function isCalculated()
    {
        return $this->isCalculated;
    }
}
