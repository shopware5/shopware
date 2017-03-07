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

use Shopware\Bundle\CartBundle\Domain\KeyCollection;

class TaxRuleCollection extends KeyCollection
{
    /**
     * @var TaxRuleInterface[]
     */
    protected $elements = [];

    public function add(TaxRuleInterface $calculatedTax): void
    {
        parent::doAdd($calculatedTax);
    }

    public function remove(float $taxRate): void
    {
        parent::doRemoveByKey((string) $taxRate);
    }

    public function removeElement(TaxRuleInterface $calculatedTax): void
    {
        parent::doRemoveByKey($this->getKey($calculatedTax));
    }

    public function exists(TaxRuleInterface $calculatedTax): bool
    {
        return parent::has($this->getKey($calculatedTax));
    }

    public function get(float $taxRate): ? TaxRuleInterface
    {
        $key = (string) $taxRate;

        if ($this->has($key)) {
            return $this->elements[$key];
        }

        return null;
    }

    public function merge(TaxRuleCollection $rules): TaxRuleCollection
    {
        $new = new self($this->elements);

        $rules->map(
            function (TaxRuleInterface $rule) use ($new) {
                if (!$new->exists($rule)) {
                    $new->add($rule);
                }
            }
        );

        return $new;
    }

    /**
     * @param TaxRuleInterface $element
     *
     * @return string
     */
    protected function getKey($element): string
    {
        return (string) $element->getRate();
    }
}
