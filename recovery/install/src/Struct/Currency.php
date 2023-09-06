<?php
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

namespace Shopware\Recovery\Install\Struct;

class Currency
{
    /**
     * @var string
     */
    private $currency;

    /**
     * @param string $Currency
     */
    private function __construct($Currency)
    {
        $this->currency = $Currency;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->currency;
    }

    /**
     * Returns a list of valid Currencies
     *
     * @return array
     */
    public static function getValidCurrencies()
    {
        return [
            'EUR',
            'USD',
            'GBP',
        ];
    }

    /**
     * @param string $Currency
     *
     * @return Currency
     */
    public static function createFromString($Currency)
    {
        return new self($Currency);
    }
}
