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

namespace Shopware\Components\Cart;

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Components\Cart\Struct\DiscountContext;

interface BasketQueryHelperInterface
{
    public const BASKET_TABLE_NAME = 's_order_basket';

    public const BASKET_ATTRIBUTE_TABLE_NAME = 's_order_basket_attributes';

    public const BASKET_TABLE_ALIAS = 'basket';

    /**
     * @return QueryBuilder
     */
    public function getPositionPricesQuery(DiscountContext $discountContext);

    /**
     * @return QueryBuilder
     */
    public function getInsertDiscountQuery(DiscountContext $discountContext);

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function getInsertDiscountAttributeQuery(DiscountContext $discountContext);

    /**
     * @return int
     */
    public function getLastInsertId();
}
