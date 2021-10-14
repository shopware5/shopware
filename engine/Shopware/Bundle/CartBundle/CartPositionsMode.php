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

namespace Shopware\Bundle\CartBundle;

final class CartPositionsMode
{
    public const PRODUCT = 0;
    public const PREMIUM_PRODUCT = 1;
    public const VOUCHER = 2;
    public const CUSTOMER_GROUP_DISCOUNT = 3;
    public const PAYMENT_SURCHARGE_OR_DISCOUNT = 4;
    public const SWAG_BUNDLE_DISCOUNT = 10;
    public const TRUSTED_SHOPS_PRODUCT = 12;

    private function __construct()
    {
    }
}
