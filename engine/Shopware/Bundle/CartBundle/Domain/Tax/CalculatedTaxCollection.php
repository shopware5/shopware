<?php
declare(strict_types=1);
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
    protected $elements = [];

    public function add(CalculatedTax $tax): void
    {
        $key = $this->getKey($tax->getTaxRate());
        $this->elements[$key] = $tax;
    }

    public function has(float $rate): bool
    {
        return parent::has($this->getKey($rate));
    }

    public function get(float $rate): ? CalculatedTax
    {
        return parent::get($this->getKey($rate));
    }

    public function remove(float $rate)
    {
        return parent::remove($this->getKey($rate));
    }

    /**
     * Returns the total calculated tax for this item
     *
     * @return float
     */
    public function getAmount(): float
    {
        $amounts = $this->map(
            function (CalculatedTax $calculatedTax) {
                return $calculatedTax->getTax();
            }
        );

        return array_sum($amounts);
    }

    public function merge(CalculatedTaxCollection $taxCollection): CalculatedTaxCollection
    {
        $new = new self($this->elements);

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

    private function getKey(float $rate): string
    {
        return $rate . '';
    }
}
