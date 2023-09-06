<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Mink\Page\Helper\Elements;

use Exception;
use SensioLabs\Behat\PageObjectExtension\PageObject\Element;
use Shopware\Tests\Mink\Tests\General\Helpers\Helper;
use Shopware\Tests\Mink\Tests\General\Helpers\HelperSelectorInterface;

/**
 * Element: HeaderCart
 * Location: Cart on the top right of the shop
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class HeaderCart extends Element implements HelperSelectorInterface
{
    /**
     * @var array
     */
    protected $selector = ['css' => 'li.navigation--entry.entry--cart'];

    /**
     * {@inheritdoc}
     */
    public function getCssSelectors()
    {
        return [
            'quantity' => 'span.cart--quantity',
            'amount' => 'span.cart--amount',
            'link' => 'a.cart--link',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getNamedSelectors()
    {
        return [];
    }

    /**
     * @throws Exception
     */
    public function checkCart(int $quantity, string $amount): void
    {
        $element = Helper::findElements($this, ['quantity', 'amount']);

        $check = [
            'quantity' => [(int) $element['quantity']->getText(), $quantity],
            'amount' => Helper::floatArray([$element['amount']->getText(), $amount]),
        ];

        $result = Helper::checkArray($check);

        if ($result !== true) {
            $message = sprintf(
                'The %s of the header cart is wrong! (%s instead of %s)',
                $result,
                $check[$result][0],
                $check[$result][1]
            );
            Helper::throwException($message);
        }
    }
}
