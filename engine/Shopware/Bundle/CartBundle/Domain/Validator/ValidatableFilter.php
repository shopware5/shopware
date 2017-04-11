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

namespace Shopware\Bundle\CartBundle\Domain\Validator;

use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\Validator\Collector\RuleDataCollectorRegistry;
use Shopware\Bundle\CartBundle\Domain\Validator\Rule\RuleCollection;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;

class ValidatableFilter
{
    /**
     * @var RuleDataCollectorRegistry
     */
    private $dataCollectorRegistry;

    public function __construct(RuleDataCollectorRegistry $dataCollectorRegistry)
    {
        $this->dataCollectorRegistry = $dataCollectorRegistry;
    }

    /**
     * @param Validatable[]        $items
     * @param CalculatedCart       $calculatedCart
     * @param ShopContextInterface $context
     *
     * @return array
     */
    public function filter(
        array $items,
        CalculatedCart $calculatedCart,
        ShopContextInterface $context,
        $filterOnMatch = true
    ): array {
        $rules = array_map(function (Validatable $item) {
            return $item->getRule();
        }, $items);

        $dataCollection = $this->dataCollectorRegistry->collect(
            $calculatedCart,
            $context,
            new RuleCollection(array_filter($rules))
        );

        $filtered = [];
        foreach ($items as $key => $item) {
            if (!$item->getRule()) {
                $filtered[$key] = $item;
                continue;
            }

            $match = $item->getRule()->match($calculatedCart, $context, $dataCollection);

            //rule match, and "filterOnMatch" also validates to true, filter class and continue
            //rule not match, and "filterOnMatch" also validates to false, filter class and continue
            if ($match === $filterOnMatch) {
                continue;
            }

            $filtered[$key] = $item;
        }

        return $filtered;
    }
}
