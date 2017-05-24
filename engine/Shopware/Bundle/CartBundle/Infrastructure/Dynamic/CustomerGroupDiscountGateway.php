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

namespace Shopware\Bundle\CartBundle\Infrastructure\Dynamic;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\CartBundle\Domain\Cart\CalculatedCart;
use Shopware\Bundle\CartBundle\Domain\LineItem\CalculatedLineItemInterface;
use Shopware\Bundle\CartBundle\Domain\LineItem\Discount;
use Shopware\Bundle\CartBundle\Domain\Price\PercentagePriceCalculator;
use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;
use Shopware\Bundle\StoreFrontBundle\CustomerGroup\CustomerGroup;

class CustomerGroupDiscountGateway
{
    /**
     * @var PercentagePriceCalculator
     */
    private $percentagePriceCalculator;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param PercentagePriceCalculator $percentagePriceCalculator
     * @param Connection                $connection
     */
    public function __construct(PercentagePriceCalculator $percentagePriceCalculator, Connection $connection)
    {
        $this->percentagePriceCalculator = $percentagePriceCalculator;
        $this->connection = $connection;
    }

    public function get(CalculatedCart $cart, ShopContextInterface $context): ? CalculatedLineItemInterface
    {
        if (!$context->getCustomer()) {
            return null;
        }

        $goods = $cart->getCalculatedLineItems()->filterGoods();

        if (0 === $goods->count()) {
            return null;
        }

        $prices = $goods->getPrices();

        $discount = $this->getDiscount(
            $context->getCurrentCustomerGroup(),
            $prices->getTotalPrice()->getTotalPrice()
        );

        if ($discount === null) {
            return null;
        }

        $discount = $this->percentagePriceCalculator->calculate($discount, $prices, $context);

        return new Discount('customer-group-discount', $discount, 'Customer group discount');
    }

    private function getDiscount(CustomerGroup $customerGroup, float $price): ? float
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(['discounts.basketdiscount']);
        $query->from('s_core_customergroups_discounts', 'discounts');
        $query->andWhere('discounts.groupID = :id');
        $query->andWhere('discounts.basketdiscountstart <= :price');
        $query->orderBy('basketdiscountstart', 'DESC');
        $query->setParameter(':price', $price);
        $query->setParameter(':id', $customerGroup->getId());
        $query->setMaxResults(1);

        $discount = $query->execute()->fetch(\PDO::FETCH_COLUMN);
        if ($discount !== false) {
            return (float) $discount * -1;
        }

        return null;
    }
}
