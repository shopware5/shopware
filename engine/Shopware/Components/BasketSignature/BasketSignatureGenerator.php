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

namespace Shopware\Components\BasketSignature;

class BasketSignatureGenerator implements BasketSignatureGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generateSignature(array $basket, $customerId)
    {
        $items = array_map(
            function (array $item) {
                return [
                    'ordernumber' => $item['ordernumber'],
                    'quantity' => (float) $item['quantity'],
                    'tax_rate' => (float) $item['tax_rate'],
                    'price' => (float) $item['price'],
                ];
            },
            $basket['content']
        );

        $items = $this->sortItems($items);

        $data = [
            'amount' => (float) $basket['sAmount'],
            'taxAmount' => (float) $basket['sAmountTax'],
            'items' => $items,
            'currencyId' => (int) $basket['sCurrencyId'],
        ];

        return hash('sha256', json_encode($data) . $customerId);
    }

    /**
     * @return array
     */
    private function sortItems(array $items)
    {
        usort(
            $items,
            function (array $a, array $b) {
                if ($a['price'] < $b['price']) {
                    return 1;
                } elseif ($a['price'] > $b['price']) {
                    return -1;
                }

                if ($a['quantity'] < $b['quantity']) {
                    return 1;
                } elseif ($a['quantity'] > $b['quantity']) {
                    return -1;
                }

                if ($a['tax_rate'] < $b['tax_rate']) {
                    return 1;
                } elseif ($a['tax_rate'] > $b['tax_rate']) {
                    return -1;
                }

                return strcmp($a['ordernumber'], $b['ordernumber']);
            }
        );

        return array_values($items);
    }
}
