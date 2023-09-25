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

namespace Shopware\Bundle\OrderBundle\Service;

use Doctrine\DBAL\Connection;
use RuntimeException;
use Shopware\Models\Dispatch\Dispatch;

class ShippingCostService implements ShippingCostServiceInterface
{
    private Connection $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    public function getShippingCostMultiplier(int $calculationType, array $basket, array $dispatchData): float
    {
        switch (true) {
            case $calculationType === Dispatch::CALCULATION_WEIGHT:
                return round((float) ($basket['weight'] ?? 1.0), 3);
            case $calculationType === Dispatch::CALCULATION_PRICE:
                return round((float) ($basket['amount'] ?? 1.0), 2);
            case $calculationType === Dispatch::CALCULATION_NUMBER_OF_PRODUCTS:
                return round((float) ($basket['count_article'] ?? 1.0));
            case $calculationType === Dispatch::CALCULATION_CUSTOM:
                return round((float) ($basket['calculation_value_' . $dispatchData['id']] ?? 1.0), 2);
        }

        throw new RuntimeException(sprintf('Shipping calculation type "%d" not supported', $calculationType));
    }

    public function calculateDispatchSurcharge(array $basket, array $dispatch): float
    {
        $surcharge = 0;

        $calculationType = (int) $dispatch['calculation'];
        if (!\in_array($calculationType, Dispatch::CALCULATIONS, true)) {
            throw new RuntimeException(sprintf('Invalid shipping calculation type "%d"', $calculationType));
        }

        $from = $this->getShippingCostMultiplier($calculationType, $basket, $dispatch);

        $result = $this->connection->fetchAssociative('SELECT `value` , factor
            FROM s_premium_shippingcosts
            WHERE `from` <= ?
            AND dispatchID = ?
            ORDER BY `from` DESC
            LIMIT 1', [$from, $dispatch['id']]);

        if ($result === false) {
            throw new RuntimeException(sprintf('Shipping calculation not found for dispatch ID "%d" and from value of "%d"', $dispatch['id'], $from));
        }
        $surcharge += $result['value'];
        if (!empty($result['factor'])) {
            $surcharge += $result['factor'] / 100 * $from;
        }

        return $surcharge;
    }

    public function calculateDispatchesSurcharge(array $basket, array $dispatches): float
    {
        $surcharge = 0;

        if (empty($dispatches)) {
            return $surcharge;
        }

        foreach ($dispatches as $dispatch) {
            try {
                $surcharge += $this->calculateDispatchSurcharge($basket, $dispatch);
            } catch (RuntimeException $e) {
                continue;
            }
        }

        return $surcharge;
    }
}
