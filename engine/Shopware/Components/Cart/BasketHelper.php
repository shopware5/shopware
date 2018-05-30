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

use Doctrine\DBAL\Connection;
use Enlight_Components_Session_Namespace as Session;
use Shopware\Components\Cart\Struct\Price;

/**
 * Class BasketHelper
 */
class BasketHelper implements BasketHelperInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var ProportionalTaxCalculatorInterface
     */
    private $calculator;

    /**
     * BasketHelper constructor.
     *
     * @param Connection                         $connection
     * @param Session                            $session
     * @param ProportionalTaxCalculatorInterface $calculator
     */
    public function __construct(Connection $connection, Session $session, ProportionalTaxCalculatorInterface $calculator)
    {
        $this->connection = $connection;
        $this->session = $session;
        $this->calculator = $calculator;
    }

    /**
     * @param int    $discountType
     * @param float  $discountValue
     * @param string $itemName
     * @param int    $mode
     * @param string $ordernumber
     * @param float  $currencyFactor
     * @param bool   $netPrice
     */
    public function addProportionalDiscount($discountType, $discountValue, $itemName, $mode, $ordernumber, $currencyFactor, $netPrice)
    {
        $prices = $this->getPositionPrices();
        $hasMultipleTaxes = $this->calculator->hasDifferentTaxes($prices);

        if ($discountType === self::DISCOUNT_ABSOLUTE) {
            $discounts = $this->calculator->calculate($discountValue, $prices, $netPrice);
        } else {
            $discounts = $this->calculator->recalculatePercentageDiscount($discountValue, $prices, $netPrice);
        }

        /** @var Price $discount */
        foreach ($discounts as $discount) {
            $this->connection->insert(
                's_order_basket',
                [
                    'sessionID' => $this->session->offsetGet('sessionId'),
                    'articlename' => $itemName . ($hasMultipleTaxes ? ' (' . $discount->getTaxRate() . '%)' : ''),
                    'articleID' => 0,
                    'ordernumber' => $ordernumber,
                    'quantity' => 1,
                    'price' => $discount->getPrice(),
                    'netprice' => $discount->getNetPrice(),
                    'tax_rate' => $discount->getTaxRate(),
                    'datum' => date('Y-m-d H:i:s'),
                    'modus' => $mode,
                    'currencyFactor' => $currencyFactor,
                ]
            );
        }
    }

    /**
     * @return array
     */
    public function getPositionPrices()
    {
        $query = $this->connection->createQueryBuilder();
        $query->select([
            'basket.price as end_price',
            'basket.netprice as net_price',
            'basket.tax_rate',
            'basket.quantity',
        ]);
        $query->from('s_order_basket', 'basket');
        $query->andWhere('basket.modus = 0');
        $query->andWhere('basket.sessionID = :session');
        $query->setParameter(':session', $this->session->get('sessionId'));

        $rows = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(function ($row) {
            return new Price(
                (float) $row['end_price'] * $row['quantity'],
                (float) $row['net_price'] * $row['quantity'],
                (float) $row['tax_rate'],
                null
            );
        }, $rows);
    }
}
