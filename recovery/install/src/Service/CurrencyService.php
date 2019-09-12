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

namespace Shopware\Recovery\Install\Service;

use Shopware\Recovery\Install\Struct\Shop;

class CurrencyService
{
    private $currencySettings = [
        'EUR' => [
            'currency_name' => 'Euro',
            'currency' => 'EUR',
            'template_char' => '&euro;',
            'symbol_position' => 0,
        ],
        'USD' => [
            'currency_name' => 'US Dollar',
            'currency' => 'USD',
            'template_char' => '$',
            'symbol_position' => 0,
        ],
        'GBP' => [
            'currency_name' => 'Pound',
            'currency' => 'GBP',
            'template_char' => '&pound;',
            'symbol_position' => 0,
        ],
    ];

    /**
     * @var \PDO
     */
    private $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @throws \RuntimeException
     */
    public function updateCurrency(Shop $shop)
    {
        $currency = strtoupper($shop->currency);

        if (!array_key_exists($currency, $this->currencySettings)) {
            throw new \RuntimeException("Couldn't find definitions for the select currency");
        }

        try {
            $prepareStatement = $this->connection->prepare(
                'UPDATE s_core_currencies SET
                `name` = :currency_name, templatechar = :template_char,
                symbol_position = :symbol_position, currency = :currency
                WHERE id = 1'
            );
            $prepareStatement->execute($this->currencySettings[$currency]);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage(), 0, $e);
        }
    }
}
