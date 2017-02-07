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

namespace Shopware\Bundle\CartBundle\Domain\Tax;

use Shopware\Bundle\CartBundle\Domain\Collection;

class CalculatedTaxCollection extends Collection
{
    /**
     * @var CalculatedTax[]
     */
    protected $items = [];

    /**
     * @param float $rate
     * @return string
     */
    private function getKey($rate)
    {
        return $rate . '';
    }

    /**
     * @param CalculatedTax $tax
     */
    public function add($tax)
    {
        $key = $this->getKey($tax->getTaxRate());
        $this->items[$key] = $tax;
    }

    /**
     * @param float $rate
     * @return bool
     */
    public function has($rate)
    {
        return parent::has($this->getKey($rate));
    }

    /**
     * @param float $rate
     * @return null|CalculatedTax
     */
    public function get($rate)
    {
        return parent::get($this->getKey($rate));
    }

    /**
     * @param float $rate
     */
    public function remove($rate)
    {
        return parent::remove($this->getKey($rate));
    }

    /**
     * Returns the total calculated tax for this item
     * @return float
     */
    public function getAmount()
    {
        $amounts = $this->map(function (CalculatedTax $calculatedTax) {
            return $calculatedTax->getTax();
        });
        return array_sum($amounts);
    }

    /**
     * @param CalculatedTaxCollection $taxCollection
     * @return CalculatedTaxCollection
     */
    public function merge(CalculatedTaxCollection $taxCollection)
    {
        $new = new self($this->items);

        /** @var CalculatedTax $calculatedTax */
        foreach ($taxCollection as $calculatedTax) {
            if (!$new->has($calculatedTax->getTaxRate())) {
                $new->add(clone $calculatedTax);
                continue;
            }

            $new->get($calculatedTax->getTaxRate())
                ->increment($calculatedTax);
        }

        return $new;
    }
}
