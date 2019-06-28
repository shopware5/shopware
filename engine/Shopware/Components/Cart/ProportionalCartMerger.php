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

namespace Shopware\Components\Cart;

use Shopware_Components_Modules;

class ProportionalCartMerger implements ProportionalCartMergerInterface
{
    /**
     * @var Shopware_Components_Modules
     */
    private $modules;

    public function __construct(Shopware_Components_Modules $modules)
    {
        $this->modules = $modules;
    }

    /**
     * {@inheritdoc}
     */
    public function mergeProportionalItems(array $content)
    {
        $newCart = [];

        foreach ($content as $cartItem) {
            if (!isset($newCart[$cartItem['ordernumber']])) {
                $newCart[$cartItem['ordernumber']] = $cartItem;
                continue;
            }

            // There are some plugins, which allow to have same product multiple times in cart
            if (empty($newCart[$cartItem['ordernumber']]['modus'])) {
                $newCart[] = $cartItem;
                continue;
            }

            if (!isset($newCart[$cartItem['ordernumber']]['fixedName'])) {
                $newCart[$cartItem['ordernumber']]['proportion'] = [$newCart[$cartItem['ordernumber']], $cartItem];
                $newCart[$cartItem['ordernumber']]['articlename'] = substr($newCart[$cartItem['ordernumber']]['articlename'], 0, strrpos($newCart[$cartItem['ordernumber']]['articlename'], ' '));
                $newCart[$cartItem['ordernumber']]['fixedName'] = true;
            } elseif (isset($newCart[$cartItem['ordernumber']]['proportion'])) {
                $newCart[$cartItem['ordernumber']]['proportion'][] = $cartItem;
            }

            $newCart[$cartItem['ordernumber']]['price'] = $this->mergeAmount($newCart[$cartItem['ordernumber']], $cartItem, 'price');
            $newCart[$cartItem['ordernumber']]['netprice'] = $this->mergeAmount($newCart[$cartItem['ordernumber']], $cartItem, 'netprice');
            $newCart[$cartItem['ordernumber']]['amountWithTax'] = $this->mergeAmount($newCart[$cartItem['ordernumber']], $cartItem, 'amountWithTax');
            $newCart[$cartItem['ordernumber']]['amount'] = $this->mergeAmount($newCart[$cartItem['ordernumber']], $cartItem, 'amount');
            $newCart[$cartItem['ordernumber']]['amountnet'] = $this->mergeAmount($newCart[$cartItem['ordernumber']], $cartItem, 'amountnet');
            $newCart[$cartItem['ordernumber']]['priceNumeric'] = $this->mergeAmount($newCart[$cartItem['ordernumber']], $cartItem, 'priceNumeric');
            $newCart[$cartItem['ordernumber']]['tax'] = $this->mergeAmount($newCart[$cartItem['ordernumber']], $cartItem, 'tax');
        }

        // Sorting taxes
        foreach ($newCart as &$item) {
            if (isset($item['proportion'])) {
                usort($item['proportion'], function ($a, $b) {
                    return $a['tax_rate'] > $b['tax_rate'] ? 1 : -1;
                });
            }
        }
        unset($item);

        return array_values($newCart);
    }

    /**
     * @param string $property
     *
     * @return float
     */
    private function mergeAmount(array $item1, array $item2, $property)
    {
        $hasComma = strpos($item1[$property], ',') !== false;
        $amount = (float) str_replace(',', '.', $item1[$property]) + (float) str_replace(',', '.', $item2[$property]);

        if ($hasComma) {
            $amount = $this->modules->Articles()->sFormatPrice($amount);
        }

        return $amount;
    }
}
