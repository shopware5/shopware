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
use Shopware\Bundle\CartBundle\Domain\Tax\CalculatedTaxCollection;
use Shopware\Bundle\CartBundle\Domain\Tax\TaxRuleCollection;

class CartPrice implements \JsonSerializable
{
    use JsonSerializableTrait;

    /**
     * @var float
     */
    protected $netPrice;

    /**
     * @var float
     */
    protected $totalPrice;

    /**
     * @var CalculatedTaxCollection
     */
    protected $calculatedTaxes;

    /**
     * @var TaxRuleCollection
     */
    protected $taxRules;

    /**
     * @param float $netPrice
     * @param float $totalPrice
     * @param CalculatedTaxCollection $calculatedTaxes
     * @param TaxRuleCollection $taxRules
     */
    public function __construct(
        $netPrice,
        $totalPrice,
        CalculatedTaxCollection $calculatedTaxes,
        TaxRuleCollection $taxRules
    ) {
        $this->netPrice = $netPrice;
        $this->totalPrice = $totalPrice;
        $this->calculatedTaxes = $calculatedTaxes;
        $this->taxRules = $taxRules;
    }

    /**
     * @return float
     */
    public function getNetPrice()
    {
        return $this->netPrice;
    }

    /**
     * @return float
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    /**
     * @return CalculatedTaxCollection
     */
    public function getCalculatedTaxes()
    {
        return $this->calculatedTaxes;
    }

    /**
     * @return TaxRuleCollection
     */
    public function getTaxRules()
    {
        return $this->taxRules;
    }
}
