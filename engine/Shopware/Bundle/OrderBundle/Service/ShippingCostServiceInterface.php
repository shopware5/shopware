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

namespace Shopware\Bundle\OrderBundle\Service;

use Shopware\Models\Dispatch\Dispatch;

interface ShippingCostServiceInterface
{
    /**
     * @param Dispatch::CALCULATION_* $calculationType
     * @param array<string, mixed>    $basket
     * @param array<string, mixed>    $dispatchData
     */
    public function getShippingCostMultiplier(int $calculationType, array $basket, array $dispatchData): float;

    /**
     * @param array<string, mixed> $basket
     * @param array<string, mixed> $dispatch
     */
    public function calculateDispatchSurcharge(array $basket, array $dispatch): float;

    /**
     * @param array<string, mixed>             $basket
     * @param array<int, array<string, mixed>> $dispatches
     */
    public function calculateDispatchesSurcharge(array $basket, array $dispatches): float;
}
