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

class TaxRuleCollection extends Collection
{
    /**
     * @var TaxRuleInterface[]
     */
    protected $items = [];

    /**
     * @param TaxRuleInterface $rule
     */
    public function add($rule)
    {
        $key = $this->getKey($rule->getRate());
        $this->items[$key] = $rule;
    }

    /**
     * @param float $rate
     *
     * @return bool
     */
    public function has($rate)
    {
        return parent::has($this->getKey($rate));
    }

    /**
     * @param float $rate
     *
     * @return null|TaxRuleInterface
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
     * @param TaxRuleCollection $rules
     *
     * @return TaxRuleCollection
     */
    public function merge(TaxRuleCollection $rules)
    {
        $new = new self($this->items);

        $rules->map(
            function (TaxRuleInterface $rule) use ($new) {
                if (!$new->has($rule->getRate())) {
                    $new->add($rule);
                }
            }
        );

        return $new;
    }

    /**
     * @param float $rate
     *
     * @return string
     */
    private function getKey($rate)
    {
        return $rate . '';
    }
}
